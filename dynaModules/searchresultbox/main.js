let thisGridApi;
let dataHistory = [];
let gotAllData = false;
let orderBy = "";

//SET INITIAL TO 1 to offset the -1 in the getData response for first page. Totalitem does not need to be exact as it is only used to know if there will be a next page
vm.totalitems = 1;
vm.callinprogress = 0;

if(!vm.data){
    vm.data = [];
}
let getData = function(){
    try{
        vm.callinprogress = 1;
        //Add a +1 to know if there are more items to fetch on the next page
        if(!vm.searchoptions.serverDetails){
            vm.searchoptions.serverDetails = {};
        }
        if(!vm.searchoptions.serverDetails.optionParams){
            vm.searchoptions.serverDetails.optionParams = {};
        }
        vm.searchoptions.serverDetails.optionParams.orderBy = orderBy;
        vm.searchoptions.serverDetails.optionParams.limit = vm.pagesize + 1;
        let currentPage = vm.pagenumber;
        if(currentPage <= 0){
            currentPage = 1;
        }
        vm.searchoptions.serverDetails.optionParams.start = (currentPage - 1) * vm.pagesize;
    
        vm.getObjectsDetail({searchParameters: vm.searchparameters, resultVarName: undefined, searchOptions: vm.searchoptions}).then(function(data){
            logService.setlog(["data fetched",{searchParameters: vm.searchparameters, resultVarName: undefined, searchOptions: vm.searchoptions, data:data,currentPage:currentPage,pageSize:vm.pagesize,orderBy:orderBy}], "debug");
            let dataLength = data.length;
            if(dataLength){
                if(dataLength > vm.pagesize){
                    data.length = vm.pagesize;
                }
                vm.gridoptions.data = data;
                //Only add to totalitems if this is not the last page.or this is the first time we are getting data
                if(dataHistory.length && vm.pagenumber == 1){
                    vm.totalitems = 1;
                    dataHistory = [];
                }
                
                if(dataLength > vm.pagesize || !dataHistory.length){
                    //SET it ro -1 to offset the +1 in the limit
                    vm.totalitems = vm.totalitems + dataLength - 1;
                    dataHistory = dataHistory.concat(data);
                    if(dataLength <= vm.pagesize){
                        gotAllData = true;
                    }
                }else if(dataLength <= vm.pagesize){
    				vm.totalitems = vm.totalitems + dataLength - 1;
                    dataHistory = dataHistory.concat(data);
                    gotAllData = true;
                }
            }else{
    			dataHistory = [];
    			vm.totalitems = 1;
    			gotAllData = true;
    			vm.gridoptions.data = dataHistory;
    		}
    		vm.callinprogress = 0;
            
    		thisGridApi.core.notifyDataChange(uiGridConstants.dataChange.OPTIONS);
            if(!vm.columndefs){
                let columnDefs = [];
                if(vm.gridoptions.data.length){
                    let firstRow = vm.gridoptions.data[0];
                    let dataKeys = Object.keys(firstRow);
                    for(let t=0; t<dataKeys.length;t++){
                        columnDefs.push({field: dataKeys[t]});
                    }
                    let rowDetailsButton = {
                        "field": dataKeys[0],
                        "displayName": "Actions",
                        "headerCellFilter": "translate",
                        "enableCellEdit": false,
                        "enableFiltering": false,
                        "cellTemplate": "<md-button class='md-raised' style='margin: 0px;padding: 0px;height: 99%;width:99%;' data-ng-click='grid.appScope.$ctrl.showRowDetails({id: row.entity[\""+dataKeys[0]+"\"], pageCode:\"rowDetail\", row: row, rowIndex: rowRenderIndex, data:{searchParameters:row.entity}},1);'><md-icon class='material-icons' style='vertical-align: baseline;'>pageview</md-icon></md-button>"
                    };
                    columnDefs.push(rowDetailsButton);
                }
                vm.gridoptions.columnDefs = columnDefs;
                thisGridApi.core.notifyDataChange(uiGridConstants.dataChange.COLUMN);
            }
        });
    }
    catch(e){
        logService.setlog(["An error occured while fetching results", e], "error", 0);
    }
}
let saveState = function(){
    if(thisGridApi){
        vm.tablestatevar = thisGridApi.saveState.save();
    }
}
let restoreState = function(scopeVar, newState){
    if(thisGridApi){
        thisGridApi.saveState.restore(scopeVar, newState);
    }
}
let onRegisterApi = function(gridApi) {
    gridApi.selection.on.rowSelectionChanged($scope,function(row){
        if(row.isSelected){
            sharedata.addItem({type: "selected", origin: "searchResult", data: row.entity}, vm.pageid);
        }else{
            sharedata.addItem({type: "unselected", origin: "searchResult",data: row.entity}, vm.pageid);
        }
    });
    
    gridApi.selection.on.rowSelectionChangedBatch($scope,function(rows){
        let selectedRows = [];
        let unselectedRows = [];
        for(let j=0;j<rows.length; j++){
            if(rows[j].isSelected){
                sharedata.addItem({type: "selected", origin: "searchResult",data: rows[j].entity}, vm.pageid);
            }else{
                sharedata.addItem({type: "unselected", origin: "searchResult",data: rows[j].entity}, vm.pageid);
            }
        }
    });
    /*gridApi.pagination.on.paginationChanged($scope, function (newPage, pageSize) {
        vm.pagenumber = newPage;
        vm.pagesize = pageSize;
        getData();
    });*/
    gridApi.colMovable.on.columnPositionChanged($scope, saveState);
    gridApi.colResizable.on.columnSizeChanged($scope, saveState);
    gridApi.grouping.on.aggregationChanged($scope, saveState);
    gridApi.grouping.on.groupingChanged($scope, saveState);
    gridApi.core.on.columnVisibilityChanged($scope, saveState);
    gridApi.core.on.filterChanged($scope, saveState);
    gridApi.core.on.sortChanged( $scope, function( grid, sortColumns ) {
        saveState();
        let sortArray = [];
        let sortString = "";
        for(let i=0; i<sortColumns.length; i++){
            sortArray[sortColumns[i].sort.priority] = {
                "field": sortColumns[i].field, 
                "direction": sortColumns[i].sort.direction
            }
        }
        for(let i=0; i<sortArray.length; i++){
            sortString = sortString + sortArray[i].field + " " + sortArray[i].direction;
            if(i < sortArray.length - 1){
                sortString = sortString + ", ";
            }
        }
        
        //reset data history if not all data are fetched
        if(!gotAllData){
            dataHistory = [];
            orderBy = sortString;
            getData();
        }else{
            let sortArrayFields = sortArray.map(function(elem){
                return elem.field;
            });
            let sortArrayDirections = sortArray.map(function(elem){
                return elem.direction;
            });
            dataHistory = lodash.orderBy(dataHistory, sortArrayFields, sortArrayDirections);
            vm.gridoptions.data = dataHistory;
            
            gridApi.core.notifyDataChange(uiGridConstants.dataChange.OPTIONS);
            if(!vm.columndefs){
                gridApi.core.notifyDataChange(uiGridConstants.dataChange.COLUMN);
            }
            
        }
    });
    thisGridApi = gridApi;
}

