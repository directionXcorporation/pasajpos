if(loadedDirectives.indexOf('{{_ID}}') < 0){
    loadedDirectives.push('{{_ID}}');
    app.compileProvider.component('{{_ID}}', {
        templateUrl: "moduleTemplate_{{_ID}}.tpl",
        controller: componentController_{{_ID}},
        controllerAs: "$ctrl",
        bindings: { {{_PARAMETERS}} }
    });
    function componentController_{{_ID}}($log, $timeout, $stateParams, $state {{_INJECTIONS}}) {
        let vm = this;
        try{
            vm.$onInit = function() {
            
                {{_FUNCTIONS}}
                
               	{{_JAVASCRIPT}}
               	
               	vm.scopeLoaded = true;
           	}
    	}catch(e){
    		$log.error(e.message);
    	}
    }
}