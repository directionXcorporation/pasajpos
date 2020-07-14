app.controller('parentobj.controllers.brands', function($scope, $filter, $window, $http, $cacheFactory, $state, $mdDialog) {
	var selectedArray = [];
	selectedArray.functions = {};
	selectedArray.parentBrands = {};
	$scope.privatedata = {};
	
	$scope.selected = [];
  	if(!$scope.parentobj.privatedata.selectedBrandsArray){
  		$scope.parentobj.privatedata.selectedBrandsArray = {};
  	}
  	if(!$scope.parentobj.privatedata.initialSelectedBrands){
		$scope.parentobj.privatedata.initialSelectedBrands = [];
	}
  	$scope.brandSelected = function(item, key){
		if(item.brandId){
			if(!$scope.parentobj.privatedata.selectedBrandsArray[item.brandId]){
				$scope.parentobj.privatedata.selectedBrandsArray[item.brandId] = {brandId:item.brandId, status:"1"};
			}else{
				if($scope.parentobj.privatedata.selectedBrandsArray[item.brandId].status== "0"){
					delete $scope.parentobj.privatedata.selectedBrandsArray[item.brandId];
				}
			}
		}
	}
	$scope.brandUnselected = function(item, key){
		if(item.brandId){
			if(!$scope.parentobj.privatedata.selectedBrandsArray[item.brandId]){
				$scope.parentobj.privatedata.selectedBrandsArray[item.brandId] = {brandId:item.brandId, status:"0"};
			}else{
				if($scope.parentobj.privatedata.selectedBrandsArray[item.brandId].status== "1"){
					delete $scope.parentobj.privatedata.selectedBrandsArray[item.brandId];
				}
			}
		}
	}
  	
	$scope.query = {
		order: 'onlineInsertTime',
		limit: 10,
		page: 1
	};

	$scope.parentsQuery = {
		order: 'onlineInsertTime',
		limit: 10,
		page: 1
	};
	if(!$scope.parentobj.privatedata.brandsWithDetails){
		$scope.parentobj.privatedata.brandsWithDetails = $scope.parentobj.getBrands($scope);
	}

	$scope.makeparent = function(parentarray){
		var keyparent = [];
		angular.forEach(parentarray, function(value, key) {
			keyparent.push({brandId: value});
		});
		return keyparent;
	}

	$scope.parentBrandSelected = function(item, key){
		if(item.brandId){
			if(!selectedArray.parentBrands[item.brandId]){
				selectedArray.parentBrands[item.brandId] = {brandId:item.brandId, status:"1"};
			}else{
				if(selectedArray.parentBrands[item.brandId].status== "0"){
					delete selectedArray.parentBrands[item.brandId];
				}
			}
		}
	}
	$scope.parentBrandUnselected = function(item, key){
		if(item.brandId){
			if(!selectedArray.parentBrands[item.brandId]){
				selectedArray.parentBrands[item.brandId] = {brandId:item.brandId, status:"0"};
			}else{
				if(selectedArray.parentBrands[item.brandId].status== "1"){
					delete selectedArray.parentBrands[item.brandId];
				}
			}
		}
	}
	
	$scope.parentobj.OpenBrandDetails = function(brand, action="edit", template='editBrand.html'){
		$scope.privatedata.record = angular.copy(brand);
		selectedArray = [];
		selectedArray.parentBrands = {};
		var originalBrandData = angular.copy(brand);
		$mdDialog.show({
			scope: $scope,        // use parent scope in template
	   		preserveScope: true,
			controller: DialogController,
			templateUrl: template,
			parent: angular.element(document.body),
			targetEvent: brand,
			clickOutsideToClose: false,
			locals: {
				originalBrandData: originalBrandData,
				brandScope: brand,
				actionScope: action
			},
			multiple: true,
			openFrom: "#edit_"+$scope.privatedata.record.brandId,
			closeTo: "#edit_"+$scope.privatedata.record.brandId
		})
		.then(function(brand) {
			
		}, function() {
	
		});
	};
				
	$scope.parentobj.getBrandDetails = function(brand,action="edit") {
		$scope.parentobj.pageIsLoading = 1;
		$scope.parentobj.setlog("Getting Brand Details",1);
		if(action=="edit"){
			if(brand.brandId){
				var brandParameters = {
					action: "getBrandDetails", 
					brandId: brand.brandId
				};
				$http({
					method : "POST",
					url : "/brands.php",
					data: brandParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					$scope.parentobj.pageIsLoading = 0;
					if(response.data.status){
						angular.extend(brand,response.data.brand);
						$scope.parentobj.setlog('',0);
						$scope.parentobj.OpenBrandDetails(brand);
					}else{
						$scope.parentobj.setlog(response.data.msg,0);
					}
				}, function submitError(response) {
					$scope.parentobj.setlog(response,0);
					$scope.parentobj.pageIsLoading = 0;
				});
				
				
			}else{
				$scope.parentobj.setlog("Error in finding requested brand",0);
				$scope.parentobj.pageIsLoading = 0;
			}
		}else{
			$scope.parentobj.setlog("Creating new brand",0);
			$scope.parentobj.pageIsLoading = 0;
			brand = {
				brandId:'',
				brandCode: '',
				brandName: '',
				parents: [],
				children: [],
				status: '1',
				onlineInsertTime: moment().format('YYYY-MM-DD')
			};
			$scope.parentobj.OpenBrandDetails(brand, action);
		}
	};
	function DialogController(brandScope, actionScope, $scope, $mdDialog, originalBrandData, $mdToast, $http, $templateCache) {
		var recordscope;
		$scope.hide = function() {
			$mdDialog.hide();
		};

		$scope.clickToClose  = function() {
			recordscope = angular.copy($scope.privatedata.record);
			for (var j = 0, lenj = recordscope.parents.length; j < lenj; j++) {
				var itemKeys = Object.keys(recordscope.parents[j]);
				for (var i = 0, len = itemKeys.length; i < len; i++) {
					if(itemKeys[i] != 'brandId'){
						recordscope.parents[j][itemKeys[i]] = undefined;
					}
				}
			};
			$scope.parentobj.setlog('',0);

			if(angular.equals(recordscope,originalBrandData)){
				$mdDialog.cancel();
			}else{
				confirmDialogClose($mdDialog,$scope.clickToSave,$mdDialog.cancel);
			}
		};
			
		$scope.clickToSave = function() {
			if($scope.privatedata.record.status === '0' || $scope.privatedata.record.status === 0){
				var confirm = $mdDialog.confirm()
					.title('Confirm Delete')
					.textContent('You are disabling this brand and it will not be shown in the list. Are you sure?')
					.ariaLabel('confirm delete')
					.targetEvent()
					.multiple(true)
					.ok('Yes, disable it!')
					.cancel('No! Do NOT disable!');

				$mdDialog.show(confirm).then(function() {
					$scope.saveBrand();
				}, function() {
					showMainformToast('noChangesMade.html', $mdToast);
					$mdDialog.hide();
				});
			}else{
				$scope.saveBrand();
			}
		};
		
		$scope.saveBrand = function(){
			recordscope = angular.copy($scope.privatedata.record);
			for (var j = 0, lenj = recordscope.parents.length; j < lenj; j++) {
				var itemKeys = Object.keys(recordscope.parents[j]);
				for (var i = 0, len = itemKeys.length; i < len; i++) {
					if(itemKeys[i] != 'brandCode' && itemKeys[i] != 'brandId'){
						recordscope.parents[j][itemKeys[i]] = undefined;
					}
				}
			};
			if(angular.equals(recordscope,originalBrandData)){
				showMainformToast('noChangesMade.html', $mdToast);
				$mdDialog.hide();
			}else{
				$scope.editBrand($mdDialog, $http, $mdToast, $templateCache)
			}
		}
		$scope.editBrand = function($mdDialog, $http, $mdToast, $templateCache) {
			$scope.parentobj.setlog("Sending data to server",1);
			$scope.formDisabled = true;
			$scope.responseErrText = '';
			$scope.brandDetails.$valid = true;
			var options = [];
			var changeArray = {};

			if($scope.brandDetails.modified){
				if(recordscope.brandCode != originalBrandData.brandCode || recordscope.brandName != originalBrandData.brandName){
					changeArray.changeDetails = 1;
				}else{
					delete recordscope.brandName;
					delete recordscope.brandCode;
				}
				if(recordscope.status != originalBrandData.status || actionScope ==="create"){
					changeArray.changeStatus = 1;
				}
			}
			
			var brandParents = recordscope.parents;
			delete recordscope.parents;

			if(Object.keys(selectedArray.parentBrands).length){
				changeArray.changeParents = 1;
				recordscope.parents = selectedArray.parentBrands;
			}
			
			delete recordscope.parent_brandId;
			delete recordscope.children;
			delete recordscope.insertBy_userId;
			delete recordscope.insertIp;
			delete recordscope.onlineInsertTime;
			if(recordscope[0]){
				delete recordscope[0];
			}
			recordscope.action = actionScope;
			var brandParameters = {
				action: "saveDetails", 
				options: recordscope,
				changeArray: changeArray
			};

			if($scope.brandDetails.$valid){
				$http({
					method : "POST",
					url : "/brands.php",
					data: brandParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					if(response.data.status){
						var i = 0;
						if(actionScope ==="create"){
							if(response.data['addBrand'].status){
								brandScope.brandId = response.data['addBrand'].brandId;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data['addBrand'].msg;
							}
						}
						angular.forEach(changeArray, function(value, key) {
							if(response.data[key].status && value){
								i = i + 1;
								if(key==='changeDetails'){
									brandScope.brandName = recordscope.brandName;
									brandScope.brandCode = recordscope.brandCode;
								}else if(key === 'changeStatus'){
									brandScope.status = recordscope.status;
								}else if(key === 'changeParents'){
									brandScope.parents = brandParents;
								}
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}
						});
						if(actionScope ==="create" && response.data['addBrand'].status){
							$scope.parentobj.privatedata.brandsWithDetails.push(brandScope);
						}
						var keys = Object.keys(changeArray);
						var len = keys.length;
						if(i === len){
							$mdDialog.hide();
							$mdToast.show({
								hideDelay   : 5000,
								position    : 'top right',
								controller  : 'ToastCtrl',
								templateUrl : 'brandChangeOK.html'
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