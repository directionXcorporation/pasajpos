function(localDb, lodash, logService){
	let createStringPropertyObject = function(obj,is, value) {
		if (typeof is == 'string'){
			return createStringPropertyObject(obj,is.split('.'), value);
		}else if (is.length==1 && value!==undefined){
			return obj[is[0]] = value;
		}else if (is.length==0){
			return obj;
		}else{
			if(!obj[is[0]]){
				obj[is[0]] = {};
			}
			return createStringPropertyObject(obj[is[0]],is.slice(1), value);
		}
	}
	let initTableMapVarColumns = function(mapElement, initialMaps, tableMap){
		let element = {};
		if(mapElement['columnsMap']){
			if(initialMaps && initialMaps.indexOf(mapElement['mapId'])>=0){
				let columnMap = mapElement['columnsMap'];
				for(var i=0; i<columnMap.length; i++){
					let elementTemp = {};
					createStringPropertyObject(elementTemp, columnMap[i]['localColumn'], '');
					lodash.merge(element, elementTemp);
				}
			}
		}
		if(mapElement['collectionsMap']){
			if(initialMaps && initialMaps.indexOf(mapElement['mapId'])>=0){
				let collectionMap = mapElement['collectionsMap'];
				for(let i=0; i<collectionMap.length; i++){
					element[collectionMap[i].localColumn] = [];
					if(initialMaps && initialMaps.indexOf(collectionMap[i]['mapId'])>=0){
						let collectionMapElement = tableMap.find(x => x.mapId == collectionMap[i]['mapId']);
						element[collectionMap[i]['localColumn']].push(initTableMapVarColumns(collectionMapElement, initialMaps, tableMap));
					}
				}
			}
		}
		return element;
	}
	let initTableMapVar = function(tableMapName, db, initialMaps=undefined){ //initialMaps is an array of mapIds we want an empty initial value for ['','']
		let value = [];
		let element = {};
		let mapElement = {};
		let tableMapsDb = localDb.getTable('tableMaps', db);
		let tableMapDetails = tableMapsDb.findOne({dynaPageTableMap_localTable: tableMapName});
		
		tableMapDetails = tableMapDetails['dynaPageTableMap_columnMap'];
		tableMapDetails = jsonize(tableMapDetails);
		if(tableMapDetails && tableMapDetails[0]){
			mapElement = tableMapDetails[0];
		}
		
		element = initTableMapVarColumns(mapElement, initialMaps, tableMapDetails);
		value.push(element);
		return value;
	}
	let jsonize = function(input){
		var output;
		if(input){
			output = input;
			if(typeof input === "string"){
				let temp = lodash.attempt(JSON.parse.bind(null, input));
				if(typeof temp === "object"){
					output = temp;
				}
			}
		}else{
			output = {};
		}
		return output;
	}
	let setInitialVariables = function(pageVars, allPageVars=undefined, vm){
		let pageVarSize = lodash.size(pageVars);
		logService.setlog(["setting pageVars",{pageVars:pageVars, pageVarSize:pageVarSize}],"debug");
	    try{
    		if(pageVars && pageVarSize){
    			for(let i=0; i<pageVarSize; i++){
    				let varDetails = pageVars[i];
					logService.setlog(["setting pageVar "+i,{varDetails:varDetails}],"debug");
    				//TO DO: MAKE IT ABLE TO READ . NOTATION CHIDLREN
    				if(varDetails.dynaPageInitVariables_initialVal && typeof varDetails.dynaPageInitVariables_initialVal === "string"){
    					varDetails.dynaPageInitVariables_initialVal = varDetails.dynaPageInitVariables_initialVal.replace(/[\t\n\r]/gm,'');
    				}
    				let oldValue;
    				oldValue = readVariable(varDetails, '', undefined, vm);
    				
    				if(varDetails.dynaPageInitVariables_type =="scope"){
    					if(!oldValue || varDetails.replaceOldVal){
    					    if(!lodash.isEqual(vm[varDetails.dynaPageInitVariables_variableName], varDetails.dynaPageInitVariables_initialVal)){
    						    vm[varDetails.dynaPageInitVariables_variableName] = varDetails.dynaPageInitVariables_initialVal;
    					    }
    					}
    				}else if(varDetails.dynaPageInitVariables_type =="dynascope"){
    					if(!oldValue || varDetails.replaceOldVal){
    						varDetails.dynaPageInitVariables_initialVal = jsonize(varDetails.dynaPageInitVariables_initialVal);
    						if(!lodash.isEqual(vm[varDetails.dynaPageInitVariables_variableName], varDetails.dynaPageInitVariables_initialVal)){
    						    vm[varDetails.dynaPageInitVariables_variableName] = varDetails.dynaPageInitVariables_initialVal;
    						}
    					}
    				}else if(varDetails.dynaPageInitVariables_type =="var"){
    					if(!oldValue || varDetails.replaceOldVal){
    					    if(!lodash.isEqual(window[varDetails.dynaPageInitVariables_variableName], varDetails.dynaPageInitVariables_initialVal)){
    						    window[varDetails.dynaPageInitVariables_variableName] = varDetails.dynaPageInitVariables_initialVal;
    					    }
    					}
    				}else if(varDetails.dynaPageInitVariables_type =="dynavar"){
    					if(!oldValue || varDetails.replaceOldVal){
    						varDetails.dynaPageInitVariables_initialVal = jsonize(varDetails.dynaPageInitVariables_initialVal);
    						if(!lodash.isEqual(window[varDetails.dynaPageInitVariables_variableName], varDetails.dynaPageInitVariables_initialVal)){
    						    window[varDetails.dynaPageInitVariables_variableName] = varDetails.dynaPageInitVariables_initialVal;
    						}
    					}
    				}else if(varDetails.dynaPageInitVariables_type =="dynaLocalStorage"){
    					if(!oldValue || varDetails.replaceOldVal){
    						varDetails.dynaPageInitVariables_initialVal = jsonize(varDetails.dynaPageInitVariables_initialVal);
    						//localStorageService.set(varDetails.dynaPageInitVariables_variableName, varDetails.dynaPageInitVariables_initialVal);
    					}
    				}else if(varDetails.dynaPageInitVariables_type =="localStorage"){
    					if(!oldValue || varDetails.replaceOldVal){
    						//localStorageService.set(varDetails.dynaPageInitVariables_variableName, varDetails.dynaPageInitVariables_initialVal);
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
    							value = readVariable(undefined, variables[j]['variableId'], allPageVars, vm);
    							searchCondition = searchCondition.replace(variables[j]['name'], value);
    						}
    						searchCondition = jsonize(searchCondition);
    						
    						let foundData = [];
    						foundData = localDb.advancedSelectItem(localTableName, searchCondition, vm.db, parameters);

    						path = lodash.sortBy(path, ['order']);
    						for(var j=0; j<lodash.size(path); j++){
    							foundData = getOutput(foundData, path[j]);
    							if(path[j].index !== undefined && path[j].index !== ""){
    							    foundData = foundData[path[j].index];
    							}
    						}
                            if(!lodash.isEqual(window[varDetails.dynaPageInitVariables_variableName], foundData)){
    						    window[varDetails.dynaPageInitVariables_variableName] = foundData;
                            }
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
    							value = readVariable(undefined, variables[j]['variableId'], allPageVars, vm);
    							searchCondition = searchCondition.replace(variables[j]['name'], value);
    						}
    						searchCondition = jsonize(searchCondition);
    						let foundData = localDb.advancedSelectItem(localTableName, searchCondition, vm.db, parameters);
    						
    						path = lodash.sortBy(path, ['order']);
    						for(var j=0; j<lodash.size(path); j++){
    							foundData = getOutput(foundData, path[j]);
    							if(path[j].index !== undefined && path[j].index !== ""){
    							    foundData = foundData[path[j].index];
    							}
    						}
    						let foundDataFinal = angular.copy(foundData);
    						if(!lodash.isEqual(vm[varDetails.dynaPageInitVariables_variableName], foundDataFinal)){
    						    vm[varDetails.dynaPageInitVariables_variableName] = foundDataFinal;
    						}
    						
    					}
    				}
					logService.setlog("setting pageVar "+i+" finished", "debug");
    			}
    		}else{
				logService.setlog("no page var to set", "debug");
			}
    	}
    	catch(e){
    	    logService.setlog(e, "debug");
    	    logService.setlog("Error happened while setting variable:", "debug");
    	    logService.setlog(pageVars, "debug");
    	}
		logService.setlog("page vars set", "debug");
	}
	let getOutput = function(data, pathInfo){
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
	
	let readVariable = function(pageVar=undefined, pageVarId='', pageVars=undefined, vm){
	    if(pageVarId=='db'){
	        return vm.db;
	    }
	    try{
    		if(!pageVar && pageVarId && pageVars){
    			pageVar = pageVars.find(item => item.dynaPageInitVariables_variableId == pageVarId);
    		}
    
    		var scopeTypes = ["scope", "dynascope", "localTableScope"];
    		var varTypes = ["var", "dynavar", "localTableVar"];
    		var localStorageTypes = ["localStorage", "dynaLocalStorage"];
    		if(scopeTypes.indexOf(pageVar.dynaPageInitVariables_type) > -1){
    			if(vm[pageVar.dynaPageInitVariables_variableName]){
    				return vm[pageVar.dynaPageInitVariables_variableName];
    			}
    		}else if(localStorageTypes.indexOf(pageVar.dynaPageInitVariables_type) > -1){
    			//return localStorageService.get(pageVar.dynaPageInitVariables_variableName);
    		}else if(varTypes.indexOf(pageVar.dynaPageInitVariables_type) > -1){
    			return window[pageVar.dynaPageInitVariables_variableName];
    		}else{
    		    return undefined;
    		}
	    }
	    catch(e){
	        logService.setlog(e, "error");
	        logService.setlog("Error happened while reading variable: "+pageVarId, "error");
	        logService.setlog(pageVar, "error");
	        return "";
	    }
	}
	
	return ({
        initTableMapVar: initTableMapVar,
        setInitialVariables: setInitialVariables,
        readVariable: readVariable,
        jsonize: jsonize
    });
}