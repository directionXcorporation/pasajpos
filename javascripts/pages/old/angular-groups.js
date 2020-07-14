app.controller('parentobj.controllers.groups', function($scope, $filter, $window, $http, $cacheFactory, $state, $mdDialog) {
	var selectedArray = [];
	selectedArray.functions = {};
	selectedArray.parentGroups = {};
	$scope.privatedata = {};
	$scope.privatedata.allgroupsWithDetails = [];
	if(!$scope.parentobj.privatedata.allgroupsWithDetailsOriginal){
		$scope.$watch($scope.parentobj.privatedata.allgroups, function () {
			$scope.parentobj.privatedata.allgroupsWithDetailsOriginal = flatListGroupBy($scope.parentobj.privatedata.allgroups,'groupId','parent_groupId','parents','groupName');
			$scope.getGroups();
		});
	}
	$scope.selected = [];
  
	$scope.query = {
		order: 'onlineInsertTime',
		limit: 10,
		page: 1
	};
	$scope.functionsQuery = {
		order: 'categoryName',
		limit: 10,
		page: 1
	};
	$scope.groupParentsQuery = {
		order: 'onlineInsertTime',
		limit: 10,
		page: 1
	};
	
	$scope.makeInitialSelectedStores = function(storesarray){
		var keystore = [];
		angular.forEach(storesarray, function(value, key) {
			keystore.push(value);
		});
		return keystore;
	}
  
	function success(groups) {
		$scope.privatedata.allgroupsWithDetails = groups;
	}
	
	function addFunction(item){
		if(item.functionCode){
			if(!selectedArray.functions[item.functionCode]){
				selectedArray.functions[item.functionCode] = {functionCode:item.functionCode, status:"1"};
			}else{
				if(selectedArray.functions[item.functionCode].status== "0"){
					delete selectedArray.functions[item.functionCode];
				}
			}
		}
	}
	$scope.functionSelected = function(item, key){
		if(item){
			addFunction(item);
			var dependency = $filter('filter')($scope.parentobj.privatedata.allfunctions, {functionCode: item.dependsOnCode});
			if(dependency[0]){
				var isCurrentlySelected = $filter('filter')($scope.privatedata.record.functions, {functionCode: dependency[0].functionCode})[0];
				if(!isCurrentlySelected){
					$scope.privatedata.record.functions.push(dependency[0]);
					addFunction(dependency[0]);
				}
			}
		}
	}
	$scope.functionUnselected = function(item, key){
		if(item.functionCode){
			if(!selectedArray.functions[item.functionCode]){
				selectedArray.functions[item.functionCode] = {functionCode:item.functionCode, status:"0"};
			}else{
				if(selectedArray.functions[item.functionCode].status== "1"){
					delete selectedArray.functions[item.functionCode];
				}
			}
		}
	}
	$scope.parentGroupSelected = function(item, key){
		if(item.groupId){
			if(!selectedArray.parentGroups[item.groupId]){
				selectedArray.parentGroups[item.groupId] = {groupId:item.groupId, status:"1"};
			}else{
				if(selectedArray.parentGroups[item.groupId].status== "0"){
					delete selectedArray.parentGroups[item.groupId];
				}
			}
		}
	}
	$scope.parentGroupUnselected = function(item, key){
		if(item.groupId){
			if(!selectedArray.parentGroups[item.groupId]){
				selectedArray.parentGroups[item.groupId] = {groupId:item.groupId, status:"0"};
			}else{
				if(selectedArray.parentGroups[item.groupId].status== "1"){
					delete selectedArray.parentGroups[item.groupId];
				}
			}
		}
	}
	$scope.getGroups = function() {
		var start = ($scope.query.page - 1) * $scope.query.limit;
		//$scope.promise = $nutrition.desserts.get($scope.query, success).$promise;
		$scope.privatedata.allgroupsWithDetails = $scope.parentobj.privatedata.allgroupsWithDetailsOriginal;
		//$scope.privatedata.allgroupsWithDetails2 = $filter('orderBy')($scope.privatedata.allgroupsWithDetails1,$scope.query.order);
		//$scope.privatedata.allgroupsWithDetails = $filter('limitTo')($scope.privatedata.allgroupsWithDetails2, 1, 1);
	};
	$scope.parentobj.OpenGroupDetails = function(group, action="edit", template='editGroup.html'){
		$scope.privatedata.record = angular.copy(group);
		selectedArray = [];
		selectedArray.functions = {};
		selectedArray.parentGroups = {};
		var originalGroupData = angular.copy(group);
		$mdDialog.show({
			scope: $scope,        // use parent scope in template
	   		preserveScope: true,
			controller: DialogController,
			templateUrl: template,
			parent: angular.element(document.body),
			targetEvent: group,
			clickOutsideToClose: false,
			locals: {
				originalGroupData: originalGroupData,
				groupScope: group,
				actionScope: action
			},
			multiple: true,
			openFrom: "#edit_"+$scope.privatedata.record.groupId,
			closeTo: "#edit_"+$scope.privatedata.record.groupId
		})
		.then(function(group) {
			
		}, function() {
	
		});
	};
				
	$scope.parentobj.getGroupDetails = function(group,action="edit") {
		$scope.parentobj.pageIsLoading = 1;
		$scope.parentobj.setlog("Getting Group Details",1);
		if(action=="edit"){
			if(group.groupId){
				var groupParameters = {
					action: "getGroupDetails", 
					groupId: group.groupId
				};
				$http({
					method : "POST",
					url : "/groups.php",
					data: groupParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					$scope.parentobj.pageIsLoading = 0;
					if(response.data.status){
						if(response.data.group[0].storesId_group){
							$scope.parentobj.privatedata.initialSelectedStores = $scope.makeInitialSelectedStores(response.data.group[0].storesId_group.split(','));
						}else{
							$scope.parentobj.privatedata.initialSelectedStores = [];
						}
						angular.extend(group,response.data.group);
						$scope.parentobj.setlog('',0);
						$scope.parentobj.OpenGroupDetails(group);
					}else{
						$scope.parentobj.setlog(response.data.msg,0);
					}
				}, function submitError(response) {
					$scope.parentobj.setlog(response,0);
					$scope.parentobj.pageIsLoading = 0;
				});
				
				
			}else{
				$scope.parentobj.setlog("Error in finding requested group",0);
				$scope.parentobj.pageIsLoading = 0;
			}
		}else{
			$scope.parentobj.setlog("Creating new group",0);
			$scope.parentobj.pageIsLoading = 0;
			group = {
				groupId:'',
				groupName: '',
				groupDescription: '',
				parents: [],
				children: [],
				functions: [],
				startDate: '',
				endDate: '',
				status: '0',
				onlineInsertTime: moment().format('YYYY-MM-DD')
			};
			$scope.parentobj.OpenGroupDetails(group, action);
		}
	};
	function DialogController(groupScope, actionScope, $scope, $mdDialog, originalGroupData, $mdToast, $http, $templateCache) {
		var recordscope;
		$scope.hide = function() {
			$mdDialog.hide();
		};

		$scope.clickToClose  = function() {
			recordscope = angular.copy($scope.privatedata.record);
			for (var j = 0, lenj = recordscope.parents.length; j < lenj; j++) {
				var itemKeys = Object.keys(recordscope.parents[j]);
				for (var i = 0, len = itemKeys.length; i < len; i++) {
					if(itemKeys[i] != 'groupName' && itemKeys[i] != 'groupId'){
						recordscope.parents[j][itemKeys[i]] = undefined;
					}
				}
			};
			$scope.parentobj.setlog('',0);
			if(angular.equals(recordscope,originalGroupData)){
				$mdDialog.cancel();
			}else{
				confirmDialogClose($mdDialog, $scope.clickToSave, $mdDialog.cancel, $scope);
			}
		};
			
		$scope.clickToSave = function() {
			if($scope.privatedata.record.status === '0' || $scope.privatedata.record.status === 0){
				var confirm = $mdDialog.confirm()
					.title('Confirm Delete')
					.textContent('You are disabling this group and it will not be shown in the list. Are you sure?')
					.ariaLabel('confirm delete')
					.targetEvent()
					.multiple(true)
					.ok('Yes, disable it!')
					.cancel('No! Do NOT disable!');

				$mdDialog.show(confirm).then(function() {
					$scope.saveGroup();
				}, function() {
					showMainformToast('noChangesMade.html', $mdToast);
					$mdDialog.hide();
				});
			}else{
				$scope.saveGroup();
			}
		};
		
		$scope.saveGroup = function(){
			recordscope = angular.copy($scope.privatedata.record);
			recordscope['stores'] = $scope.parentobj.privatedata.selectedStoresArray;
			for (var j = 0, lenj = recordscope.parents.length; j < lenj; j++) {
				var itemKeys = Object.keys(recordscope.parents[j]);
				for (var i = 0, len = itemKeys.length; i < len; i++) {
					if(itemKeys[i] != 'groupName' && itemKeys[i] != 'groupId'){
						recordscope.parents[j][itemKeys[i]] = undefined;
					}
				}
			};
			if(angular.equals(recordscope,originalGroupData)){
				showMainformToast('noChangesMade.html', $mdToast);
				$mdDialog.hide();
			}else{
				$scope.editGroup($mdDialog, $http, $mdToast, $templateCache)
			}
		}
		$scope.editGroup = function($mdDialog, $http, $mdToast, $templateCache) {
			$scope.parentobj.setlog("Sending data to server",1);
			$scope.formDisabled = true;
			$scope.responseErrText = '';
			$scope.groupDetails.$valid = true;
			var options = [];
			var changeArray = {};
			
			//TODO originalSalesScreenData['salesScreenIdstoresId_storeIdGroup']!=recordscope['stores'] check for id of status 1s and count status 0s
			var storeSize = Object.keys(recordscope['stores']).length;
			if(storeSize>0){
				changeArray.changeStores = 1;
			}
			
			if($scope.groupDetails.modified){
				if(recordscope.groupName != originalGroupData.groupName || recordscope.groupDescription != originalGroupData.groupDescription){
					changeArray.changeDetails = 1;
				}else{
					delete recordscope.groupDescription;
					delete recordscope.groupName;
				}
				if(recordscope.status != originalGroupData.status || recordscope.endDate != originalGroupData.endDate  || recordscope.startDate != originalGroupData.startDate){
					changeArray.changeStatus = 1;
				}else{
					delete recordscope.endDate;
					delete recordscope.startDate;
				}
			}
			
			var groupFunctions = recordscope.functions;
			recordscope.functions = undefined;
			if(Object.keys(selectedArray.functions).length){
				changeArray.changeFunctions = 1;
				recordscope.functions = selectedArray.functions;
			}
			
			var groupParents = recordscope.parents;
			delete recordscope.parents;

			if(Object.keys(selectedArray.parentGroups).length){
				changeArray.changeParents = 1;
				recordscope.parents = selectedArray.parentGroups;
			}
			
			delete recordscope.parent_groupId;
			delete recordscope.children;
			delete recordscope.insertBy_userId;
			delete recordscope.insertIp;
			delete recordscope.onlineInsertTime;
			delete recordscope.users;
			if(recordscope[0]){
				delete recordscope[0];
			}
			recordscope.action = actionScope;
			var groupParameters = {
				action: "saveDetails", 
				options: recordscope,
				changeArray: changeArray
			};

			if($scope.groupDetails.$valid){
				$http({
					method : "POST",
					url : "/groups.php",
					data: groupParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					if(response.data.status){
						var i = 0;
						if(actionScope ==="create"){
							if(response.data['addGroup'].status){
								groupScope.groupId = response.data['addGroup'].groupId;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data['addGroup'].msg;
							}
						}
						angular.forEach(changeArray, function(value, key) {
							if(response.data[key].status && value){
								i = i + 1;
								if(key==='changeDetails'){
									groupScope.groupName = recordscope.groupName;
									groupScope.groupDescription = recordscope.groupDescription;
								}else if(key === 'changeStatus'){
									groupScope.status = recordscope.status;
									groupScope.startDate = recordscope.startDate;
									groupScope.endDate = recordscope.endDate;
								}else if(key === 'changeParents'){
									groupScope.parents = groupParents;
								}else if(key === 'changeFunctions'){
									groupScope.functions = groupFunctions;
								}
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}
						});
						if(actionScope ==="create" && response.data['addGroup'].status){
							$scope.parentobj.privatedata.allgroups.push(groupScope);
						}
						var keys = Object.keys(changeArray);
						var len = keys.length;
						if(i === len){
							$mdDialog.hide();
							$mdToast.show({
								hideDelay   : 5000,
								position    : 'top right',
								controller  : 'ToastCtrl',
								templateUrl : 'groupChangeOK.html'
							});
							$scope.parentobj.setlog('',0);
							$scope.formDisabled = false;
						}else{
							$scope.responseErrText = $scope.responseErrText.substring(1);
							$mdToast.show(
								$mdToast.simple()
									.content($scope.responseErrText)
									.hideDelay(10000)
									.position('top right')
									.theme("error-toast")
									.capsule(true)
									.action("X")
							);
							$scope.parentobj.setlog($scope.responseErrText.replace("\n",";"),0);
							$scope.formDisabled = false;
						}
					}else{
						$scope.parentobj.setlog(response.data.msg,0);
						$scope.formDisabled = false;
					}
				}, function submitError(response) {
					$scope.parentobj.setlog(response,0);
					$scope.formDisabled = false;
				});
			}else{
				$scope.formDisabled = false;
				$scope.parentobj.setlog("Errors found that prevented sending form",0);
			}
		}
	}
});