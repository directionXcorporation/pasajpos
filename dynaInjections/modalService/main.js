function($mdDialog, $q, $stateParams, $translate, $state, sharedata, $timeout, $stateRegistry, logService){
    let pageStateViewUrl = "dynaPage.html";
    var dynaPageModalState0 = {
		name: 'dynaPage.modal0',
		url: '/modal0/:modalPageId0/:objectId0',
		loginRequired: 1,
		params: {
            modalPageId0: {squash: false, dynamic: false},
            objectId0: {squash: true, value: null, dynamic: false},
            data0: {squash: true, value: null, dynamic: true},
            selectedValues0: {squash: true, value: [], dynamic: true},
            inModal0: {squash: true, value: 1, dynamic: true}
        },
		views : {
			'modal0@' : {
				templateUrl : function (params) {
					if(params.modalPageId0){
						var fullUrl = pageStateViewUrl;
						return fullUrl;
					}
				       	//return a valid Url to the template, and angular will load it for you
				},
				controller: 'dynaPage',
				controllerAs: '$ctrl'
			}
		}
    }
    var isStateRegistered = $stateRegistry.get("dynaPage.modal0");
    if(!isStateRegistered){
        $stateRegistry.register(dynaPageModalState0);
    }
    var dynaPageModalState1 = {
		name: 'dynaPage.modal0.modal1',
		url: '/modal1/:modalPageId1/:objectId1',
		loginRequired: 1,
		params: {
            modalPageId1: {squash: false, dynamic: false},
            objectId1: {squash: true, value: null, dynamic: false},
            data1: {squash: true, value: null, dynamic: true},
            selectedValues1: {squash: true, value: [], dynamic: true},
            inModal1: {squash: true, value: 1, dynamic: true}
        },
		views : {
			'modal1@' : {
				templateUrl : function (params) {
					if(params.modalPageId1){
						var fullUrl = pageStateViewUrl;
						return fullUrl;
					}
				       	//return a valid Url to the template, and angular will load it for you
				},
				controller: 'dynaPage',
				controllerAs: '$ctrl'
			}
		}
    }
    isStateRegistered = $stateRegistry.get("dynaPage.modal0.modal1");
    if(!isStateRegistered){
        $stateRegistry.register(dynaPageModalState1);
    }
    
    var dynaPageModalState2 = {
		name: 'dynaPage.modal0.modal1.modal2',
		url: '/modal2/:modalPageId2/:objectId2',
		loginRequired: 1,
		params: {
            modalPageId2: {squash: false, dynamic: false},
            objectId2: {squash: true, value: null, dynamic: false},
            data2: {squash: true, value: null, dynamic: true},
            selectedValues2: {squash: true, value: [], dynamic: true},
            inModal2: {squash: true, value: 1, dynamic: true}
        },
		views : {
			'modal2@' : {
				templateUrl : function (params) {
					if(params.modalPageId2){
						var fullUrl = pageStateViewUrl;
						return fullUrl;
					}
				       	//return a valid Url to the template, and angular will load it for you
				},
				controller: 'dynaPage',
				controllerAs: '$ctrl'
			}
		}
    }
    isStateRegistered = $stateRegistry.get("dynaPage.modal0.modal1.modal2");
    if(!isStateRegistered){
        $stateRegistry.register(dynaPageModalState2);
    }
    
    var dynaPageModalState3 = {
		name: 'dynaPage.modal0.modal1.modal2.modal3',
		url: '/modal3/:modalPageId3/:objectId3',
		loginRequired: 1,
		params: {
            modalPageId3: {squash: false, dynamic: false},
            objectId3: {squash: true, value: null, dynamic: false},
            data3: {squash: true, value: null, dynamic: true},
            selectedValues3: {squash: true, value: [], dynamic: true},
            inModal3: {squash: true, value: 1, dynamic: true}
        },
		views : {
			'modal3@' : {
				templateUrl : function (params) {
					if(params.modalPageId3){
						var fullUrl = pageStateViewUrl;
						return fullUrl;
					}
				       	//return a valid Url to the template, and angular will load it for you
				},
				controller: 'dynaPage',
				controllerAs: '$ctrl'
			}
		}
    }
    isStateRegistered = $stateRegistry.get("dynaPage.modal0.modal1.modal2.modal3");
    if(!isStateRegistered){
        $stateRegistry.register(dynaPageModalState3);
    }
    
    var dynaPageModalState4 = {
		name: 'dynaPage.modal0.modal1.modal2.modal3.modal4',
		url: '/modal4/:modalPageId4/:objectId4',
		loginRequired: 1,
		params: {
            modalPageId4: {squash: false, dynamic: false},
            objectId4: {squash: true, value: null, dynamic: false},
            data4: {squash: true, value: null, dynamic: true},
            selectedValues4: {squash: true, value: [], dynamic: true},
            inModal4: {squash: true, value: 1, dynamic: true}
        },
		views : {
			'modal4@' : {
				templateUrl : function (params) {
					if(params.modalPageId4){
						var fullUrl = pageStateViewUrl;
						return fullUrl;
					}
				       	//return a valid Url to the template, and angular will load it for you
				},
				controller: 'dynaPage',
				controllerAs: '$ctrl'
			}
		}
    }
    isStateRegistered = $stateRegistry.get("dynaPage.modal0.modal1.modal2.modal3.modal4");
    if(!isStateRegistered){
        $stateRegistry.register(dynaPageModalState4);
    }
    return {
        openPageInModal: openPageInModal
    };
    function gotoPrevState(state){
		$state.go(state.name, state.params, {reload: false, inherit: true, notify: false, location: "string"});
	}
	function getElementCount(){
        let modalElements = document.getElementsByTagName("md-dialog");
        let modalCount = 0;
        if(modalElements){
          modalCount = modalElements.length;
        }
        return modalCount;
    }
    
    function openPageInModalDialogController($mdDialog, $scope, $state, logService, sharedata, originalState){
        let vm = this;
        vm.title = vm.modalPageParameters.newState.parameters.modalPageId;
        
        //Show save button
    	vm.showActionButtons = vm.modalPageParameters.newState.showActionButtons;
    	
    	//Setting state name like dynaPage.modal0 for first modal, dynaPage.modal0.modal1 for second modal, ...
    	let stateTail = "";
        for(let i=0;i<=vm.modalCount;i++){
          stateTail += ".modal"+i;
        }
        let stateName = "dynaPage"+stateTail;
        
        //setting parameter names based on state number. {modalPageId0, objectId0, data0} for first modal, ...
    	let stateParam = {};
    	for(let key in vm.modalPageParameters.newState.parameters){
    	    if(vm.modalPageParameters.newState.parameters.hasOwnProperty(key)){
    	        stateParam[key+vm.modalCount] = vm.modalPageParameters.newState.parameters[key];
    	    }
    	}
        logService.setlog(["opening modal with following parameters", {stateParam: stateParam, stateName: stateName}], "debug");
        $state.go(stateName, stateParam, {reload: false, inherit: true, notify: true, location: "string", relative: $stateRegistry.get("dynaPage")});

    	vm.clickToClose = function clickToClose(){
    	    $mdDialog.cancel({data: [], status: 1});
    	    gotoPrevState(vm.originalState);
    	}
    	vm.clickToSave = function clickToSave(){
    	    logService.setlog(["Saving details", {modalPageParameters: vm.modalPageParameters.selectParameters}], 
    	    "debug");
        	if(vm.modalPageParameters.selectParameters){
        		try{
        		    let selectedValueArray = [];
        		    for(let i=0 ;i< vm.modalPageParameters.responseArray.length; i++){
        			    selectedValueArray[i] = sharedata.searchList(vm.modalPageParameters.selectParameters.pageId, vm.modalPageParameters.responseArray[i], vm.modalPageParameters.selectParameters.limit);
        		    }
        		    logService.setlog(["Saved details", {selectedValueArray: selectedValueArray}], 
    	    "debug");
        			$mdDialog.hide({data: selectedValueArray, status: 1});
        		}
        		catch(e){
        		    logService.setlog(["Could not fetch selected values", e], "error", 0);
        			$mdDialog.cancel({data: [], status: 0});
        		}
        	}else{
        		$mdDialog.cancel({data: [], status: 0});
        	}
            gotoPrevState(vm.originalState);
    	}
    }
    function afterShowAnimation(scope, element, options){
        
    }
    function openPageInModal(options){
        let originalState = {
            name: angular.copy($state.current.name), 
            params: angular.copy($state.params)
        }
        
        //Count number of open modals
        let modalCount = getElementCount();
        logService.setlog(["opening page in modal",{originalState: originalState, options: options, modalCount: modalCount}], "debug");
        if(modalCount==5){
            return $q(function(resolve, reject){
                alert("Maximum number of open modals(5) reached.");
                logService.setlog("Maximum number of open modals(5) reached.", "error");
                reject([]);
            });
        }else{
            logService.setlog("opening modal in template "+ "openPageInModal"+modalCount+".tpl", "debug");
            return $mdDialog.show({
            	preserveScope: false,
            	parent: angular.element(document.querySelector('#pageContent')),
            	targetEvent: angular.element(document.querySelector('#pageContent')),
            	controller: openPageInModalDialogController,
            	bindToController: true,
            	controllerAs: '$ctrl',
            	templateUrl: "openPageInModal"+modalCount+".tpl",
            	clickOutsideToClose: false,
            	onComplete: afterShowAnimation,
            	locals: {
            		modalPageParameters: options,
            		originalState: originalState,
            		modalCount: modalCount
            	},
            	multiple: true,
            	fullscreen : true
            })
            .then(function(response) {
            	return (response);
            }, function(response) {
            	return (response);
            });
        }
    }
}