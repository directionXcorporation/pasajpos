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
	vm.gridoptions.cellEditableCondition = function(vm) {
		return vm.row.entity.salesPayment_paymentStatus == 'not attempted';
	}
}

if(!newRowTemplate){
	var newRowTemplate = angular.copy(vm.data[0].salesPayment[0]);
}
if(!vm.gridoptions.data){
	vm.gridoptions.data = vm.data[0].salesPayment;
}