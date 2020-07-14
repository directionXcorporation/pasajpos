function(httpService, localDb, localStorageService, lodash, Auth, loginService, logService, $translate, $mdDialog){
    var access = [];
    return {
        checkAccess: checkAccess,
        runFunctionWithAccessCheck: runFunctionWithAccessCheck
    };
    
    function createHash(input){
        let output = '';
        let flatten = function(object){
            let result = object;
            if(lodash.isObject(object)){
                result = Object.keys(object).reduce(function (r, k) {
                    if(lodash.isObject(object[k])){
                        object[k] = flatten(object[k]);
                    }
                    let b = r.concat(k, object[k]);
                    return b;
                }, []);
            }
            return result;
        }
        output = lodash.flatMapDeep(input, flatten).join('_');
        return output;
    }
    function selectEscalationTime(defaultTime){
        let TimeSelectDialogController = function($mdDialog, defaultTime){
        	let vm = this;
            vm.escalationTime = defaultTime;
        	vm.clickToClose = function(){
        		$mdDialog.cancel();
        	}
        	vm.clickToSave = function(options){
        		$mdDialog.hide(options);
        	}
        }
        logService.setlog('opening dialog for selecting escalation time', 'debug');
        return $mdDialog.show({
        	preserveScope: false,
        	controller: TimeSelectDialogController,
        	controllerAs: '$ctrl',
        	bindToController: true,
        	templateUrl: 'selectAccessTime.tpl',
        	parent: angular.element(document.body),
        	clickOutsideToClose: false,
        	locals: {
        		defaultTime: defaultTime
        	},
        	multiple: true,
        	fullscreen: false
        })
        .then(function(response) {
        	return response.escalationTime;
        }, function() {
        	return;
        });
    }
    function runFunctionWithAccessCheck(functionToRun, functionId, params=[], isAllowed= true, allowIfNoRule= true, method='pinCodeLogin', escalateTime=5, maxRetryTime=3, retryTime=0){
        let userId = Auth.getUser();
        return localDb.initilizeDb().then(function(db){
            let haveAccess = checkAccess(functionId, db, params, isAllowed, allowIfNoRule);
            if(haveAccess && haveAccess['isAllowed']){
                logService.setlog(["No escalation needed for running function "+functionId, {functionId:functionId, db:db, params:params, haveAccess:haveAccess, isAllowed:isAllowed, allowIfNoRule:allowIfNoRule}],"debug");
                return functionToRun();
            }else{
                return escalateAccess(functionId, params, isAllowed, allowIfNoRule, method, escalateTime).then(function(response){
                    let status = response.status;
                    response = response.response;
                    logService.setlog(["Running function "+functionId + " on escalated access?", {functionId:functionId, db:db, params:params, isAllowed:isAllowed, allowIfNoRule:allowIfNoRule, userId:userId, response:response, status: status}],"debug");
                    if(status){
                        logService.setlog(['Checking escalation of access to '+functionId + ' for '+userId+' by '+response.userId, response],'debug');
                        if(response.isAllowed){
                            return selectEscalationTime(escalateTime).then(function(selectedTime){
                                access[functionId][response.key][userId] = response.isAllowed;
                                setTimeout(function() { access[functionId][response.key][userId] = false; }, selectedTime*1000);
                                logService.setlog(['Escalating access to '+functionId + ' for '+userId+' on key '+ response.key +' succeeded by '+response.userId+ ' for '+selectedTime*1000+' seconds',response],'debug');
                        
                                return functionToRun();
                            }).catch(function(e){
                                return functionToRun();
                            });
                        }else{
                            logService.setlog(['Escalating access to '+functionId + ' for '+userId+' failed-Retried times ' +retryTime+ ' out of max allowed '+maxRetryTime, response],'debug');
                            retryTime = retryTime + 1;
                            if(retryTime < maxRetryTime){
                                return runFunctionWithAccessCheck(functionToRun, functionId, params, isAllowed, allowIfNoRule, method, escalateTime, maxRetryTime, retryTime)
                            }
                        }
                    }else{
                        logService.setlog(['Escalating access to '+functionId + ' for '+userId+' failed',response],'debug');
						return (false);
                    }
                }).catch(function (error) {
                    logService.setlog(['Escalating access to '+functionId + ' for '+userId+' failed', error],'debug');
					return(false);
                });
            }
        });
    }
    function escalateAccess(functionId, params=[], isAllowed= true, allowIfNoRule= true, method='pinCodeLogin', escalateTime=5){
        let userId = Auth.getUser();
        return loginService.showLogin(method, 0).then(function (response) {
            if(response.userId){
                return localDb.initilizeDb().then(function(db){
                    let allowed = checkAccess(functionId, db, params, isAllowed, allowIfNoRule, response);
                    return {status: true, response: allowed};
                });
            }else{
                logService.setlog(["Escalating access to "+functionId + " failed", response],"debug");
                return {status: false, response: response};
            }
        })
		.catch(function (error) {
		    logService.setlog(["Escalating access to "+functionId + " failed",error],"debug");
			return {status: false, response: error};
		});
    }
    function checkAccess(functionId, db, params=[], isAllowed=true, allowIfNoRule=true, userDetails={}){
        let userId;
        if(userDetails && userDetails.userId){
            userId = userDetails.userId;
        }
        if(!(Array.isArray(params))){
            params = [params];
        }
        logService.setlog(["checking access to "+functionId, {db: db, params:params, isAllowed:isAllowed}],"debug");
        try{
            if(!userId){
                userId = Auth.getUser();
            }
            logService.setlog(["checking access to "+functionId + " for "+userId],"debug");
        }
        catch(e){
            logService.setlog(["Current userId could not be fetched on "+functionId, {error: e}], "error");
        }
        if(access && access[functionId] && access[functionId][''] && access[functionId][''][userId]){
            logService.setlog(["found access to "+functionId + " and key "+"''"+" for "+userId+" in cache"],"debug");
            return {isAllowed: (access[functionId][''][userId] && isAllowed), key: ''};
        }else{
            if(!access[functionId]){
                access[functionId] = [];
            }
            
        }
        
        let functionGroupDetails = getFunctionGroup(functionId, params, db);
        logService.setlog(["checking access to "+functionId + ":fetched  functionGroupDetails", {functionId: functionId, functionGroupDetails: functionGroupDetails, params: params}],"debug");
        if(!functionGroupDetails || !functionGroupDetails.length){
            if(!access[functionId]['']){
                access[functionId][''] = [];
            }
            if(allowIfNoRule){
                access[functionId][''][userId] = true;
                return {isAllowed: isAllowed, key: ''};
            }else{
                access[functionId][''][userId] = false;
                return {isAllowed: false, key: ''};
            }
        }
        
        let functionGroupActionAllowed;
        
        try{
            if(!userDetails || ! userDetails.userId){
                userDetails = loginService.getLoggedInUserDetails(0, db);
            }
            logService.setlog(["fetched userDetails for access to "+functionId + " for "+userId, {userDetails: userDetails}],"debug");
        }
        catch(e){
            logService.setlog(["Current userDetails could not be fetched for "+userId+ " on function "+functionId, {db: db, error: e}], "error");
        }
        try{
            let usersGroup = loginService.getLoggedInUserGroups(userDetails);
            functionGroupActionAllowed = checkGroupAccess(functionGroupDetails, usersGroup, userId, functionId, params, allowIfNoRule);
            
            logService.setlog(["checking access to "+functionId+" for current user: "+userId, {params: params, isAllowed: isAllowed, functionId: functionId, 'userDetails of logged in user': userDetails, functionGroupDetails: functionGroupDetails, functionGroupActionAllowed:functionGroupActionAllowed}], "debug");
        }
        catch(e){
            logService.setlog(["Error while checking access to "+functionId+" for current user ", {params: params, isAllowed: isAllowed, functionId: functionId, 'userDetails of logged in user': userDetails, functionGroupDetails: functionGroupDetails, error: e}], "error");
        }
        
        if(functionGroupActionAllowed && functionGroupActionAllowed['status']){
            if(!access[functionId][functionGroupActionAllowed['key']]){
                access[functionId][functionGroupActionAllowed['key']] = [];
            }
            access[functionId][functionGroupActionAllowed['key']][userId] = true;
            return {isAllowed: isAllowed, key: functionGroupActionAllowed['key']};
        }else{
            return {isAllowed: false, key: ''};
        }
    }
    function getFunctionGroup(functionId, params, db){
        let searchCondition = {
            'dynaPageFunctionId_usersGroupId_functionId' : functionId,
            'dynaPageFunctionId_usersGroupId_status' : 1,
            'dynaPageFunctionStatus_status' : 1,
            'groupsStatus_status' : 1
        };
        let functionGroups = localDb.advancedSelectItem('functionGroup', searchCondition, db);
        
        return functionGroups;
    }
    function checkGroupAccess_Sub(functionGroupId, usersGroupFlat, key, functionId, userId, allowIfNoRule){
        if((access && access[functionId] && access[functionId][key] && access[functionId][key][userId]) || (allowIfNoRule && !functionGroupId) || functionGroupId === '' || usersGroupFlat.includes(functionGroupId)){
            return true;
        }
    }
    function checkObjectOverlap(source, sub){
        let good = [];
        if(sub && sub.length){
            if(source && source.length){
                for(let i=0; i<sub.length; i++){
                    if(sub[i] !== undefined){
                        let result = Object.keys(sub[i]).every(function(k){
                            return Object.keys(source[i]).indexOf(k)>-1 && source[i][k]===sub[i][k];
                        });
                        if(result){
                            good[i] = 1;
                        }else{
                            return false;
                        }
                    }else{
                        good[i] = 1;
                    }
                }
                if(good.length == sub.length){
                    return true;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return true;
        }
        /*var exists = source.some(function(o){
            return Object.keys(o).some(function(k){
                  return !Object.keys(sub).indexOf(k)>-1 || o[k]!=sub[k];
            });
        });*/
    }
    function checkGroupAccess(functionGroupDetails, usersGroup, userId, functionId, params, allowIfNoRule){
        let usersGroupFlat = usersGroup.map(group=>group.usersGroup_groupId);
        logService.setlog(["Function:checkGroupAccess", {functionGroupDetails: functionGroupDetails, usersGroupFlat:usersGroupFlat, usersGroup:usersGroup, userId:userId, functionId:functionId}],"debug");
        for(let i=0; i<functionGroupDetails.length; i++){
            let functionGroupId = functionGroupDetails[i].dynaPageFunctionId_usersGroupId_groupId;
            let key = '';
            if(!functionGroupDetails[i].dynaPageFunctionAccessValidator_code){
                let haveAccess = checkGroupAccess_Sub(functionGroupId, usersGroupFlat, key, functionId, userId, allowIfNoRule);
                if(haveAccess){
                    logService.setlog(["Granted access to Function "+ functionId+" for "+functionGroupId+" (no validator code found)", {functionGroupDetails: functionGroupDetails[i], usersGroupFlat:usersGroupFlat, usersGroup:usersGroup, userId:userId, functionId:functionId, haveAccess: haveAccess}],"debug");
                    return {status: true, key: ''};
                }
            }else{
                if(checkObjectOverlap(params, functionGroupDetails[i].dynaPageFunctionAccessValidator_code)){
                    key = createHash(functionGroupDetails[i].dynaPageFunctionAccessValidator_code);
                    let haveAccess = checkGroupAccess_Sub(functionGroupId, usersGroupFlat, key, functionId, userId, allowIfNoRule);
                    if(haveAccess){
                        logService.setlog(["Granted access to Function "+ functionId+" for "+functionGroupId+" (validator code found)", {functionGroupDetails: functionGroupDetails[i], usersGroupFlat:usersGroupFlat, usersGroup:usersGroup, userId:userId, functionId:functionId, haveAccess: haveAccess, params: params}],"debug");
                        return {status: true, key: key};
                    }
                }
            }
        }
        return {status: false, key: ''};
    }
}