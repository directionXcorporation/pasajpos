let onRegisterApi = function(gridApi) {
    
}
if(!vm.gridoptions){
    let initOptions = {
        showColumnFooter: true,
        enableSorting: false,
        enableColumnMenus: false
    };
	vm.gridoptions = vm.initGrid(onRegisterApi, initOptions);
	vm.gridoptions.showGridFooter = false;
}

if(!vm.data || !vm.data.length){
    if(!vm.data){
        vm.data = [];
    }
    
    if(!lastTillOperationDetails){
        var lastTillOperationDetails = vm.getLastTillOperation(vm.tilldata, vm.db);
    }
    if(vm.data.length == 0){
    	let newTillOperation = vm.createNewTillOperation(vm.tilldata, vm.db, lastTillOperationDetails);
    	vm.data.push(newTillOperation);
    }
    var AllowedTillPaymentMethods = vm.tilldata.tillPaymentMethods.filter(method => method.tillIdpaymentMethodId_status == 1);
    var includedPaymentMethods = createTillOperationPaymentMethods(vm.data[0].tillOperation_tillOperationType, AllowedTillPaymentMethods, vm.db);
    var paymentExpectedSums;
    
    var startTime = getCurrentTime();
    var endTime = angular.copy(startTime);
    if(lastTillOperationDetails && lastTillOperationDetails.length){
        startTime = lastTillOperationDetails[0].tillOperation_tillOperationTime;
    }
    if(vm.data){
        let tillPaymentShot = [];
        let salesSearchOptions = {
            startTime: startTime, 
            endTime: endTime
        };
        
        let salesDetails = vm.searchOfflineSales(vm.db, salesSearchOptions);
        paymentExpectedSums = createPaymentMethodSums(salesDetails, includedPaymentMethods);
        tillPaymentShot = createTillOperationPaymentExpectedShot(paymentExpectedSums, vm.data[0]);
        
        vm.data[0].tillPaymentShot = tillPaymentShot;
    }
}

if(!vm.gridoptions.data && vm.data[0].tillPaymentShot){
	vm.gridoptions.data = vm.data[0].tillPaymentShot;
}