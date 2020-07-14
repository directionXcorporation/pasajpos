var hintsOn = {};
vm.searchIntro = function(description){
    let data = {
        action: 'searchIntro',
        description: '%'+description+'%',
        debug: globals.debug
    }
    let options = {
        method : "POST",
	    url : "/smartTicket.php",
		data: data,
		headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
    }
    if(onlineStatus.isOnline()){
        return httpService.request(options).then(function(response){
            logService.setlog(response, "debug");
            if(response && response.data && response.data.rows){
                return response.data.rows;
            }else{
                return [];
            }
        }, function(error){
            logService.setlog(error, "error");
            return [];
        });
    }else{
        return $q(function(resolve, reject){
            resolve([]);
        });
    }
}
let searchStepsByIntro = function(introIds){
    if(!introIds){
        return [];
    }
    let data = {
        action: 'searchStepsByIntro',
        introIds: introIds,
        debug: globals.debug
    };
    let options = {
        method : "POST",
	    url : "/smartTicket.php",
		data: data,
		headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
    };
    if(onlineStatus.isOnline()){
        return httpService.request(options).then(function(response){
            logService.setlog(response, "debug");
            if(response && response.data && response.data.rows){
                return response.data.rows;
            }else{
                return [];
            }
        }, function(error){
            logService.setlog(error, "error");
            return [];
        });
    }else{
        return $q(function(resolve, reject){
            resolve([]);
        });
    }
}

