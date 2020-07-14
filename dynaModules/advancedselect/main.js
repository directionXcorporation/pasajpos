vm.clearSearchTerm = function() {
    vm.searchTerm = '';
};
if(typeof vm.namefieldname === "string"){
    vm.namefieldnameJSON = lodash.attempt(JSON.parse.bind(null, vm.namefieldname));
}else{
    vm.namefieldnameJSON = vm.namefieldname;
}

vm.loadPage = function(){
    var modalPageParameters = {
    	newState: {
    		parameters: {
    			"modalPageId": vm.pageid
    		},
    		"showActionButtons": true
    	},
    	selectParameters: {
    		pageId: vm.pageid,
    		limit: 9999999999
    	}
    }
    modalPageParameters.responseArray = [{type: "selected", origin: "searchResult"}];
    return modalService.openPageInModal(modalPageParameters).then(function(response){
    	let selectedValues = vm.preselected || [];
    	if(response && response.data && response.data[0] && response.data[0][0]){
    	    for(let i=0;i<response.data[0].length;i++){
    		    selectedValues.push(response.data[0][i].data);
		    }
    	}
    	vm.data = selectedValues;
    });
}

if(!vm.pageid && !vm.data){
    if(!vm.options.noinit){
        //Should get values from options
        vm.getObjectsDetail({searchParameters: vm.options.searchParameters || {}, resultVarName: undefined, searchOptions: vm.options}).then(function(data){
            data = lodash.uniqBy(data, vm.idfieldname);
            vm.data = data;
        });
    }
}