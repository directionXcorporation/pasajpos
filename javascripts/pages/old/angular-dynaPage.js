{{_PAGE_INJECTIONS_JS}}

{{_PAGE_MODULES_JS}}

app.controller('parentobj.controllers.dynaPage.{{_PAGE_CODE}}', function($scope, $timeout, $http, $injector, $log, i18nService, tableMapsService, lodash, initVarsService, localStorageService, gridsterService, onlineStatus, functionAccess, localDb, loginService, moment, $mdSidenav, $mdBottomSheet, $transitions {{_INJECTIONS}}) {
    let vm = this;
    vm.checkFunctionAccess = functionAccess.checkAccess;
    vm.pageIsLoading = 1;
    
    {{_PAGE_FUNCTIONS}}
    
	vm.pageCode = "";
	let pageContent;
	var pageCode = "{{_PAGE_CODE}}";

	let pageLocalDb = {};
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
	//END
	vm.stateParams = stateParams;
	if(stateParams.data){
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
	    
	    return $http({
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
	vm.readVariable = function(pageVar=undefined, pageVarId='', pageVars=undefined){
	    try{
    		if(!pageVar && pageVarId && pageVars){
    			pageVar = pageVars.find(item => item.dynaPageInitVariables_variableId == pageVarId);
    		}
    
    		var variableValue = undefined;
    		var scopeTypes = ["scope", "dynascope", "localTableScope"];
    		var varTypes = ["var", "dynavar", "localTableVar"];
    		var localStorageTypes = ["localStorage", "dynaLocalStorage"];
    		if(scopeTypes.indexOf(pageVar.dynaPageInitVariables_type) > -1){
    			if(vm[pageVar.dynaPageInitVariables_variableName]){
    				variableValue = vm[pageVar.dynaPageInitVariables_variableName];
    			}
    		}else if(localStorageTypes.indexOf(pageVar.dynaPageInitVariables_type) > -1){
    			variableValue = localStorageService.get(pageVar.dynaPageInitVariables_variableName);
    		}else if(varTypes.indexOf(pageVar.dynaPageInitVariables_type) > -1){
    			variableValue = window[pageVar.dynaPageInitVariables_variableName];
    		}
    		return variableValue;
	    }
	    catch(e){
	        $log.error(e);
	        $log.error("Error happened while reading variable: "+pageVarId);
	        $log.error(pageVar);
	        return "";
	    }
	}
	
	function getOutput(data, pathInfo){
		let groupBy = pathInfo.groupBy
		let pathName = pathInfo.name;
		let tempOutput = [];
		if(data.length && data instanceof Object){
			for(var t=0; t<lodash.size(data); t++){
				if(groupBy){
					if(!tempOutput[lodash.get(data[t], groupBy, '')]){
						tempOutput[lodash.get(data[t], groupBy, '')] = [];
					}
					tempOutput[lodash.get(data[t], groupBy, '')].extend(lodash.get(data[t], pathName));
				}else{
					let val = lodash.get(data[t], pathName);
					if(Array.isArray(val)){
						angular.extend(tempOutput, val);
					}else{
					    tempOutput.push(val);
					}
				}
			}
		}else if(data instanceof Object){
			angular.extend(tempOutput, lodash.get(data, pathName));
		}
		return tempOutput;
	}
	function setInitialVariables(pageVars, allPageVars=undefined){
	    try{
    		if(pageVars){
    			for(var i=0; i<lodash.size(pageVars); i++){
    				let varDetails = pageVars[i];
    				//TO DO: MAKE IT ABLE TO READ . NOTATION CHIDLREN
    				if(varDetails.dynaPageInitVariables_initialVal && typeof varDetails.dynaPageInitVariables_initialVal === "string"){
    					varDetails.dynaPageInitVariables_initialVal = varDetails.dynaPageInitVariables_initialVal.replace(/[\t\n\r]/gm,'');
    				}
    				let oldValue = vm.readVariable(varDetails);
    				if(varDetails.dynaPageInitVariables_type =="scope"){
    					if(!oldValue || varDetails.replaceOldVal){
    						vm[varDetails.dynaPageInitVariables_variableName] = varDetails.dynaPageInitVariables_initialVal;
    					}
    				}else if(varDetails.dynaPageInitVariables_type =="dynascope"){
    					if(!oldValue || varDetails.replaceOldVal){
    						varDetails.dynaPageInitVariables_initialVal = initVarsService.jsonize(varDetails.dynaPageInitVariables_initialVal);
    						vm[varDetails.dynaPageInitVariables_variableName] = varDetails.dynaPageInitVariables_initialVal;
    					}
    				}else if(varDetails.dynaPageInitVariables_type =="var"){
    					if(!oldValue || varDetails.replaceOldVal){
    						window[varDetails.dynaPageInitVariables_variableName] = varDetails.dynaPageInitVariables_initialVal;
    					}
    				}else if(varDetails.dynaPageInitVariables_type =="dynavar"){
    					if(!oldValue || varDetails.replaceOldVal){
    						varDetails.dynaPageInitVariables_initialVal = initVarsService.jsonize(varDetails.dynaPageInitVariables_initialVal);
    						window[varDetails.dynaPageInitVariables_variableName] = varDetails.dynaPageInitVariables_initialVal;
    					}
    				}else if(varDetails.dynaPageInitVariables_type =="dynaLocalStorage"){
    					if(!oldValue || varDetails.replaceOldVal){
    						varDetails.dynaPageInitVariables_initialVal = initVarsService.jsonize(varDetails.dynaPageInitVariables_initialVal);
    						localStorageService.set(varDetails.dynaPageInitVariables_variableName, varDetails.dynaPageInitVariables_initialVal);
    					}
    				}else if(varDetails.dynaPageInitVariables_type =="localStorage"){
    					if(!oldValue || varDetails.replaceOldVal){
    						localStorageService.set(varDetails.dynaPageInitVariables_variableName, varDetails.dynaPageInitVariables_initialVal);
    					}
    				}else if(varDetails.dynaPageInitVariables_type =="localTableVar"){
    					if(!oldValue || varDetails.replaceOldVal){
    						let initialVal = varDetails.dynaPageInitVariables_initialVal;
    						let localTableName = initialVal.localTableName;
    						let searchCondition = JSON.stringify(initialVal.searchCondition);
    						let parameters = initialVal.parameters;
    						let path = initialVal.path;
    						let variables = initialVal.variables;
    						for(var j=0; j<lodash.size(variables); j++){
    							let value = '';
    							value = vm.readVariable(undefined, variables[j]['variableId'], allPageVars);
    							searchCondition = searchCondition.replace(variables[j]['name'], value);
    						}
    						searchCondition = initVarsService.jsonize(searchCondition);
    						let foundData = localDb.advancedSelectItem(localTableName, searchCondition, pageLocalDb.db, parameters);

    						path = lodash.sortBy(path, ['order']);
    						for(var j=0; j<lodash.size(path); j++){
    							foundData = getOutput(foundData, path[j]);
    							if(path[j].index !== undefined && path[j].index !== ""){
    							    foundData = foundData[path[j].index];
    							}
    						}

    						window[varDetails.dynaPageInitVariables_variableName] = foundData;
    					}
    				}else if(varDetails.dynaPageInitVariables_type =="localTableScope"){
    					if(!oldValue || varDetails.replaceOldVal){
    						let initialVal = varDetails.dynaPageInitVariables_initialVal;
    						let localTableName = initialVal.localTableName;
    						let searchCondition = JSON.stringify(initialVal.searchCondition);
    						let parameters = initialVal.parameters;
    						let path = initialVal.path;
    						let variables = initialVal.variables;
    						for(var j=0; j<lodash.size(variables); j++){
    							let value = '';
    							value = vm.readVariable(undefined, variables[j]['variableId'], allPageVars);
    							searchCondition = searchCondition.replace(variables[j]['name'], value);
    						}
    						searchCondition = initVarsService.jsonize(searchCondition);
    						let foundData = localDb.advancedSelectItem(localTableName, searchCondition, pageLocalDb.db, parameters);
    						path = lodash.sortBy(path, ['order']);
    						for(var j=0; j<lodash.size(path); j++){
    							foundData = getOutput(foundData, path[j]);
    							if(path[j].index !== undefined && path[j].index !== ""){
    							    foundData = foundData[path[j].index];
    							}
    						}
    						vm[varDetails.dynaPageInitVariables_variableName] = foundData;
    					}
    				}
    			}
    		}
    	}
    	catch(e){
    	    $log.error(e);
    	    $log.error("Error happened while setting variable:");
    	    $log.error(pageVars);
    	}
	}

	vm.runCommands = function(pageCommand){
		if(lodash.size(pageCommand)){
			for(var i=0; i<lodash.size(pageCommand); i++){
				let rowDetails = pageCommand[i];
				if(rowDetails.dynaPageCommandData_commandSource == "local"){
					try {
						if(angular.isFunction(vm[rowDetails.dynaPageCommandData_commandText])){
							$scope.$eval(rowDetails.dynaPageCommandData_commandText)();
						}else{
							if(typeof window[rowDetails.dynaPageCommandData_commandText]=== "function"){
								eval(window[rowDetails.dynaPageCommandData_commandText]());
							}else{
								eval(rowDetails.dynaPageCommandData_commandText);
							}
						}
					} catch (e) {
						if (e instanceof SyntaxError) {
							logService.setlog(e, "error", 0);
						}else{
							logService.setlog(e, "error", 0);
						}
					}
				}
				
				//SET PARAMETERS AFTER THIS COMMAND IS DONE
				for(let j=0; j<lodash.size(rowDetails.dynaPageId_dynaPageCommandDataId_parameters); j++){
					if(rowDetails.dynaPageId_dynaPageCommandDataId_parameters[j].parameterOut && window[rowDetails.dynaPageId_dynaPageCommandDataId_parameters[j].parameterIn]){
						vm[rowDetails.dynaPageId_dynaPageCommandDataId_parameters[j].parameterOut] = window[rowDetails.dynaPageId_dynaPageCommandDataId_parameters[j].parameterIn];
					}else if(rowDetails.dynaPageId_dynaPageCommandDataId_parameters[j].parameterOut){
						vm[rowDetails.dynaPageId_dynaPageCommandDataId_parameters[j].parameterOut] = "";
					}
				}
			}
		}
	}
	
	vm.showPage = function(pageData={}){
		//RUN COMMANDS
		//TODO: set parameters for use in page. Also run serve query if required. be bale to set query parameters in both(dynaPageSqlData_sqlParameters)
		if(pageData.pageCommand){
			let pageLoadCommands = lodash.filter(pageData.pageCommand, {dynaPageCommandCondition_conditionType: 'event', dynaPageCommandCondition_conditionText: 'pageLoad', dynaPageCommandData_commandSource: 'local'});
			vm.runCommands(pageLoadCommands);
		}

		vm.pageCode = pageData.pageCode;
		let dialogType = "";
		pageContent = pageData.pageData;
        
		let selectedScreenIndex = vm.data.screens.findIndex(item=>item.screenName == 'main');
		initPage(selectedScreenIndex, pageContent);
		vm.pageIsLoading = 0;
	}
	function getPageDetails(pgCode) {
		logService.setlog("Fetching Page Details", "info", 1);
		var pageData = {};
		
		let parameters = {limit: 1};
		let searchCondition = {dynaPageDetails_pageId: pgCode, dynaPageDetails_availableOffline: 1};
        
		let pageDataRow = localDb.advancedSelectItem("dynaPages", searchCondition, pageLocalDb.db, parameters);
		if(pageDataRow && pageDataRow.length && !onlineStatus.isOnline){
		    logService.setlog(["data for "+pgCode+" found in local database",pageDataRow]);
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
    		return $http({
    			method : "POST",
    			cache: false,
    			data: params,
    			url : "/dynaPage.php",
    			headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
    		}).then(function submitSuccess(response) {
    			if(response.data.status){
    				if(response.data.pageDetails && response.data.pageDetails.rows && response.data.pageDetails.rows[0]){
    					let pageDataRow = response.data.pageDetails.rows[0];
    					logService.setlog(["data for "+pgCode+" found online",pageDataRow]);
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
    			
    			for(i=0; i<lodash.size(importTables); i++){
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
                    							let inputVariableValue = vm.readVariable(undefined, functionInputs[t].variableId, pageData.pageVars);
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
                									
                					functionOutputArray = [];
                					functionOutputArray.push(outputVariable);
                	
                					setInitialVariables(functionOutputArray);
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
            						let setVariableValue = vm.readVariable(setVariable);
            						if((setVariableValue === undefined || setVariableValue === null || setVariableValue === "" || (Array.isArray(setVariableValue) && !lodash.size(setVariableValue))) && queryParameters[j].isRequired){
            						    
            							importTables[i]['process'] = 0;
            							
            							watchedImportParameters[setVariable.dynaPageInitVariables_variableId] = {variable: setVariable, table: importTable, outputVar :setVariableValue};
            							
            							if(!watchForChange){
            							    var watchForChange = {};
            							}
            							
            							watchForChange[setVariable.dynaPageInitVariables_variableId] = $scope.$watch(function(){
            								if(watchedImportParameters[setVariable.dynaPageInitVariables_variableId]){
            								    try{
            								        watchedImportParameters[setVariable.dynaPageInitVariables_variableId].outputVar = vm.readVariable(watchedImportParameters[setVariable.dynaPageInitVariables_variableId].variable);
            								    }catch(e){
            								        $log.error("Error in watcher");
            								        $log.debug(watchedImportParameters[setVariable.dynaPageInitVariables_variableId]);
            								    }
            								}
            								
            								return watchedImportParameters[setVariable.dynaPageInitVariables_variableId];
            							}, function(newValues, oldValues) {
            								let process = 1;
            								try{
            								    if(newValues){
                    								if(newValues.outputVar === undefined || newValues.outputVar === null || newValues.outputVar === "" || (Array.isArray(newValues.outputVar) && !lodash.size(newValues.outputVar))){
                    									    
                    									if(newValues.outputVar !== oldValues.outputVar){
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
        				        $log.error(e);
                        		$log.error("An error occured in setting the following queryParameters:");
                        		$log.error(queryParameters[j]);
                        		reject({success: [], fail: [e]});
        				    }
        				}
        				if(importTables[i]['process']){
        				    
        					for(var j=0; j<lodash.size(sendWithQueryParams); j++){
        					    try{
            						importTables[i]['dynaPageInitVariables_initialVal']['postParameters'][importTables[i]['dynaPageInitVariables_initialVal']['parametersVar']][sendWithQueryParams[j]['serverName']] = temporaryParamHolder[sendWithQueryParams[j]['localName']];
        					    }
        					    catch(e){
        					        $log.error(e);
        					        $log.error("And Error Occured in importing the following table Map in seting Query Param");
        					        $log.error(importTables[i]['process']);
        					        $log.error(importTables[i]);
        					        reject({success: [], fail: [e]});
        					    }
        					}
        					importTables[i]['dynaPageInitVariables_initialVal']['postParameters']['sendWithQueryParams'] = undefined;
        					importTables[i]['dynaPageInitVariables_initialVal']['postParameters']['queryParameters'] = undefined;
        				}
        			}
        			catch(e){
    		            $log.error(e);
    		            $log.error("Ex1: An error occured in importing the following tableMap:");
    		            $log.error(importTables[i]);
    		            reject({success: [], fail: [e]});
    		        }
		        }
    			var processed = 0;
    			var successResponse = [];
    			var failedResponse = [];
    			
    			for(var key=0; key < lodash.size(importTables); key++){
    			    try{
        				let importTable = importTables[key];
        				if(importTable['process']){
        					let callBackFunctions = lodash.get(importTable, 'dynaPageInitVariables_initialVal.callbacks', []);
        					
        					if(1){
            					tableMapsService.importDataFromServer(db, importTable, 3, 0, lastResponse, {}).then(function(importResponse){
            	
            						processed = processed + 1;
            						successResponse.push(importResponse);
            						
            						let succssCallBackFunctions = callBackFunctions.filter(item => item.runWhen == "success");
            						for(var i=0; i<lodash.size(succssCallBackFunctions); i++){
            						    try{
                							let callBackFunction = callBackFunctions[i];
                							let functionName = lodash.get(callBackFunction, 'functionName', undefined);
                							let functionPath = lodash.get(callBackFunction, 'functionPath', undefined);
                							let functionInputs = lodash.get(callBackFunction, 'input', []);
                							let outputVariableId = lodash.get(callBackFunction, 'outputVariableId');
                							if(functionName && functionPath){
                								let parameters = [];
                								for(var t=0; t<lodash.size(functionInputs); t++){
                									if(functionInputs[t].variableId){
                										let inputVariableValue = vm.readVariable(undefined, functionInputs[t].variableId, pageData.pageVars);
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
                									
                								functionOutputArray = [];
                								functionOutputArray.push(outputVariable);
                	
                								setInitialVariables(functionOutputArray);
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
            						try{
                						let localTableRelatedVars = pageData.localTableRelatedVars.filter(item => item.dynaPageInitVariables_initialVal && item.dynaPageInitVariables_initialVal.localTableName==importTable.dynaPageInitVariables_initialVal.localTableName);
                						setInitialVariables(localTableRelatedVars, pageData.pageVars);
                						
                						if(processed == lodash.size(importTables)){
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
    		    reject({success: [], fail: [importErrorResponse]});
    		}
		});
	}
	
	localDb.initilizeDb().then(function(db){
	    vm.db = db;
	    pageLocalDb.db = db;
	    
    	getPageDetails(pageCode).then(function(pageData){
    		//Set initial variables except localTable(set below)
    		let localTableNames = [];
    		let localTables = [];
    		if(pageData.pageVars && lodash.size(pageData.pageVars)){
    		    try{
            		let setPageVarTypes = ["var", "scope", "dynascope", "dynavar", "localStorage", "dynaLocalStorage"];
            		let pageVars = pageData.pageVars.filter(item => setPageVarTypes.indexOf(item.dynaPageInitVariables_type) >= 0);
            
            		pageVars = lodash.orderBy(pageVars, ['dynaPageInitVariables_setPriority'], ['desc']);
            		setInitialVariables(pageVars);
            
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
    			if(tableMapsResponse.status){
                    pageLocalDb.tableMapsDb = localDb.getTable('tableMaps', db);
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
    				/*
    				//Set localtable related vars that are related to tablemaps=>merged with the next block
    				let localTableRelatedVarsForTableMaps = pageData.localTableRelatedVars.filter(item => item.dynaPageInitVariables_initialVal && item.dynaPageInitVariables_initialVal.localTableName=='tableMaps');
                    setInitialVariables(localTableRelatedVarsForTableMaps, pageData.pageVars);
                    //End
                    */
                    
                    //Set those localtable related vars that does not have local table downlload in current page
                    let localTableRelatedVarsWithoutTableDownloads = pageData.localTableRelatedVars.filter( x => x.dynaPageInitVariables_initialVal && !importTables.find(el => x.dynaPageInitVariables_initialVal.localTableName == el.dynaPageInitVariables_initialVal.localTableName) );
                    
                    if(localTableRelatedVarsWithoutTableDownloads && localTableRelatedVarsWithoutTableDownloads.length){
                        logService.setlog(["The following variables are related to local tables but their tables are not being downloaded/updated in the current page, and this is not a good practice unless they are tablemap related. However, I try to set them if the local table exists.", localTableRelatedVarsWithoutTableDownloads], "info");
                        setInitialVariables(localTableRelatedVarsWithoutTableDownloads, pageData.pageVars);
                    }
                    //End
                    	$log.debug(vm.db);
    				importData(importTables, pageLocalDb.db, pageData).then(function(importResponse){
    					if(Object.keys(watchedImportParameters) && lodash.size(Object.keys(watchedImportParameters))){
    					    logService.setlog("watting to finish setting up variables", "info");
    					    logService.setlog(watchedImportParameters, "info");
    					    var setVarsTimer = setTimeout(function(){
    					        let confirmProceed = confirm("I could not set some variables! Do you want to proceed? Beware some features might not work properly.");
    					        if(confirmProceed){
    					            vm.showPage(pageData);
    					        }
    					    }, 25000);
    						var watchForchange2 = $scope.$watch(function(){return watchedImportParameters;}, function(newVal, oldVal){
    							if(!Object.keys(newVal) || !lodash.size(Object.keys(newVal))){
    								watchForchange2();
    								clearTimeout(setVarsTimer);
    								vm.showPage(pageData);
    							}
    						}, true);
    					}else{
    						vm.showPage(pageData);
    					}
    				}, function(importErrorResponse){
    				    logService.setlog(importErrorResponse, "error");
    				});
    			}
    		});
    	});
	});
	
	logService.setlog(pageLocalDb,"info")
});