vm.startIntro = function(introId, currentStepNumber=0, pausedIntros=[]){
    logService.setlog(["Starting new intro", {introId:introId, currentStepNumber:currentStepNumber, pausedIntros:pausedIntros}], "debug");
    if(!introId){
        return;
    }
    let introIds = [introId];
    let activeIntroId = introId;
    
    searchStepsByIntro(introIds).then(function(introResponse){
        let createIntroSteps = function(intros){
            let steps = {};
            let conditions = {};
            let javascripts = {};
            for(let i =0; i<intros.length;i++){
                let recordIntroId = intros[i].dynaIntroId_stepId_introId;
                let recordStepId = intros[i].dynaIntroId_stepId_stepId;
                
                if(!conditions[recordIntroId]){
                    conditions[recordIntroId] = {};
                }
                if(!javascripts[recordIntroId]){
                    javascripts[recordIntroId] = {};
                }
                
                if(!steps[recordIntroId]){
                    steps[recordIntroId] = [];
                }
                
                let step = {
                    introId: recordIntroId,
                    stepId: recordStepId,
                    intro: $translate.instant(intros[i].dynaIntroSteps_stepDescription),
                    rawElement: intros[i].dynaIntroStep_pageId_element,
                    element: eval(intros[i].dynaIntroStep_pageId_element),
                    position: intros[i].dynaIntroStep_pageId_position,
                    subIntro: intros[i].dynaIntroStep_pageId_subIntro
                };
                if(intros[i].dynaIntroStep_pageId_stepCondition){
                    conditions[recordIntroId][recordStepId] = initVarsService.jsonize(intros[i].dynaIntroStep_pageId_stepCondition);
                }
                if(intros[i].dynaIntroStep_pageId_javascript){
                    javascripts[recordIntroId][recordStepId] = intros[i].dynaIntroStep_pageId_javascript;
                }
            
                steps[recordIntroId].push(step);
            }
            return {steps: steps, conditions: conditions, javascripts: javascripts};
        }
        if(introResponse && introResponse.length){
            
            let introResponseArray = createIntroSteps(introResponse);
            let steps = introResponseArray['steps'];
            let conditions = introResponseArray['conditions'];
            let javascripts = introResponseArray['javascripts'];
            
    		
    		let runIntro = function(activeIntroId, steps,conditions,javascripts,currentStepNumber, pausedIntros){
    		    logService.setlog(["running intro", {steps:steps, conditions:conditions, javascripts:javascripts}], "debug");
    		let introOptions = {};
    		let activeSteps = [];
    		let addStep = function(stepNumber=0, steps=[], current={}, tried=0){
    		    currentStepNumber = stepNumber;
    		    let nextStepNumber_temp = currentStepNumber;
    		    logService.setlog(["creating current step: all info",{stepNumber: stepNumber, current:current, steps:steps, tried:tried, conditions: conditions, javascripts:javascripts}], "debug");
    		    if(tried > steps.length){
    		        return current;
    		    }
    		    stepNumber = parseInt(stepNumber);
        		if(stepNumber>=0 && steps[stepNumber]){
        		    steps[stepNumber].element = eval(steps[stepNumber].rawElement);
        		    let step = steps[stepNumber];
        		    try{
        		        let targetElement = eval(step.rawElement);
        			    logService.setlog(["creating current step::1",{targetElement:targetElement}], "debug");
                		if(conditions[activeIntroId] && conditions[activeIntroId][step.stepId]){
                		    logService.setlog(["creating current step::conditions",{condition:conditions[activeIntroId][step.stepId]}], "debug");
                		    if(conditions[activeIntroId][step.stepId].next){
                		        logService.setlog(["creating current step::next",{next:conditions[activeIntroId][step.stepId].next}], "debug");
                    		    let result = eval(conditions[activeIntroId][step.stepId].next);
                    		    if(result){
                    		        logService.setlog(["creating current step: next step skipped",conditions[activeIntroId][step.stepId]],"debug");
                    		        nextStepNumber_temp = stepNumber + 1;
                    		        current = addStep(nextStepNumber_temp, steps, current, tried+1);
                    		    }else{
                    		        current = step;
                    		    }
                		    }
                		    if(conditions[activeIntroId][step.stepId].prev && (nextStepNumber_temp == stepNumber)){
                		        logService.setlog(["creating current step::prev",{prev:conditions[activeIntroId][step.stepId].prev}], "debug");
                		        let result = eval(conditions[activeIntroId][step.stepId].prev);
                		        if(result){
                    		        logService.setlog(["creating current step: remained in previous step",conditions[activeIntroId][step.stepId]],"debug");
                    		        nextStepNumber_temp = stepNumber - 1;
                    		        current = addStep(nextStepNumber_temp, steps, current, tried+1);
                    		    }else{
                    		        current = step;
                    		    }
                		    }
                		    if(!conditions[activeIntroId][step.stepId].prev && !conditions[activeIntroId][step.stepId].next && (nextStepNumber_temp == stepNumber)){
                		        logService.setlog(["creating current step::other",{condition:conditions[activeIntroId][step.stepId]}], "debug");
                		        current = step;
                		    }
                		}else{
                		    current = step;
                		}
        			}
        			catch(e){
        			    logService.setlog(["creating current step: Error",e],"debug");
        			}
        			finally{
    			    
    			    }
        		
            		if(javascripts[activeIntroId] && javascripts[activeIntroId][step.stepId]){
            		    try{
            		        logService.setlog(["Running javascript",{current:current, javascripts:javascripts, stepNumber:stepNumber}], "debug");
            		        eval(javascripts[activeIntroId][step.stepId]);
            		    }
            		    catch(e){
            		        logService.setlog(["Error in running javascript",{e:e, current:current, javascripts:javascripts, stepNumber:stepNumber}], "debug");
            		    }
            		    finally{
            		        
            		    }
            		}
        		}else{
        		    logService.setlog(['You reached the end of intro'], "debug");
        		    current = {
        		        intro: 'You reached the end of intro'
        		    };
        		}
        		if(current.subIntro){
        		    current.showSkip = true;
        		}else{
        		    current.showSkip = false;
        		}
        		logService.setlog(["Creating current step",{current: current}],"debug");
        		return current;
    		}
    		
    		let newStep = addStep(currentStepNumber, steps[activeIntroId]);
    		activeSteps.push(newStep);
    		introOptions = {
    			steps: activeSteps/*.filter(function(obj) { return (obj.element && obj.element.id); })*/,
    			showStepNumbers: false,
    			showBullets: false,
    			exitOnOverlayClick: false,
    			exitOnEsc:true,
    			keyboardNavigation: false,
    			disableInteraction: false,
    			nextLabel: "",
    			prevLabel: "",
    			skipLabel: $translate.instant("Skip"),
    			exitLabel: $translate.instant("Exit"),
    			doneLabel: $translate.instant("Done")
    		};
    		logService.setlog(["initializing intro", introOptions], "debug");
    		ngIntroService.clear();
    		ngIntroService.setOptions(introOptions);
    		let unfinishedIntros = [];
    		
    		let gotoNextStep = function(nextStepNumber=1, steps=[]){
    		    logService.setlog(["gotoNextStep:Start",{nextStepNumber:nextStepNumber, currentStepNumber:currentStepNumber, introOptions:introOptions}], "debug");
    		    try{
        			logService.setlog(["[intro service] going to next step", {currentStepNumber:currentStepNumber, nextStepNumber:nextStepNumber, introId:introId, activeIntroId:activeIntroId, steps: steps, activeSteps: introOptions.steps}], "debug");
        			
            		    if(steps[nextStepNumber]){
                            let nextElement = eval(steps[nextStepNumber].rawElement);
                            if(nextElement){
                                logService.setlog("Creating next step "+(nextStepNumber), "debug");
                                let newStep = addStep(nextStepNumber, steps, introOptions.steps[0]);
                                introOptions.steps.length = 0;
                                introOptions.steps.push(newStep);
                                logService.setlog(["Next Step Created ", {activeSteps: introOptions.steps}], "debug");
                                ngIntroService.refresh();
                                $timeout(function(){ngIntroService.start();}, 0);
                            }else{
                                logService.setlog(["Remaining is same step "+(currentStepNumber), {nextElement:nextElement, step:steps[nextStepNumber]}],"debug");
                                $timeout(function(){ngIntroService.start();}, 0);
                            }
                        }else{
                            ngIntroService.exit();
                            ngIntroService.clear();
                        }
        			
    		    }
    		    catch(e){
    		        logService.setlog(["gotoNextStep:Error",{e:e, nextStepNumber:nextStepNumber, currentStepNumber:currentStepNumber, introOptions:introOptions}], "debug");
    		    }
    		}
    		ngIntroService.onExit(function(){
    			logService.setlog("[intro service] exit");
    		});
    		
    		let createNextStep = function(){
    		    let nextStepNumber = currentStepNumber + 1;
        		if(steps[activeIntroId] && steps[activeIntroId][nextStepNumber]){
        		    gotoNextStep(nextStepNumber, steps[activeIntroId]);
        		}else{
        		    if(pausedIntros.length){
        			    let pausedIntroDetails = pausedIntros.pop();
        			    logService.setlog(["Getting paused intro",{pausedIntroDetails:pausedIntroDetails}], "debug");
        			    let pausedIntroStepNumber = pausedIntroDetails.currentStepNumber;
        			    let pausedIntroSteps = pausedIntroDetails.steps;
        			    let pausedIntroConditions = pausedIntroDetails.conditions;
        			    let pausedIntroJavascripts = pausedIntroDetails.javascripts;
        			    let pausedIntroId = pausedIntroDetails.introId;
        			    runIntro(pausedIntroId, pausedIntroSteps, pausedIntroConditions, pausedIntroJavascripts, pausedIntroStepNumber+1, pausedIntros);
        			}else{
            		    ngIntroService.exit();
                        ngIntroService.clear();
        			}
        		}
    		}
    		ngIntroService.onSkip(function(){
    			logService.setlog("[intro service] skip", "debug");
    			try{
    			    createNextStep();
    			}
    			catch(e){
    			    logService.setlog(["[intro service] skip error",e], "debug");
    			}
    		});
    		
    		ngIntroService.onComplete(function(){
    		    
    			logService.setlog(["[intro service] complete", {steps:steps, activeIntroId:activeIntroId, currentStepNumber:currentStepNumber}], "debug");
    			try{
    			    if(steps[activeIntroId][currentStepNumber] && steps[activeIntroId][currentStepNumber].subIntro){
    			        let subIntros = initVarsService.jsonize(steps[activeIntroId][currentStepNumber].subIntro);
    			        logService.setlog(["subintro found", {subIntros: subIntros}], "debug");
    			        for(let t=0; t<subIntros.length; t++){
    			            let subIntro = subIntros[t];
    			            let subIntroCondition = subIntro.condition;
    			            if(subIntroCondition == "" || eval(subIntroCondition)){
            			        let pausedIntro = {
            			            introId: introId,
            			            currentStepNumber: currentStepNumber,
            			            steps: steps,
            			            conditions: conditions,
            			            javascripts: javascripts
            			        }

                			    pausedIntros.push(pausedIntro);
                			    let CurrentStepNumber_temp = angular.copy(currentStepNumber);
                			    logService.setlog(["[service] onComplete: pausedIntro",{pausedIntro:pausedIntro, pausedIntros:pausedIntros,currentStepNumber:currentStepNumber, activeIntroId:activeIntroId, CurrentStepNumber_temp:CurrentStepNumber_temp, steps: steps}], "debug");
                			    currentStepNumber = parseInt(subIntro.stepNumber);
                			    activeIntroId = subIntro.introId;
                			    vm.startIntro(activeIntroId, currentStepNumber, pausedIntros);
                			    break;
    			            }
    			        }
        			    //createNextStep();
        			}else{
        			    createNextStep();
        			}
    			}
    			catch(e){
    			    logService.setlog(["[intro service] complete error",e], "debug");
    			}
    		});
    		  
    		ngIntroService.onBeforeChange(function(targetElement){
    			
    		});
    		  
    		ngIntroService.onChange(()=>{
    			logService.setlog("[intro service] on change");
    		});
    		  
    		ngIntroService.onAfterChange((targetElement)=>{
    			logService.setlog("[intro service] after Change");
    		});
    		ngIntroService.refresh();
    		$timeout(function(){ngIntroService.start();}, 0);
        }
        
        runIntro(activeIntroId, steps,conditions,javascripts,currentStepNumber, pausedIntros);
        }
    }, function(error){
        logService.setlog(error, "error");
    });

}
let searchHintsByPage = function(pageCode){
    let data = {
        action: 'searchHintsByPage',
        pageCode: pageCode,
        debug: globals.debug
    }
    let options = {
        method : "POST",
	    url : "/smartTicket.php",
		data: data,
		headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
    }
    if(onlineStatus.isOnline()){
        return httpService.request(options).then(function(response){
            logService.setlog(response, "debug");
            if(response && response.data && response.data.rows){
                return response.data.rows;
            }else{
                return [];
            }
        }, function(error){
            logService.setlog(error, "error");
            return [];
        });
    }else{
        return $q(function(resolve, reject){
            resolve([]);
        });
    }
}
vm.toggleHints = function(pageCode, selectedScreen=""){
    logService.setlog(["togling hints", {pageCode:pageCode, selectedScreen:selectedScreen, hintsOn:hintsOn}], "debug");
    if(!hintsOn[pageCode] || !hintsOn[pageCode][selectedScreen]){
        searchHintsByPage(pageCode).then(function(response){
            logService.setlog(["formatting hints", {response:response}], "debug");
            let hints = [];
            for(let i =0; i<response.length;i++){
                let hint = {
                    hint: $translate.instant(response[i].dynaIntroSteps_stepDescription),
                    element: eval(response[i].dynaIntroStep_pageId_element),
                    hintPosition: response[i].dynaIntroStep_pageId_position
                }
                hints.push(hint);
            }
            logService.setlog(["showing hints", {hints:hints}], "debug");
            if(hints && hints.length){
                let hintOptions = {};
        		
        		hintOptions = {
        			hints:hints,
        			hintButtonLabel: $translate.instant("OK")
        		};
        		$log.debug("initializing hints", hintOptions);
        		ngIntroService.clear();
        		ngIntroService.setOptions(hintOptions);
                ngIntroService.addHints();

        		if(!hintsOn[pageCode]){
        		    hintsOn[pageCode] = {};
        		}
        		hintsOn[pageCode][selectedScreen] = true;
            }
        }, function(error){
            logService.setlog(error, "error");
        });
    }else{
        ngIntroService.removeHints();
        ngIntroService.clear();
        hintsOn[pageCode][selectedScreen] = false;
    }
}