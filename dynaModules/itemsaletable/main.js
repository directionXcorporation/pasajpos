vm.receiptIsLoading = 1;
let onRegisterApi = function(gridApi) {
    
}

if(!vm.gridoptions){
    let initOptions = {
        showColumnFooter: true,
        enableSorting: false,
        enableColumnMenus: false,
        columnDefs: vm.columndefs
    };
	vm.gridoptions = vm.initGrid(onRegisterApi, initOptions);
	vm.gridoptions.showGridFooter = false;
}

if($stateParams && $stateParams.objectId && vm.extraparams){
    let receiptId = $stateParams.objectId;
    let searchparameters = {
        salesHeader_offlineSalesId: receiptId
    }

    let searchoptions = {
        type: ["offline", "online"], 
        serverDetails: vm.extraparams.salesServerDetails, 
        db: vm.db, 
        localTableName:"salesHeader", 
        commandCode:"salesSearch", 
        action:"runServerCommand"
    }
    
    vm.getObjectsDetail({searchParameters: searchparameters, resultVarName: undefined, searchOptions: searchoptions}).then(function(response){
         vm.data = response;
         let initItemsTableData = initItemsTable(vm, newRowTemplate);
         vm = initItemsTableData.v;
         if(!newRowTemplate){
            var newRowTemplate = initItemsTableData.newRowTemplate;
        }
    });
}else{
    let initItemsTableData = initItemsTable(vm, newRowTemplate);
    vm = initItemsTableData.v;
    if(!newRowTemplate){
        var newRowTemplate = initItemsTableData.newRowTemplate;
    }
}
function initItemsTable(v, newRowTemplate){
    if(!v.data){
        v.data = [];
    }
    if(v.data.length == 0){
    	v.data.push(v.createNewReceipt(v.tilldata, v.db));
    }
    v.round = priceRounding.round;
    
    if(v.data){
    	if(!newRowTemplate && v.data[0].salesItem && v.data[0].salesItem.length){
    		var newRowTemplate = angular.copy(v.data[0].salesItem[0]);
    	}
    	
    	if(!v.gridoptions.data || !v.gridoptions.data.length){
    		v.gridoptions.data = v.data[0].salesItem;
    	}
    	if(!v.gridoptions.gridFooterTemplate){
    		v.gridoptions.gridFooterTemplate = "";
    	}
    }
    v.receiptIsLoading = 0;
    return {v: v, newRowTemplate: newRowTemplate};
}