{{_CONTROLLER_AS}}.{{_FUNCTION_ID}} = function({{_PARAMETERS_IN}}){

	if(checkPermissionGlobalParameter){
	
		return functionAccess.runFunctionWithAccessCheck(function () { {{_CONTROLLER_AS}}.{{_FUNCTION_ID}}({{_PARAMETERS_IN_NOVALUE}}) }, "{{_FUNCTION_ID}}", {{_PARAMETERS_IN_NOVALUE}});
		
	}else{
	
		{{_FUNCTION_TEXT}}
		
	}
	
}