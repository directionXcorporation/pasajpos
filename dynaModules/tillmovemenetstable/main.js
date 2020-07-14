let onRegisterApi = function(gridApi) {
    
}
if(!vm.gridoptions){
    let initOptions = {
        showColumnFooter: true,
        enableSorting: true,
        enableColumnMenus: true
    };
	vm.gridoptions = vm.initGrid(onRegisterApi, initOptions);
	vm.gridoptions.showGridFooter = false;
}

if(vm.data && (!vm.data[0].tillPaymentMovement || !vm.data[0].tillPaymentMovement.length)){
    var AllowedTillPaymentMethods = vm.tilldata.tillPaymentMethods.filter(method => method.tillIdpaymentMethodId_status == 1);
    var includedPaymentMethods = createTillOperationPaymentMethods("movement", AllowedTillPaymentMethods, vm.db);
    
    let tillPaymentMovement = createTillMovements(vm.data[0], includedPaymentMethods);
    vm.data[0].tillPaymentMovement = tillPaymentMovement.tillPaymentMovement;
    vm.maxTransferAmount = tillPaymentMovement.maxAmountAvailableToTransfer;
}
if(!vm.gridoptions.data && vm.data[0].tillPaymentMovement){
    vm.gridoptions.data = vm.data[0].tillPaymentMovement;
}