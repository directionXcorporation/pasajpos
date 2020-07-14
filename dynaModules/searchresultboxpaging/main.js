logService.setlog(vm.autopagesize, "debug");
if(vm.autopagesize){
    $timeout(function(){
        //var tableElement = angular.element(document.getElementById('test').children('ui-grid-viewport'));
        let tableElement = angular.element(document.getElementById(vm.tableid).querySelectorAll('[ui-grid-viewport]'));
        if(tableElement.length){
            let tableHeight = tableElement[0].offsetHeight;
            let rowHeight = 30;
            let suggestedPageSize = Math.floor(tableHeight / rowHeight);
            if(suggestedPageSize > 1){
                if(!vm.allpagesizes.find(function(size){return size == suggestedPageSize;})){
                    vm.allpagesizes.unshift(suggestedPageSize);
                }
                vm.pagesize = suggestedPageSize;
            }
        }
    }, 1000);
}

if(vm.pagenumber < 0){
    vm.pagenumber = 1;
}
if(vm.totalitems && vm.pagesize){
    vm.allPagesArray = Array(Math.ceil(vm.totalitems/vm.pagesize)).fill(0).map((e,i)=>i+1);
}else{
    vm.allPagesArray = [1];
}
var pageNumberWatch = $scope.$watch(() => vm.pagenumber, function (newValue, oldValue) {
    if(newValue < 0){
        $timeout(function(){
            vm.pagenumber = 1;
        }, 5);
    }
});
vm.$onChanges = (changes) => {
    if (changes.searchtrigger){
        if(changes.searchtrigger.currentValue == 0){
            return;
        }
        if (changes.searchtrigger.currentValue === changes.searchtrigger.previousValue) {
            return;
        }else if(changes.searchtrigger.currentValue){
            vm.pagenumber = 1;
        }
    }
}