if(!vm.gridoptions){
    let initOptions = {
        showColumnFooter: vm.showcolumnfooter,
        enableSorting: vm.enablesorting,
        enableColumnMenus: vm.enablecolumnmenus,
        columnDefs: vm.columndefs,
        useExternalPagination: vm.externalpaging,
        useExternalSorting: vm.externalsorting,
        useExternalFiltering: vm.externalfiltering,
        paginationPageSize: vm.pagesize,
        paginationPageSizes: vm.allpagesizes,
        totalItems: vm.totalitems
    };
    vm.gridoptions = vm.initGrid(onRegisterApi, initOptions);
}

var tableStateWatch;
if(!tableStateWatch){
    tableStateWatch = $scope.$watch(() => vm.tablestatevar, function (newValue, oldValue) {
        if(newValue !== oldValue){
            restoreState($scope, newValue);
        }
    });
}
vm.$onChanges = (changes) => {
    if (changes.searchtrigger && !vm.callinprogress){
        if(changes.searchtrigger.currentValue == 0){
            return;
        }
        if (changes.searchtrigger.currentValue === changes.searchtrigger.previousValue) {
            return;
        }else if(changes.searchtrigger.currentValue){
            logService.setlog("Start data search", "debug");
            vm.totalitems = 1;
            dataHistory =[];
            vm.pagenumber = 1;
			gotAllData = false;
            getData();
        }
    }else{
        if (changes.pagenumber && !vm.callinprogress){
            if (changes.pagenumber.currentValue == changes.pagenumber.previousValue) {
                return;
            }else if(changes.pagenumber.currentValue && changes.pagenumber.previousValue != 0){
                logService.setlog("changing page", "debug");
                let currentPage = changes.pagenumber.currentValue;
                if(currentPage == -1){
                    currentPage = 1;
                }
                let cachedData = dataHistory.slice((currentPage - 1)*vm.pagesize, vm.pagesize * currentPage);
                if(cachedData.length >= vm.pagesize || gotAllData){
                    vm.gridoptions.data = cachedData;
                }else{
                    getData();
                }
            }
        }
    }
    return;
};