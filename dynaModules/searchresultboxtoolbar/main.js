var searchParameters = {
    tableViewDetail_tableCode: vm.tablemapcode,
    allowedGroups: [],
    start: 0,
    limit: 99999999
};
var serverDetails = {};

vm.searchData(["online","offline"], searchParameters, "", {serverDetails: serverDetails, db: vm.db, localTableName:"tableView", commandCode:"tableViewSearch", action:"runServerCommand"}).then(function(response){
    if(response.status){
        vm.availableTableViews = response.result;
    }
});
vm.changeView = function(selectedView){
    vm.tablestatevar = selectedView.tableViewDetail_tableState;
}