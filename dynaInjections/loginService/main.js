function(httpService, $q, $mdDialog, $translate, $interval, localDb, globals, errorHandling, $mdToast, logService, menuService, $cookies, onlineStatus){
    let vm = this;
	var salesTimer;
	vm.getLoggedinUserTable = function(){
		//if(!globals.db){
			return localDb.initilizeDb().then(function(db){
				let loggedinUserDb = localDb.getTable('loggedinUser', db);
				return {loggedinUserDb: loggedinUserDb, db: db};
			});
		/*}else{
			return $q(function (resolve, reject) {
				let loggedinUserDb = localDb.getTable('loggedinUser', globals.db);
				resolve({loggedinUserDb: loggedinUserDb, db: globals.db});
			});
		}*/
	}
    
    vm.getLoggedInUserDetails_Sub = function(allDetails=0, db){
        let loggedinUserDb = localDb.getTable('loggedinUser', db);
        if(loggedinUserDb.data.length){
        	let loggedInUser = localDb.selectItem('loggedinUser', {}, 1, loggedinUserDb);
        	if(loggedInUser && allDetails){
        	    let storeUsersDb = localDb.getTable('storeUsers', db);
        	    let loggedInUserDetails = localDb.selectItem('storeUsers', {usersIdstoresId_userId: userId}, 1, storeUsersDb);
        	    loggedInUserDetails = angular.extend(loggedInUserDetails, loggedInUser);
        		return loggedInUserDetails;
        	}else if(loggedInUser){
        	    return loggedInUser;
        	}else{
        		return {};
        	}
        }else{
            return {};
            logService.setlog("Allowed till users were not properly recorded and database of till users is empty. Logout, close browser and try again","error");
        }
    }
    vm.getLoggedInUserGroups = function(loggedInUserDetails){
        let userGroups = [];
        if(loggedInUserDetails && loggedInUserDetails.usersGroup){
            let usersGroup = loggedInUserDetails.usersGroup;
            userGroups = usersGroup.filter(userGroup=>((userGroup.usersGroup_isActive) && (userGroup.usersGroup_accessStart == '0000-00-00') && (userGroup.usersGroup_accessEnd == '0000-00-00')));
        }
        return userGroups;
    }
	vm.getLoggedInUserDetails = function(allDetails=0, db=undefined){
	    let that = vm;
	    if(!db){
	        //Promise-style response
    	    return localDb.initilizeDb().then(function(db){
        	    return that.getLoggedInUserDetails_Sub(allDetails, db);
    	    });
	    }else{
	        //Normal response
	        return vm.getLoggedInUserDetails_Sub(allDetails, db);
	    }
	}
	vm.newLoggedinUser = function(userToInsert){
	    logService.setlog(["Insert logged in user details",{userToInsert:userToInsert}], "debug");
		//if(!globals.db){
				return localDb.initilizeDb().then(function(db){
					let loggedinUserDb = localDb.getTable('loggedinUser', db);
					return vm.insertLoggedinUser(userToInsert, loggedinUserDb, db);
				}.bind(vm));
		/*}else{
				let loggedinUserDb = localDb.getTable('loggedinUser', globals.db);
				return vm.insertLoggedinUser(userToInsert, loggedinUserDb, globals.db);
		}*/
	}
	vm.insertLoggedinUser = function(userToInsert, loggedinUserDb, db){
		let userToInsertArray = [];
		let userToUpdateArray = [];

		return vm.removeLoggedinUser(db, loggedinUserDb).then(function(){
			userToInsertArray.push(userToInsert);
			
			if(userToInsertArray.length){
				localDb.insertItem('loggedinUser', userToInsertArray, loggedinUserDb);
			}else if(userToUpdateArray.length){
				localDb.updateItem('loggedinUser', userToUpdateArray, loggedinUserDb);
			}
			return localDb.saveDb(db);
		}, function(err){
			logService.setlog(["Error happened while logging in", {err: err}], "error");
			return {};
		});
	};
	
	vm.removeLoggedinUser = function(db, loggedinUserDb=undefined){
		if(!loggedinUserDb){
			loggedinUserDb = localDb.getTable('loggedinUser', db);
		}
		localDb.clearTable('loggedinUser', loggedinUserDb, db);
    	logService.setlog("loggeduser cleared", "debug");
    	return localDb.saveDb(db);
	}
	
	vm.pinCodeLogin = function(hashedPinCode, userId, db){
	    logService.setlog(["function: pinCodeLogin: ",{hashedPinCode: hashedPinCode, userId: userId}],"debug");
	    //TODO: read pin from storeUsers not loggedsinUSer
	    let storeUsersDb = localDb.getTable('storeUsers', db);
	    let hasheduserIdPinCode = hashCode(hashedPinCode+userId);
	    let hasheduserIdPinCode2 = hashCode(hasheduserIdPinCode+userId);
	    let searchCondition = {
	        usersIdstoresId_userId: userId,
	        usersPin_hashedPinCode: hasheduserIdPinCode2
	    }
	    logService.setlog(["function: pinCodeLogin: ", {searchCondition: searchCondition, hasheduserIdPinCode:hasheduserIdPinCode, hasheduserIdPinCode2: hasheduserIdPinCode2}],"debug");
		let loggedInUser = localDb.advancedSelectItem('storeUsers', searchCondition, db, {limit:1}, storeUsersDb);
		if(loggedInUser && loggedInUser.length){
		    logService.setlog(["function: pinCodeLogin ",{loggedInUser: loggedInUser}], "debug");
		    loggedInUser = loggedInUser[0];
			if(loggedInUser.usersIdstoresId_userId){
				return loggedInUser;
			}
		}else{
			return {};
		}
	};
	
	vm.setTimer = function(pageId){
	    //TODO: interval should be a setting for each page in pagedata
		let interval = 300;
		vm.cancelTimer();
		console.log(interval);
		if(interval>0){
			salesTimer = $interval(function(){
				console.log("logout");
			}, interval);
		}
	}
	vm.cancelTimer = function(){
	    $interval.cancel(salesTimer);
	}
	vm.showLogin = function(formType='pinCodeLogin', saveUser=true, defaultUsername=''){
		vm.cancelTimer();
		let storeUsers = [];
		return localDb.initilizeDb().then(function(db){
			let storeUsersDb = localDb.getTable('storeUsers', db);
			let storeUsers = localDb.advancedSelectItem('storeUsers', {usersIdstoresId_status: 1}, db, {}, storeUsersDb);
    		return $mdDialog.show({
    	   		preserveScope: false,
    			controller: vm.LoginDialogController,
    			controllerAs: '$ctrl',
    			templateUrl: formType+'.tpl',
    			parent: angular.element(document.body),
    			clickOutsideToClose: false,
    			locals: {
    				thisHolder: vm,
    				saveUser: saveUser,
    				storeUsers: storeUsers,
					defaultUsername: defaultUsername
    			},
    			multiple: true,
    			fullscreen: false
    		})
    		.then(function(response) {
    			return response;
    		}, function(response) {
    			return response;
    		});
		}.bind(vm));
	}
	vm.LoginDialogController = function(thisHolder, saveUser, storeUsers, defaultUsername){
	    let vm = this;
		if(!vm.privatedata){
			vm.privatedata = {
				username: defaultUsername
			};
		}
	    vm.showLogin = thisHolder.showLogin;
	    vm.storeUsers = storeUsers;
		vm.clickToClose = function(){
			let response = {userId: ''};
			$mdDialog.cancel(response);
		}
		vm.recoverPassword = function(username='', showForm=1){
		    logService.setlog("recovering password for: "+username);
			thisHolder.loginRegisterlogout('recoverPassword', '', username).then(function(response){
				if(showForm){
					thisHolder.showLogin('recoverPassword', true, username);
				}
			});
		}
		vm.clickToSave = function(type='pinCodeLogin', options={}){
			let response = {userId: ''};
			thisHolder.getLoggedinUserTable().then(function(loggedinUserDbArray){
					vm.FormFooterShow = false;
					vm.FormDisabled = true;
					vm.responseErrText = "";
					thisHolder.loginRegisterlogout(type, options.userId, options.username, options.password, options.pinCode, '', '', '', options.token).then(function(response){
					    logService.setlog(["login response received", {response:response}], "debug");
						if(response.data.status){
							if(response.data.userId){
							    if(saveUser){
    							    //menuService.getAllowedMenus(response.data.usersGroup, 'usersGroup_groupId').then(function(menu){
    							    
        							let userToInsert = {userId: response.data.userId, usersDetail_userId: response.data.usersDetail_userId, usersGroup: response.data.usersGroup, langISO2: response.data.usersDetail_langISO2, countryISO2: response.data.usersDetail_countryISO2, firstname: response.data.usersDetail_firstName, lastname: response.data.usersDetail_lastName};
        								thisHolder.newLoggedinUser(userToInsert).then(function(result){
        								if(userToInsert.countryISO2 && userToInsert.langISO2){
        								    let lang = userToInsert.langISO2+'_'+userToInsert.countryISO2;
        								    $translate.use(lang);
        								}
        								if(result.status){
        										//Auth.setUser(userToInsert.userId);
        										//thisHolder.showCustomToast('login');
        								}else{
        										//Auth.setUser(userToInsert.userId);
        										//thisHolder.showCustomToast('login');
        								}
        							}, function(error){
        							    logService.setlog(["error saving loggedin user details", error, userToInsert], "error");
        							});
        							$mdDialog.hide(userToInsert);
    							    //});
							    }else{
							        $mdDialog.hide(response.data);
							    }
							}
						}else{
							vm.FormFooterShow = true;
							vm.FormDisabled = false;
							logService.setlog(response.data.msg, "error");
							vm.responseErrText = response.data.msg;
						}
					}, function(response){
						vm.FormFooterShow = true;
						vm.FormDisabled = false;
						logService.setlog(response, "error");
						vm.responseErrText = response.statusText;
					});
				
			});
		}
	}

	vm.showCustomToast = function(action){
		var templateUrl;
		if(action=='register'){
			templateUrl = "registerComplete.html";
		}else if(action=='login'){
			templateUrl = "loginComplete.html";
		}
		$mdToast.show({
			hideDelay   : 5000,
			position    : 'top right',
			controller  : 'ToastCtrl',
			templateUrl : templateUrl
		});
	}
	function deleteAllCookies() {
        var cookies = document.cookie.split(";");
        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i];
            var eqPos = cookie.indexOf("=");
            var name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
            document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
        }
    }
	vm.logout = function(userId=''){
	    return localDb.initilizeDb().then(function(db){
	        vm.removeLoggedinUser(db);
    	    return vm.loginRegisterlogout('logout', userId).then(function(response){
    	        $cookies.remove("pasaj_Session");
    	        deleteAllCookies();
    	        return response;
    	    }, function submitError(response) {
    	        $cookies.remove("pasaj_Session");
    	        deleteAllCookies();
    			return response;
    		});
	    }, function submitError(response) {
	        $cookies.remove("pasaj_Session");
	        deleteAllCookies();
    		return response;
    	});
	}
	let hashCode = function(code){
	    code = code.toString();
	    let hashedCode = '*'+sha512(md5(sha512(code))).toUpperCase();
	    return hashedCode;
	}
	vm.loginRegisterlogout = function(action, userId='', username='', password='', pinCode='', firstname='', lastname='', gender='', token='') {
	    let hashedPinCode = '';
		let hashedToken = '';
	    if(pinCode){
	        hashedPinCode = hashCode(pinCode);
	    }
		if(token){
	        hashedToken = hashCode(token);
	    }
	    if(action == 'pinCodeLogin'){
	        return localDb.initilizeDb().then(function(db){
	            let user;
	            user = vm.pinCodeLogin(hashedPinCode, userId, db);
	            if(user.usersIdstoresId_userId){
	                user.status = 1;
	                user.msg = '';
	                user.userId = user.usersIdstoresId_userId;
	            }else{
	                user = {
    	                status: 0,
    	                msg: 'User Not Found',
    	                userId: ''
    	            }
	            }
	            return {data: user};
	        });
	    }else{
    	    if(action=='usernameLogin'){
    	        action = 'login';
    	    }
    	    
    		var loginParameters = {action: action, username:username,password: sha512(password),firstname:firstname,lastname:lastname,gender:gender,hashedPinCode: hashedPinCode, hashedToken: hashedToken};
    		if(onlineStatus.isOnline()){
        		return httpService.request({
        			method : "POST",
        			url : "/login.php",
        			data: loginParameters,
        			headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
        		}).then(function submitSuccess(response) {
					logService.setlog(["loginRegisterlogout: Returned response for "+action,{response: response, loginParameters: loginParameters}], "debug");
        			return response;
        		}, function submitError(response) {
        			return response;
        		});
    		}else{
    		    logService.setlog("user is offline", "info");
    		    return $q(function (resolve, reject) {
    		        resolve({status: 0, msg: "not online"});
    		    });
    		}
	    }
	}
}