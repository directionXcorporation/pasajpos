try{
    if(loadedDirectives.indexOf('{{_ID}}') < 0){
        loadedDirectives.push('{{_ID}}');
        app.component('{{_ID}}', {
            templateUrl: "moduleTemplate_{{_ID}}.tpl",
            controller: componentController_{{_ID}},
            controllerAs: "$ctrl",
            bindings: { {{_PARAMETERS}} }
        });
        function componentController_{{_ID}}(logService, $scope, $log, $timeout, $stateParams, $stateRegistry, $mdSidenav, $mdBottomSheet, $element, functionAccess, loginService, onlineStatus, $mdToast, tableMapsService, moment, initVarsService, httpService, $state, globals, $translate {{_INJECTIONS}}) {
            let vm = this;
            vm.checkFunctionAccess = functionAccess.checkAccess;
            try{
                vm.$onInit = function() {
                    {{_FUNCTIONS}}
                    
                   	{{_JAVASCRIPT}}
                   	
                   	vm.scopeLoaded = true;
               	}
        	}catch(e){
        		logService.setlog(e, "error", 0);
        	}
        }
    }
}catch(e){
    console.error(["Error in setting up module: {{_ID}}", e]);
}