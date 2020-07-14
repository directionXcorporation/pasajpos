if(loadedDirectives.indexOf('{{_ID}}') < 0) {
    loadedDirectives.push('{{_ID}}');
    app.compileProvider.directive('{{_ID}}', function($compile, $stateParams, $state, $log {{_INJECTIONS}}) {
        return {
            restrict: 'E',
            replace: true,
            transclude: false,
            required: '',
            priority: 999,
            scope: { {{_PARAMETERS}} },
            templateUrl: function(tElement, tAttrs) {
            	if(tAttrs.templatename){
        			return tAttrs.templatename;
        		}else{
        			return "moduleTemplate_{{_ID}}.tpl";
        		}
        	},
            link: function(scope.new(true), elem, attrs) {
                $scope = scope;
            	{{_FUNCTIONS}}
            	try{
        	       	let html = '<style>{{_CSS}}</style>';
        	       	{{_JAVASCRIPT}}
        	       	elem.prepend($compile(html)($scope));
        	       	$scope.scopeLoaded = true;
            	}catch(e){
            		$log.error(e.message);
            	}
        	}
        };
    });
}