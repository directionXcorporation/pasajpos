let originalSearchFields = angular.copy(vm.searchfields);
if(!vm.searchfields && vm.outputparameters){
	logService.setlog(["Creating screens", {vm: vm}], "debug");
	let items = [];
	let dataKeys = Object.keys(vm.outputparameters);
	let x = 0;
	let y = 0;
    for(let i=0; i<dataKeys.length;i++){
		if((i>0) && (i % parseInt(vm.cols))){
			y = 0;
			x = x + 1;
		}
		if(typeof vm.outputparameters[dataKeys[i]] == "object"){
			vm.outputparameters[dataKeys[i]] = JSON.stringify(vm.outputparameters[dataKeys[i]]);
		}
		let item = {
			"element": "<md-input-container><label translate>"+dataKeys[i]+"</label><textarea ng-model='$ctrl.outputparameters[\""+dataKeys[i]+"\"]'></textarea></md-input-container>",
			"cols": 1,
			"rows": 1,
			"x": x,
			"y": y
		}
		items.push(item);
	}
	vm.searchfields = [{
	    "order": 100,
		"screenId": "main",
		"screenName": "Details",
		"items": items
	}];
}
logService.setlog(["screens",{searchfields: vm.searchfields, originalSearchFields:originalSearchFields}], "debug");

let content = {
	"options": {
		"gridType": "fit",
		"margin": "0",
		"compactType": "none",
		"minCols": 2,
		"maxCols": parseInt(vm.cols) || 4,
		"minRows": 1,
		"maxRows": parseInt(vm.rows) || 2,
		"outerMargin": true
	},
	"screens": vm.searchfields
};
logService.setlog(["showing data",{content: content}], "debug");

if(vm.precallfunction && vm.precallfunction instanceof Function){
    vm.precallfunction();
}
var originalData = angular.copy(vm.outputparameters);

content = gridsterService.initGridster(content);
vm.data = content;