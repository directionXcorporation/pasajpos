{{_PAGE_INJECTIONS_JS}}

{{_PAGE_MODULES_JS}}

app.controller('dynaPage', function($scope, $timeout, $mdToast, $injector, $log, i18nService, localStorageService, tableMapsService, lodash, onlineStatus, localDb, moment, $mdSidenav, $mdBottomSheet, $translate, httpService, $transitions, menuService, Auth {{_INJECTIONS}}) {
    var vm = this;
    vm.checkFunctionAccess = functionAccess.checkAccess;
    vm.runFunctionWithAccessCheck = functionAccess.runFunctionWithAccessCheck;
    vm.pageIsLoading = 1;
    let deviceDisabled = 0;
    
    {{_PAGE_FUNCTIONS}}
    
	vm.moduleManager = moduleManager;
	vm.pageCode = "";
	vm.lang = 'en';
	vm.enablePageEdit = false;
	
	let pageContent;
	var pageCode = 'home';

	let watchedImportParameters = {};
    
    var stateParams = $state.params;
    let pattern = /^(?:modalPageId|objectId|data|inModal|selectedValues)[0-9]+/;
    for (let varName in stateParams) {
        if (pattern.test(varName)) {
            stateParams[varName.slice(0, -1)] = stateParams[varName];
        }
    }
    
    //START: uncomment to use ui-route for modals
    vm.inModal = stateParams.inModal || 0;
    let loadInInclude = stateParams.loadInInclude || 0;
    
    if(!loadInInclude){
        if(stateParams.modalPageId){
    		pageCode = stateParams.modalPageId;
    		vm.inModal = 1;
    	}else{
    		if(stateParams.pageCode){
        		pageCode = stateParams.pageCode;
        	}
    	}
    }

    logService.setlog(["loading page "+pageCode, {pageCode: pageCode, '$state': $state, stateParams: stateParams, loadInInclude: loadInInclude}], "debug");
	//END
	vm.stateParams = stateParams;
	if(stateParams.data){
        logService.setlog(["setting data for page from state parameters", {data: stateParams.data}], "debug");
	    for (var key in stateParams.data) {
	        if(stateParams.data.hasOwnProperty(key)){
	            vm[key] = stateParams.data[key];
	        }
	    }
	}
	
	function runServerCommand(commandCode, parameters){
	    var data = {
	        action: "runServerCommand",
	        commandCode: commandCode,
	        parameters: parameters
	    }
	    
	    return httpService.request({
			method : "POST",
			cache: false,
			data: data,
			url : "/dynaPage.php",
			headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
		}).then(function submitSuccess(response) {
			return response;
		});
	}
	
	function initPage(selectedScreenIndex = 0, pageContent = {}){
		let content = gridsterService.initGridster(pageContent);
		vm.data = content;
		
		vm.selectedScreen = content.screens[selectedScreenIndex];
	}
	
	initPage(0, pageContent);
	
	vm.changeScreen = function(screenId){
		vm.selectedScreen = pageContent.screens.find(item => item.screenId == screenId);
	}
	vm.functionExists = function(functionName){
	    if (typeof vm[functionName] === "function") {
	        return true;
	    }else{
	        return false;
	    }
	}
	function insertInjections(pageInjections){
		var pageInjections = pageInjections.split(",");
		for(var i=0; i<lodash.size(pageInjections); i++){
			let injectorName = pageInjections[i];
			if(!window[injectorName]){
				if($injector.has(injectorName)){
					window[injectorName] = $injector.get(injectorName);
				}else{
					logService.setlog("Some services were not loaded.", "error", 0);
				}
			}
		}
	}

	vm.runCommands = function(pageCommand){
	    let allCommands = [];
	    try{
    	    logService.setlog(["running command",{pageCommand:pageCommand}], "debug");
    		if(lodash.size(pageCommand)){
    			for(var i=0; i<lodash.size(pageCommand); i++){
    			    let thisCommand;
    				let rowDetails = pageCommand[i];
    				if(rowDetails.dynaPageCommandData_commandSource == "local"){
    					try {
    						if(angular.isFunction(vm[rowDetails.dynaPageCommandData_commandText])){
    							thisCommand = $scope.$eval(rowDetails.dynaPageCommandData_commandText)();
    						}else{
    							if(typeof window[rowDetails.dynaPageCommandData_commandText]=== "function"){
    								thisCommand = eval(window[rowDetails.dynaPageCommandData_commandText]());
    							}else{
    								thisCommand = eval(rowDetails.dynaPageCommandData_commandText);
    							}
    						}
    					} catch (e) {
    						logService.setlog(e, "error", 0);
    					}
    				}
    				
    				//SET PARAMETERS AFTER THIS COMMAND IS DONE
    				/*for(let j=0; j<lodash.size(rowDetails.dynaPageId_dynaPageCommandDataId_parameters); j++){
    					if(rowDetails.dynaPageId_dynaPageCommandDataId_parameters[j].parameterOut && window[rowDetails.dynaPageId_dynaPageCommandDataId_parameters[j].parameterIn]){
    						vm[rowDetails.dynaPageId_dynaPageCommandDataId_parameters[j].parameterOut] = window[rowDetails.dynaPageId_dynaPageCommandDataId_parameters[j].parameterIn];
    					}else if(rowDetails.dynaPageId_dynaPageCommandDataId_parameters[j].parameterOut){
    						vm[rowDetails.dynaPageId_dynaPageCommandDataId_parameters[j].parameterOut] = "";
    					}
    				}*/
    				allCommands.push(thisCommand);
    			}
    			
    		}
	    }
	    catch(e){
	        logService.setlog(["error happened while running command", {e:e, pageCommand:pageCommand}], "debug");
	    }
		return allCommands;
	}
	
	let runPageCommands = function(pageData, type='pageLoad'){
	    return $q(function(resolve, reject){
	        try{
        	    logService.setlog(["runCommands",{pageData: pageData, type: type}], "debug");
        		//RUN COMMANDS
        		let pageCommands = [];
        		if(pageData.pageCommand){
        			let pageLoadCommands = lodash.filter(pageData.pageCommand, {dynaPageCommandCondition_conditionType: 'event', dynaPageCommandCondition_conditionText: type, dynaPageCommandData_commandSource: 'local'});
        			pageCommands = vm.runCommands(pageLoadCommands);
        		}
                $q.all(pageCommands).then(function(){
                    logService.setlog(["All pageLoad commands ran successfully",{pageCommands:pageCommands, type:type}], "debug");
                    resolve();
                }, function(error){
                    reject({error:error, pageData:pageData, type:type});
                });
	        }
	        catch(e){
	            logService.setlog(["Error happened while running page commands",{pageData:pageData, type:type}], "error")
	        }
	    });
	}
	
	var showLogin;
	vm.showPage = function(pageData={}, editable=false){
        runPageCommands(pageData, 'pageLoad').then(function(){
            vm.pageCode = pageData.pageCode;
    		let dialogType = "";
    		pageContent = pageData.pageData;
            if(!pageContent.options){
    	        pageContent.options = {};
    	    }
    	    if(editable == true){
    	        pageContent.options.draggable = {
    	            delayStart: 50,
    	            enabled: true
    	        };
    	        pageContent.options.resizable = {
    	            delayStart: 50,
    	            enabled: true
    	        };
    	        pageContent.options.disablePushOnDrag = false;
    	        pageContent.options.disablePushOnResize = false;
    	        pageContent.options.pushItems = true;
    	        pageContent.options.swap = true;
    	    }else{
    	        pageContent.options.draggable = {
    	            enabled: false
    	        };
    	        pageContent.options.resizable = {
    	            enabled: false
    	        };
    	        pageContent.options.disablePushOnDrag = true;
    	        pageContent.options.disablePushOnResize = true;
    	        pageContent.options.pushItems = false;
    	        pageContent.options.swap = false;
    	    }
    	    
    		let selectedScreenIndex = vm.data.screens.findIndex(item=>item.screenName == 'main');
    		logService.setlog(["loading page",{pageContent:pageContent,selectedScreenIndex:selectedScreenIndex}], "debug");
    		initPage(selectedScreenIndex, pageContent);
    		vm.pageIsLoading = 0;
        }, function(error){
            logService.setlog(["An error happened while running page commands",{error:error}], "debug");
        });
        $scope.$emit("updateMenu", {}, function(data){
            vm.menu = data.menu;
            showLogin = data.showLogin;
            vm.menuClick = data.menuClick;
            logService.setlog(["page loaded",{vm: vm}], "debug");
        });
	}
	function getPageDetails(pgCode) {
		logService.setlog("Fetching Page Details", "info", 1);
		var pageData = {};
		
		let parameters = {limit: 1};
		let searchCondition = {dynaPageDetails_pageId: pgCode, dynaPageDetails_availableOffline: 1};
		
		let pageDataRow = localDb.advancedSelectItem("dynaPages", searchCondition, vm.db, parameters);
		logService.setlog(["searching local database for "+pgCode, {'searchCondition': searchCondition, 'db': vm.db, 'parameters': parameters, foundData: pageDataRow, foundDataLength: pageDataRow.length, online: onlineStatus.isOnline()}], "debug");
		if(pageDataRow && pageDataRow.length && !onlineStatus.isOnline()){
		    logService.setlog(["data for "+pgCode+" found in local database",pageDataRow], "debug");
		    return $q(function (resolve, reject) {
		        pageData = jsonizePageData(pageDataRow[0]);
		        logService.clearlog();
		        resolve(pageData);
		    });
		}else{
    		let params = {
    			action: "getPageDetails",
    			pageCode: pgCode,
    			properties: {
    				status: 1, 
    				data: 1,
    				injections: 1,
    				initialVariables: 1,
    				modules: 1
    			}
    		};
    		return httpService.request({
    			method : "POST",
    			cache: false,
    			data: params,
    			url : "/dynaPage.php",
    			headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
    		}).then(function submitSuccess(response) {
    			if(response.data.status){
    				if(response.data.pageDetails && response.data.pageDetails.rows && response.data.pageDetails.rows[0]){
    					let pageDataRow = response.data.pageDetails.rows[0];
    					logService.setlog(["data for "+pgCode+" found online",{response:response, pageDataRow:pageDataRow}], "debug");
    					pageData = jsonizePageData(pageDataRow);
    				}
    				logService.clearlog();
    				return pageData;
    			}else{
    				logService.setlog(response.data.msg, "error", 0);
    				return pageData;
    			}
    		}, function submitError(response) {
    			logService.setlog(response, "error", 0);
    			return pageData;
    		});
		}
	}
	function jsonizePageData(pageDataRow){
	    pageDataRow.dynaPageData_data = initVarsService.jsonize(pageDataRow.dynaPageData_data);
    					
    	for(let i=0; i<lodash.size(pageDataRow.pageCommand); i++){
    		pageDataRow.pageCommand[i]['dynaPageCommandData_commandParameters'] = initVarsService.jsonize(pageDataRow.pageCommand[i]['dynaPageCommandData_commandParameters']);
    	}
    
    	let pageData = {
    		pageLang: pageDataRow.dynaPageDetails_pageLang,
    		pageCode: pageDataRow.dynaPageDetails_pageId,
    		pageData: pageDataRow.dynaPageData_data,
    		pageVars: pageDataRow.dynaPageInitVariables_vars,
    		pageCommand: pageDataRow.pageCommand,
    		pageType: pageDataRow.dynaPageDetails_pageType, 
    		availableOffline: pageDataRow.dynaPageDetails_availableOffline
    	};
    	return pageData;
	}
	function importData(importTables, db, pageData, tries=3, tried=0, lastResponse=''){
		return $q(function (resolve, reject) {
		    try{
		        if(!importTables || !lodash.size(importTables)){
		            resolve({success: [], fail: []});
		        }
    			let localTableDetails = [];
    			let timeoutHolder = {};
    			let defaultValues = {};
    			for(let i=0; i<lodash.size(importTables); i++){
    			    try{
        				let importTable = importTables[i].dynaPageInitVariables_initialVal;
        				let postParameters = lodash.get(importTable, 'postParameters', []);
        				let queryParameters = lodash.get(postParameters, 'queryParameters', []);
        				let sendWithQueryParams = lodash.get(postParameters, 'sendWithQueryParams', []);
        				if(!importTables[i]['dynaPageInitVariables_initialVal']['postParameters'][importTables[i]['dynaPageInitVariables_initialVal']['parametersVar']]){
        				    importTables[i]['dynaPageInitVariables_initialVal']['postParameters'][importTables[i]['dynaPageInitVariables_initialVal']['parametersVar']] = {};
        				}
        				importTables[i]['process'] = 1;
                        
                        //run precall functions to set variables
                        let preCallFunctions = lodash.get(importTable, 'preCallFunctions', []);
                        
        				for(var j=0; j<lodash.size(preCallFunctions); j++){
        				    try{
            				    let preCallFunction = preCallFunctions[j];
                				let functionName = lodash.get(preCallFunction, 'functionName', undefined);
                				let functionPath = lodash.get(preCallFunction, 'functionPath', undefined);
                				let functionInputs = lodash.get(preCallFunction, 'input', []);
                				let outputVariableId = lodash.get(preCallFunction, 'outputVariableId');
                				if(functionName && functionPath){
                				    let parameters = [];
                					for(var t=0; t<lodash.size(functionInputs); t++){
                					    try{
                    						if(functionInputs[t].variableId){
                    							let inputVariableValue = initVarsService.readVariable(undefined, functionInputs[t].variableId, pageData.pageVars, vm);
                    							parameters.push(inputVariableValue);
                    						}
                					    }
                					    catch(e){
    		                                $log.error(e);
                        		            $log.error("An error occured in setting the following precall function input:");
                        		            $log.error(preCallFunctions[j]);
                        		            $log.error(functionInputs[t]);
                        		            reject({success: [], fail: [e]});
                					    }
                					}
                	
                					let outputVariable = pageData.pageVars.find(x => x.dynaPageInitVariables_variableId == outputVariableId);
                					let functionOutput;
                					if(functionPath == "window"){
                						functionOutput = eval(functionName).apply(this, parameters);
                					}else if(functionPath == "scope"){
                						functionOutput = vm[functionName].apply(this, parameters);
                					}else{
                						functionOutput = eval(lodash.get(functionPath, functionName)).apply(this, parameters);
                					}
        
                					if(functionOutput){
                					    try{
                    						outputVariable.dynaPageInitVariables_initialVal = functionOutput;
                    						outputVariable.replaceOldVal = 1;
                					    }
                					    catch(e){
    		                                $log.error(e);
                        		            $log.error("An error occured in setting the following precall function output. The following lines show: 1-preCallFunctions[j] that cause error, 2-outputVariableId that causes error in that function, 3-outputVariable value, 4-functionOutput, 5-e");
                        		            $log.error(preCallFunctions[j]);
                        		            $log.error(outputVariableId);
                        		            $log.error(outputVariable);
                        		            $log.error(functionOutput);
                        		            reject({success: [], fail: [e]});
                					    }
                					}
                									
                					let functionOutputArray = [];
                					functionOutputArray.push(outputVariable);
                	
                					initVarsService.setInitialVariables(functionOutputArray, undefined, vm);
                				}
        				    }
        				    catch(e){
		                        $log.error(e);
                    		    $log.error("An error occured in setting the following precall function:");
                    		    $log.error(preCallFunctions[j]);
                    		    reject({success: [], fail: [e]});
            				}
        				}
        				
        				let temporaryParamHolder = {};
        				for(var j=0; j<lodash.size(queryParameters); j++){
        				    try{
            					let setVariable = pageData.pageVars.find(x => x.dynaPageInitVariables_variableId == queryParameters[j].variableId);
            					if(setVariable){
            						let setVariableValue = initVarsService.readVariable(setVariable, '', undefined, vm);
            						if((setVariableValue === undefined || setVariableValue === null || setVariableValue === "" || (Array.isArray(setVariableValue) && !lodash.size(setVariableValue))) && queryParameters[j].isRequired){
            						    
            							importTables[i]['process'] = 0;
            							
            							watchedImportParameters[setVariable.dynaPageInitVariables_variableId] = {variable: setVariable, table: importTable, outputVar :setVariableValue};
            							
            							if(!watchForChange){
            							    var watchForChange = {};
            							}
            							
            							watchForChange[setVariable.dynaPageInitVariables_variableId] = $scope.$watch(function(){
            							    if(!timeoutHolder[setVariable.dynaPageInitVariables_variableId]){
                							    timeoutHolder[setVariable.dynaPageInitVariables_variableId] = $timeout(function(){
                							        logService.setlog(["skipped setting up variable as it took too long", {variable: setVariable}], "error");
                							        defaultValues[setVariable.dynaPageInitVariables_variableId] = '';
                							        
                							    },25000);
                							    logService.setlog(["Watch on "+setVariable.dynaPageInitVariables_variableId+" activated",{timeoutHolder: timeoutHolder, setVariable:setVariable, importTable:importTable}], "debug");
            							    }
            								if(watchedImportParameters[setVariable.dynaPageInitVariables_variableId]){
            								    try{
            								        watchedImportParameters[setVariable.dynaPageInitVariables_variableId].outputVar = initVarsService.readVariable(watchedImportParameters[setVariable.dynaPageInitVariables_variableId].variable, '', undefined, vm) || (defaultValues[setVariable.dynaPageInitVariables_variableId]);
            								    }catch(e){
            								        $log.error("Error in watcher");
            								        $log.debug(watchedImportParameters[setVariable.dynaPageInitVariables_variableId]);
            								    }
            								}
            								
            								return watchedImportParameters[setVariable.dynaPageInitVariables_variableId];
            							}, function(newValues, oldValues) {
            							    logService.setlog(["Watched variable changed", {newValues:newValues, oldValues:oldValues, timeoutHolder:timeoutHolder}], "debug");
            								let process = 1;
            								if(oldValues && newValues && newValues.variable && newValues.variable.dynaPageInitVariables_variableId && timeoutHolder[newValues.variable.dynaPageInitVariables_variableId] && defaultValues[newValues.variable.dynaPageInitVariables_variableId] == ''){
            								    logService.setlog(["Watched variable changed and timeout cancelled", {newValues:newValues, oldValues:oldValues, timeoutHolder:timeoutHolder}], "debug");
            								    $timeout.cancel(timeoutHolder[newValues.variable.dynaPageInitVariables_variableId]);
            								    delete watchedImportParameters[newValues.variable.dynaPageInitVariables_variableId];
                    						    resolve({});
            								}
            								try{
            								    if(newValues){
                    								if(newValues.outputVar === undefined || newValues.outputVar === null || newValues.outputVar === "" || (Array.isArray(newValues.outputVar) && !lodash.size(newValues.outputVar))){
                    									    
                    									if(oldValues && (newValues.outputVar !== oldValues.outputVar)){
                        								    $log.warn("Required variable for table import not set. variable: ");
                        								    $log.warn(newValues.variable);
                    									}
                    									process = 0;
                    								}
            								    }else{
            								        newValues = oldValues;
            								    }
            								}
            								catch(e){
            								    $log.error("Error in setting required variable");
            								    $log.error(newValues);
            								    $log.error(oldValues);
            								}
            								if(process){
            								    try{
                									let localTables = pageData.pageVars.filter(x => x.dynaPageInitVariables_type == 'localTable');
                									let pendingImportTableToProcess = [];
                									if(lodash.size(newValues) && newValues){
                    									pendingImportTableToProcess = localTables.filter(x => x.dynaPageInitVariables_initialVal && x.dynaPageInitVariables_initialVal.downloadData == 1 && x.dynaPageInitVariables_initialVal.localTableName == newValues.table.localTableName);
                    									
                    									importData(pendingImportTableToProcess, db, pageData, tries, tried, lastResponse).then(function(importResponse){

                    										if(newValues && newValues){
                    										    watchForChange[newValues.variable.dynaPageInitVariables_variableId]();
                    										    
                    										    delete watchedImportParameters[newValues.variable.dynaPageInitVariables_variableId];
                    									    }
                    										resolve(importResponse);
                    									});
                									}
            								    }
            								    catch(e){
            								        $log.error(e);
                        		                    $log.error("An error occured in setting the following queryParameters process:");
                        		                    $log.error(queryParameters[j]);
                        		                    $log.error(process);
                        		                    reject({success: [], fail: [e]});
            								    }
            								}
            							}, true);
            						}else if(setVariableValue === undefined || setVariableValue === null){
            							temporaryParamHolder[queryParameters[j]['name']] = lodash.get(queryParameters[j], 'default', '');
            						}else{
            							temporaryParamHolder[queryParameters[j]['name']] = setVariableValue;
            						}
            					}else{
            						if(queryParameters[j].isRequired){
            						    $log.warn("a required variable is not defined: "+queryParameters[j]['name']);
            							//importTables[i]['dynaPageInitVariables_initialVal']['url'] = "";
            							importTables[i]['process'] = 0;
            						}else{
            							temporaryParamHolder[queryParameters[j]['name']] = lodash.get(queryParameters[j], 'default', '');
            						}
            					}
        				    }
        				    catch(e){
                        		logService.setlog(["An error occured in setting the following queryParameters",{importTable: importTables[i], queryParameters: queryParameters[j], error:e}], "error");
                        		reject({success: [], fail: [e]});
        				    }
        				}
        				if(importTables[i]['process']){
        				    
        					for(var j=0; j<lodash.size(sendWithQueryParams); j++){
        					    try{
            						importTables[i]['dynaPageInitVariables_initialVal']['postParameters'][importTables[i]['dynaPageInitVariables_initialVal']['parametersVar']][sendWithQueryParams[j]['serverName']] = temporaryParamHolder[sendWithQueryParams[j]['localName']];
        					    }
        					    catch(e){
        					        logService.setlog(["And Error Occured in importing the following table Map in seting Query Param",{importTable: importTables[i], process: importTables[i]['process'], error:e}], "error");
        					        reject({success: [], fail: [e]});
        					    }
        					}
        					importTables[i]['dynaPageInitVariables_initialVal']['postParameters']['sendWithQueryParams'] = undefined;
        					importTables[i]['dynaPageInitVariables_initialVal']['postParameters']['queryParameters'] = undefined;
        				}
        			}
        			catch(e){
    		            logService.setlog(["Error in importing data for following tableMap",{importTable: importTables[i], error:e}], "error");
    		            reject({success: [], fail: [e]});
    		        }
		        }
    			var processed = 0;
    			var successResponse = [];
    			var failedResponse = [];
    			
    			for(let key=0; key < lodash.size(importTables); key++){
    			    try{
        				let importTable = importTables[key];
						logService.setlog(["processing table map import "+key,{importTable:importTable}], "debug");
        				if(importTable['process']){
        					let callBackFunctions = lodash.get(importTable, 'dynaPageInitVariables_initialVal.callbacks', []);
        					
        					if(1){
            					tableMapsService.importDataFromServer(db, importTable, 3, 0, lastResponse, {}).then(function(importResponse){
            	
            						processed = processed + 1;
									logService.setlog("processing table map import "+key+" done", "debug");
            						successResponse.push(importResponse);
            						
            						let succssCallBackFunctions = callBackFunctions.filter(item => item.runWhen == "success");
									logService.setlog(["succssCallBackFunctions",{succssCallBackFunctions:succssCallBackFunctions}], "debug");
            						for(let i=0; i<lodash.size(succssCallBackFunctions); i++){
            						    try{
                							let callBackFunction = callBackFunctions[i];
											logService.setlog(["processing callback function "+i,{callBackFunction: callBackFunction}], "debug");
                							let functionName = lodash.get(callBackFunction, 'functionName', undefined);
                							let functionPath = lodash.get(callBackFunction, 'functionPath', undefined);
                							let functionInputs = lodash.get(callBackFunction, 'input', []);
                							let outputVariableId = lodash.get(callBackFunction, 'outputVariableId');
                							if(functionName && functionPath){
                								let parameters = [];
                								for(let t=0; t<lodash.size(functionInputs); t++){
                									if(functionInputs[t].variableId){
                										let inputVariableValue = initVarsService.readVariable(undefined, functionInputs[t].variableId, pageData.pageVars, vm);
                										parameters.push(inputVariableValue);
                									}
                								}
        
                								let outputVariable = pageData.pageVars.find(item => item.dynaPageInitVariables_variableId == outputVariableId);
                								let functionOutput;
                								if(functionPath == "window"){
                									functionOutput = eval(functionName).apply(this, parameters);
                								}else if(functionPath == "scope"){
                									functionOutput = vm[functionName].apply(this, parameters);
                								}else{
                									functionOutput = eval(lodash.get(functionPath, functionName)).apply(this, parameters);
                								}
                	
                								if(functionOutput){
                									outputVariable.dynaPageInitVariables_initialVal = functionOutput;
                									outputVariable.replaceOldVal = 1;
                								}
                									
                								let functionOutputArray = [];
                								functionOutputArray.push(outputVariable);
                	
                								initVarsService.setInitialVariables(functionOutputArray, undefined, vm);
                							}
            						    }
            						    catch(e){
            						        $log.error(e);
                            		        $log.error("An error occured in importing the following tableMap callback function:");
                            		        $log.error(succssCallBackFunctions[i]);
                            		        $log.error(importTables[key]);
                            		        reject({success: [], fail: [e]});
            						    }
            						}
            							
            						//set tableMap related vars
									logService.setlog("set tableMap related vars", "debug");
            						try{
                						let localTableRelatedVars = pageData.localTableRelatedVars.filter(item => item.dynaPageInitVariables_initialVal && item.dynaPageInitVariables_initialVal.localTableName==importTable.dynaPageInitVariables_initialVal.localTableName);
                						logService.setlog(["localTableRelatedVars", {localTableRelatedVars: localTableRelatedVars}], "debug");
										initVarsService.setInitialVariables(localTableRelatedVars, pageData.pageVars, vm);
                						logService.setlog(["localTableRelatedVars done",{processed:processed, importTables:importTables}] , "debug");
                						if(processed == lodash.size(importTables)){
											logService.setlog("all import tables processed", "debug");
                							resolve({success: successResponse, fail: failedResponse});
                						}
            						}
            						catch(e){
            						    $log.error(e);
                            		    $log.error("Ex2: An error occured in importing the following tableMap:");
                            		    $log.error(importTables[key]);
                            		    reject({success: [], fail: [e]});
            						}
            					}, function(importResponse) {
            						processed = processed + 1;
            						failedResponse.push(importResponse);
            						if(processed == lodash.size(importTables)){
            							resolve({success: successResponse, fail: failedResponse});
            						}
            					});
        					}
        				}
        			}
        			catch(e){
        		        $log.error(e);
        		        $log.error("Ex3: An error occured in importing the following tableMap:");
        		        $log.error(importTables[key]);
        		        reject({success: [], fail: [e]});
        		    }
    		    }
    		}
    		catch(importErrorResponse){
    		    logService.setlog(["error while importing data",{importTables: importTables, importErrorResponse: importErrorResponse}], "error");
    		    reject({success: [], fail: [importErrorResponse]});
    		}
		});
	}
	
    let saveDeviceId = function(deviceId, status=1){
        logService.setlog(["saving device id", {deviceId: deviceId}]);
        let params = {
    		action: "saveDevice",
    		deviceId: deviceId,
            deviceStatus: status
    	};
    	return httpService.request({
    		method : "POST",
    		cache: false,
    		data: params,
    		url : "/dynaPage.php",
    		headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
    	}).then(function submitSuccess(response) {
            logService.setlog(["device id saved", {response: response}]);
    		if(response.data.status){
                
            }
        }, function(error){
            
        });
    }
    
	localDb.initilizeDb().then(function(db){
	    vm.db = db;
        
        vm.deviceId = '';
		let deviceId = '';
		let localTable = localDb.getTable('localTable', db);
		let deviceIdArray = localDb.advancedSelectItem('localTable', {name: "deviceDetails"}, db, {limit: 1}, localTable);
		if(deviceIdArray && deviceIdArray[0] && deviceIdArray[0].value){
			deviceId = deviceIdArray[0].value.deviceId;
            let deviceParameters = {
                deviceDetails_deviceId: deviceId,
                deviceDetails_deviceId_nullcheck: deviceId,
                start:0,
                limit:1
            };
            logService.setlog("Device Id existed: "+deviceId, "debug");
            runServerCommand("deviceDetailsSearch", deviceParameters).then(function(deviceDetailsResponse){
                logService.setlog(["Check deviceId status: ",{deviceDetailsResponse: deviceDetailsResponse}], "debug");
                if(deviceDetailsResponse && deviceDetailsResponse.data && deviceDetailsResponse.data.deviceDetails && deviceDetailsResponse.data.deviceDetails.result && deviceDetailsResponse.data.deviceDetails.result.rows && deviceDetailsResponse.data.deviceDetails.result.rows.length){
                    let deviceDetails = deviceDetailsResponse.data.deviceDetails.result.rows[0];
                    let deviceStatus = deviceDetails.deviceDetails_status;
                    if(deviceStatus == 0){
                        document.body.innerHTML = '<div style="margin-top:auto;margin-bottom:auto;width:100%;border:1px red solid;text-align:center;color:red;">This device is disabled by administrator<br />"'+deviceId+'"</div>';
                        deviceDisabled = 1;
                        deviceIdArray[0].value.status = 0;
                        localDb.updateItem('localTable', deviceIdArray, localTable, db);
                        throw new Error('This device is disabled by administrator '+deviceId);
                    }else{
                        logService.setlog("Device Id is active: "+deviceId, "debug");
                    }
                }else{
                    saveDeviceId(deviceId);
                }
            });
		}else{
			deviceId = guid.newuuid('deviceId');
			let deviceIdIdToInsertArray = [
				{
					name: "deviceDetails",
					value: {deviceId: deviceId, status: 1}
				}
			];
			localDb.insertItem('localTable', deviceIdIdToInsertArray, localTable);
			localDb.saveDb(db);
            saveDeviceId(deviceId);
			logService.setlog("Device Id created: "+deviceId);
		}
		vm.deviceId = deviceId;
                
	    let enablePageEdit = localDb.advancedSelectItem("localTable", {name: "enablePageEdit"}, db, {limit: 1});
	    if(enablePageEdit && enablePageEdit.length){
	        vm.enablePageEdit = enablePageEdit[0].value;
	    }
	    
	    logService.setlog(["db setup",{vmdb:vm.db, db:db}], "debug");
	    logService.setlog("getting details of "+pageCode, "debug");
    	getPageDetails(pageCode).then(function(pageData){
    	    ///check commands before load
    	    runPageCommands(pageData, 'beforePageLoad').then(function(){
        		//Set initial variables except localTable(set below)
        		let localTableNames = [];
        		let localTables = [];
        		if(pageData.pageVars && lodash.size(pageData.pageVars)){
        		    try{
                		let setPageVarTypes = ["var", "scope", "dynascope", "dynavar", "localStorage", "dynaLocalStorage"];
                		let pageVars = pageData.pageVars.filter(item => setPageVarTypes.indexOf(item.dynaPageInitVariables_type) >= 0);
                
                		pageVars = lodash.orderBy(pageVars, ['dynaPageInitVariables_setPriority'], ['desc']);
                		initVarsService.setInitialVariables(pageVars, undefined, vm);
                
                		//Set initial localTable
                		localTables = pageData.pageVars.filter(item => item.dynaPageInitVariables_type == 'localTable');
                		
                		for(var i=0; i<lodash.size(localTables); i++){
                			localTableNames.push(localTables[i].dynaPageInitVariables_variableName);
                		}
        		    }
        		    catch(e){
        		        logService.setlog(e, "error");
        		        logService.setlog("Error setting initial vars", "error");
        		        logService.setlog(pageData.pageVars, "error");
        		    }
        		}
        		logService.setlog(["Getting following tableMaps", localTableNames], "debug");
        		tableMapsService.getTableMaps(localTableNames, db).then(function(tableMapsResponse){
        		    logService.setlog(['tableMaps imported', {tableMapsResponse:tableMapsResponse}], 'debug');
        			if(tableMapsResponse.status){
                        let tableMapsDb = localDb.getTable('tableMaps', db);
        				for(var i=0; i<lodash.size(localTables); i++){
        				    try{
        					    localTables[i].dynaPageInitVariables_initialVal = initVarsService.jsonize(localTables[i].dynaPageInitVariables_initialVal);
        				    }
        				    catch(e){
        				        logService.setlog("Error jsonizing local table initval", "error");
        				        logService.setlog(localTables[i], "error");
        				        logService.setlog(e, "error");
        				    }
        				};
        				
        				//set initial values for localTable variables-download data from server
        				let importTables = localTables.filter(x => x.dynaPageInitVariables_initialVal && x.dynaPageInitVariables_initialVal.downloadData == 1);
        				
        				//localtable related vars
        				let localTableRelatedVars = [];
        				if(pageData.pageVars && lodash.size(pageData.pageVars)){
            				let setPageVarTypes2 = ["localTableVar", "localTableScope"];
            				localTableRelatedVars = pageData.pageVars.filter(item => setPageVarTypes2.indexOf(item.dynaPageInitVariables_type) >= 0);
            				localTableRelatedVars = lodash.orderBy(localTableRelatedVars, ['dynaPageInitVariables_setPriority'], ['desc']);
            				if(localTableRelatedVars){
                				for(var i=0; i<lodash.size(localTableRelatedVars); i++){
                					localTableRelatedVars[i].dynaPageInitVariables_initialVal = initVarsService.jsonize(localTableRelatedVars[i].dynaPageInitVariables_initialVal);
                				}
            				}
        				}
        				pageData.localTableRelatedVars = localTableRelatedVars;
                        
                        //Set those localtable related vars that does not have local table downlload in current page
                        let localTableRelatedVarsWithoutTableDownloads = pageData.localTableRelatedVars.filter( x => x.dynaPageInitVariables_initialVal && !importTables.find(el => x.dynaPageInitVariables_initialVal.localTableName == el.dynaPageInitVariables_initialVal.localTableName) );
                        
                        if(localTableRelatedVarsWithoutTableDownloads && localTableRelatedVarsWithoutTableDownloads.length){
                            
                            logService.setlog(["The following variables are related to local tables but their tables are not being downloaded/updated in the current page, and this is not a good practice unless they are tablemap related. However, I try to set them if the local table exists.", localTableRelatedVarsWithoutTableDownloads], "info");
                            initVarsService.setInitialVariables(localTableRelatedVarsWithoutTableDownloads, pageData.pageVars, vm);
                        }
                        //End
        				importData(importTables, vm.db, pageData).then(function(importResponse){
        					if(Object.keys(watchedImportParameters) && lodash.size(Object.keys(watchedImportParameters))){
        					    logService.setlog("watting to finish setting up variables", "info");
        					    logService.setlog(watchedImportParameters, "info");
        					    var setVarsTimer = setTimeout(function(){
        					        let confirmProceed = confirm("I could not set some variables! Do you want to proceed? Beware some features might not work properly.");
        					        if(confirmProceed){
        					            vm.showPage(pageData, vm.enablePageEdit);
        					        }
        					    }, 25000);
        						var watchForchange2 = $scope.$watch(function(){return watchedImportParameters;}, function(newVal, oldVal){
        						    logService.setlog(["Watch on watchedImportParameters activated", {newVal: newVal, oldVal:oldVal}], "debug");
        							if(!Object.keys(newVal) || !lodash.size(Object.keys(newVal))){
        								watchForchange2();
        								clearTimeout(setVarsTimer);
        								vm.showPage(pageData, vm.enablePageEdit);
        							}
        						}, true);
        					}else{
        						vm.showPage(pageData, vm.enablePageEdit);
        					}
        				}, function(importErrorResponse){
        				    logService.setlog(importErrorResponse, "error");
        				});
        			}
        		});
    	    }, function(error){
    	        logService.setlog(["Error happened while loading page", {error:error, pageData:pageData}], "error");
    	    });
    	});
	});
});