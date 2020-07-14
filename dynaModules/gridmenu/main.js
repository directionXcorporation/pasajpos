vm.currentData = angular.copy(vm.data);

vm.nodeClick = function(action){
    let nodeAction = function(){
      eval(action);
    };
    nodeAction();
}