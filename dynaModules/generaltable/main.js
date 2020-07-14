vm.tableIsLoading = 1;
let onRegisterApi = function(gridApi) {
    
}

let newRowTemplate = angular.copy(vm.newrowtemplate);

if(!vm.gridoptions){
    logService.setlog(["column defenitions",{columndefs: vm.columndefs}], "debug");
    let columndefs = [];
    if(!vm.columndefs){
        for(let key in newRowTemplate){
            if (newRowTemplate.hasOwnProperty(key)) {
                    if(typeof newRowTemplate[key] == 'number'){
                        columndefs.push({field:key, type:'number', enableCellEdit:false, visible: (newRowTemplate[key]==0), cellTemplate: '<md-input-container class="md-icon-float md-icon-right md-block"><input ng-model="grid.appScope.$ctrl.gridoptions.data[rowRenderIndex][\''+key+'\']" title="'+key+'" type="number" ng-keypress="grid.appScope.$ctrl.addRow($event)" /></md-input-container>'});
                    }else{
                        columndefs.push({field:key, enableCellEdit:false, visible: (newRowTemplate[key]==''), cellTemplate: '<md-input-container class="md-icon-float md-icon-right md-block"><input ng-model="grid.appScope.$ctrl.gridoptions.data[rowRenderIndex][\''+key+'\']" title="'+key+'" type="text" ng-keypress="grid.appScope.$ctrl.addRow($event)" /><md-icon class="material-icons" data-ng-click="grid.appScope.$ctrl.advancedSelectItem_inventory({data:grid.appScope.$ctrl.gridoptions.data, index: rowRenderIndex});">launch</md-icon></md-input-container>'});
                    }
            }
        }
    }else{
        columndefs = vm.columndefs;
    }
    let initOptions = {
        showColumnFooter: false,
        enableSorting: false,
        enableColumnMenus: false,
        columnDefs: columndefs
    };
	vm.gridoptions = vm.initGrid(onRegisterApi, initOptions);
	vm.gridoptions.showGridFooter = false;
    vm.gridoptions.enableCellEdit = false;
    vm.gridoptions.enableCellEditOnFocus = false;
}

vm.gridoptions.data = vm.items;
if(!vm.gridoptions.data){
    vm.gridoptions.data = [];
    vm.gridoptions.data.push(newRowTemplate);
}
let listener = $scope.$watch(function(){return vm.items;}, function(newValue, oldValue) {
    if(newValue != oldValue){
        vm.gridoptions.data = newValue;
        vm.gridoptions.data.push(newRowTemplate);
        if(newValue && newValue.length){
            listener();
        }
    }
});
let length = vm.gridoptions.data.length;
if(length==0){
    let newRowTemplate3 = angular.copy(vm.newrowtemplate);
    vm.gridoptions.data.push(newRowTemplate3);
}
vm.addRow = function(keyEvent, data_input=vm.gridoptions.data, newrowtemplate_input=vm.newrowtemplate) {
    logService.setlog("adding new row main", "debug");
    if (keyEvent.which === 13){
        let length = data_input.length;
        logService.setlog("adding new row main length "+length, "debug");
        if(length){
            if(data_input[length-1].inventoryMovementsDetails_itemBarcode !=''){
                let newRowTemplate2 = angular.copy(newrowtemplate_input);
                logService.setlog(["adding new row",{newRowTemplate: newRowTemplate2}], "debug");
                data_input.push(newRowTemplate2);
                $timeout(function(){
                    let element = document.querySelector('#movementItemBarcode_'+(length));
                    if(element){
                        element.focus();
                        logService.setlog("focus on "+"#movementItemBarcode_"+(length),"debug");
                    }
                },1);
            }
        }
    }
}

vm.tableIsLoading = 0;

logService.setlog(["initializing grid",{gridoptions: vm.gridoptions, items: vm.items}], "debug");