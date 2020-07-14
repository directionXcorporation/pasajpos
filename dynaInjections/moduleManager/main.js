function($mdDialog, $q, $stateParams, $translate, $state, sharedata, $timeout, $stateRegistry, logService, httpService, guid, lodash){
	return {
        openModuleSelection: openModuleSelection,
		openGridsterOptions: openGridsterOptions,
		addNewScreen: addNewScreen
    };
	
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
	function getAvailableModules(){
		let parameters = {
				dynaPageModules_code: "",
                dynaPageModules_code_nullcheck: "",
				dynaPageModules_moduleId: "",
				dynaPageModules_moduleId_nullcheck: "",
				dynaPageModulesStatus_status: 1,
				dynaPageModulesStatus_status_nullcheck: 1,
                start:0,
                limit:99999
            };
		return runServerCommand("modulesSearch", parameters).then(function(modulesDetailResponse){
			if(modulesDetailResponse && modulesDetailResponse.status && modulesDetailResponse.status==200){
				if(modulesDetailResponse.data && modulesDetailResponse.data.modulesDetail && modulesDetailResponse.data.modulesDetail.result){
					if(modulesDetailResponse.data.modulesDetail.result.status && modulesDetailResponse.data.modulesDetail.result.rows){
						return modulesDetailResponse.data.modulesDetail.result.rows;
					}
				}
			}
		});
	}
	
	function openModuleSelection(pageData, screenId){
		let originalPageData = angular.copy(pageData);
		let currentScreenIndex = pageData.screens.findIndex(screen=>screen.screenId == screenId);
		let openModuleSelectionDialogController = function($mdDialog){
			let vm = this;
			vm.formDisabled = 0;
			
			let searchCondition = {"name": "availableModules"};
			let availableModules = sharedata.searchList("mainform", searchCondition, 1);
			
			if(availableModules && availableModules[0] && availableModules[0].value){
				vm.availableModules = availableModules[0].value;
			}else{
				getAvailableModules().then(function(modules){
					let data = {"name": "availableModules", "value": modules};
					sharedata.addItem(data, "mainform");
					vm.availableModules = modules;
				});
			}
			logService.setlog(["available modules",{availableModules: vm.availableModules, pageData:pageData, screenId:screenId}], "debug");
			vm.makeModule = function(module){
				let openModuleParametersDialogController = function(module, $mdDialog){
					let vm = this;
					vm.formDisabled = 0;
					vm.inputParameters = lodash.attempt(JSON.parse.bind(null, module.dynaPageModules_inputParameters));
					
					vm.module = module;
					vm.parametersValue = {};
					vm.newmodule = {};
					logService.setlog(["creating new module", {module: vm.module}], "debug");
					vm.clickToClose = function clickToClose(){
						$mdDialog.cancel({newItem: "", status: 1});
					}
					vm.clickToSave = function clickToSave(){
						//TODO: create module with parameters
						let moduleText = "<"+module.dynaPageModules_moduleId;
						for(let i=0; i<vm.inputParameters.length; i++){
							let parameter = vm.inputParameters[i];
							moduleText += " "+parameter.parameterName+"=";
							if(parameter.type=="int"){
								
							}else{
								moduleText += "'";
							}
							if(vm.parametersValue[parameter.parameterName]){
								if(parameter.type=="int"){
									vm.parametersValue[parameter.parameterName] = parseInt(vm.parametersValue[parameter.parameterName]);
								}
								moduleText += vm.parametersValue[parameter.parameterName];
							}else if(parameter.default){
								if(parameter.type=="int"){
									parameter.default = parseInt(parameter.default);
								}
								moduleText += parameter.default;
							}
							if(parameter.type=="int"){
								
							}else{
								moduleText += "'";
							}
						}
						moduleText += ">";
						let newItem = {
							element: moduleText
							,cols: vm.newmodule.cols
							,rows: vm.newmodule.rows
							,x: vm.newmodule.x
							,y: vm.newmodule.y
						};
						$mdDialog.hide({newItem: newItem, status: 1});
					}
				}
				return $mdDialog.show({
					preserveScope: false,
					parent: angular.element(document.querySelector('#pageContent')),
					controller: openModuleParametersDialogController,
					bindToController: true,
					controllerAs: '$ctrl',
					templateUrl: "moduleManager_selectModule_moduleParameters.tpl",
					clickOutsideToClose: false,
					locals: {
						module: module
					},
					multiple: true,
					fullscreen : false,
					openFrom: "#"+module.dynaPageModules_moduleId,
					closeTo: "#"+module.dynaPageModules_moduleId
				})
				.then(function(response) {
					if(currentScreenIndex > -1){
						pageData.screens[currentScreenIndex].items.push(response.newItem);
					}
					return;
				}, function(response) {
					return;
				});
			}
			vm.clickToClose = function clickToClose(){
				pageData.screens[currentScreenIndex].items = originalPageData.screens[currentScreenIndex].items;
				$mdDialog.cancel({data: [], status: 1});
			}
			vm.clickToSave = function clickToSave(){
				$mdDialog.hide({data: [], status: 1});
			}
		}
	
		return $mdDialog.show({
            preserveScope: false,
            parent: angular.element(document.querySelector('#pageContent')),
            controller: openModuleSelectionDialogController,
            bindToController: true,
            controllerAs: '$ctrl',
            templateUrl: "moduleManager_selectModule.tpl",
            clickOutsideToClose: false,
            locals: {
				
            },
            multiple: true,
            fullscreen : true,
			openFrom: "#openModuleSelection",
			closeTo: "#openModuleSelection"
        })
        .then(function(response) {
           	return;
        }, function(response) {
			logService.setlog("Edit Canceled");
			return;
        });
	}
	
	function openGridsterOptions(options){
		logService.setlog(["gridster options",{options:options}], "debug");
		let originalData = angular.copy(options);
		
		let pageSettingsController = function(options){
			let vm = this;
			vm.formDisabled = 0;
			vm.options = options;
			
			vm.clickToClose = function clickToClose(){
				$mdDialog.cancel({options: [], status: 1});
			}
			vm.clickToSave = function clickToSave(){
				$mdDialog.hide({options: vm.options, status: 1});
			}
		}
		$mdDialog.show({
			preserveScope: false,
			controller: pageSettingsController,
			controllerAs: '$ctrl',
			bindToController: true,
			templateUrl: 'moduleManager_pageSettings.tpl',
			parent: angular.element(document.body),
			clickOutsideToClose: false,
			locals: {
				options: options
			},
			multiple: false,
			openFrom: "#openGridsterOptions",
			closeTo: "#openGridsterOptions"
		})
		.then(function(data) {
			options = data.options;
			if(data && data.options && data.options.api && data.options.api.optionsChanged){
				data.options.api.optionsChanged();
			}
    
		}, function() {
			options = angular.copy(originalData);
		});
	};
	
	function addNewScreen(pageData){
		let addNewScreenDialogController = function(){
			let vm = this;
			vm.formDisabled = 0;
			vm.msg = "";
			
			vm.clickToClose = function clickToClose(){
				$mdDialog.cancel({screen: {}, status: 1});
			}
			vm.clickToSave = function clickToSave(){
				let data = {
					screenName: vm.screenName,
					screenId: guid.newuuid('pageScreen'),
					items: []
				}
				if(vm.screenName == 'main'){
					vm.msg = "Screen name cannot be 'main'";
				}else{
					$mdDialog.hide({screen: data, status: 1});
				}
			}
		}
		$mdDialog.show({
			preserveScope: false,
			controller: addNewScreenDialogController,
			controllerAs: '$ctrl',
			bindToController: true,
			templateUrl: 'moduleManager_addNewScreen.tpl',
			parent: angular.element(document.body),
			clickOutsideToClose: false,
			fullscreen: false,
			locals: {
				
			},
			multiple: false,
			openFrom: "#addNewScreen",
			closeTo: "#addNewScreen"
		})
		.then(function(data) {
			logService.setlog(["adding new screen",{data: data, pageData:pageData}], "debug");
			pageData.screens.push(data.screen);
		}, function() {
			
		});
	}
}