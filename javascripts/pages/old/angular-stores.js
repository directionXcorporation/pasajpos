app.controller('parentobj.controllers.stores',function($scope, $filter, $window, $http, $cacheFactory, $state, $mdDialog, i18nService, uiGridConstants) {
	var selectedArray = [];
	if(!$scope.parentobj){
		$scope.parentobj = {};
		$scope.parentobj.privatedata = {};
	}
	if(!$scope.parentobj.privatedata.selectedStoresArray){
		$scope.parentobj.privatedata.selectedStoresArray = {};
	}
	if(!$scope.parentobj.privatedata.initialSelectedStores){
		$scope.parentobj.privatedata.initialSelectedStores = [];
	}
	$scope.privatedata = {};
	
	$scope.brandsQuery = {
		order: 'onlineInsertTime',
		limit: 10,
		page: 1
	};
	
	$scope.saveNewState = 0;
	
	//Change the field name to field id
	$scope.tableProperties = {showFieldNames : 1};
	$scope.$watch('tableProperties.showFieldNames', function() {
		if($scope.parentobj.privatedata.stores.headers){
			angular.forEach($scope.parentobj.privatedata.stores.headers, function(value, key){
		        	if($scope.tableProperties.showFieldNames){
		        		$scope.parentobj.privatedata.stores.headers[key]['displayName'] = value['fieldName'];
		        	}else{
		        		$scope.parentobj.privatedata.stores.headers[key]['displayName'] = value['fieldId'];
		        	}
        		});
        		$scope.storesGridOption.columnDefs = $scope.parentobj.privatedata.stores.headers;
        		$scope.storesGridApi.core.notifyDataChange(uiGridConstants.dataChange.COLUMN);
        	}
	},true);
	
	//save the grid state
	if(!$scope.parentobj.privatedata.stores){
		$scope.parentobj.privatedata.stores = {};
	}
	if(!$scope.parentobj.privatedata.brandsWithDetails){
		$scope.parentobj.getBrands($scope);
	}
	
	$scope.changeTableView = function(view){
		if(view && view.gridState){
			$scope.storesGridApi.saveState.restore( $scope, view.gridState );
		}
		if(!$scope.storesGridOption.paginationPageSize){
			$scope.storesGridOption.paginationPageSize = $scope.pagination.pageSize;
		}
	}
	$scope.showSaveTableView = function(){
		if($scope.isAllowedToSaveStoreView){
			$scope.saveNewState = 1;
		}
	}
	
	$scope.parentobj.getStores = function(getCount=1,getHeaders=0) {
		
		var start = ($scope.pagination.currentPage - 1) * $scope.pagination.pageSize;
		var getIsAllowedToSaveStoreView = 0;
		var getAllowedStoreViews = 0;
		let getreceipts = 0;
		
		if(!$scope.parentobj.privatedata.stores.headers){
			getHeaders = 1;
		}
		
		if(!$scope.isAllowedToSaveStoreView){
			getIsAllowedToSaveStoreView = 1;
		}
		
		if(!$scope.privatedata.availableStoreViews){
			$scope.privatedata.availableStoreViews = [];
			getAllowedStoreViews = 1;
		}
		if(!$scope.privatedata.availableReceipts){
			$scope.privatedata.availableReceipts = [];
			getreceipts = 1;
		}
		
		if(!$scope.pagination.orderBy){
			$scope.pagination.orderBy = '';
		}
		
		var httpCache = $cacheFactory.get('$http');
		var url = "/stores.php?action=getallstores&start="+start+"&limit="+$scope.pagination.pageSize+"&orderby="+$scope.pagination.orderBy+"&getcount="+getCount+"&getheaders="+getHeaders+"&getIsAllowedToSaveStoreView="+getIsAllowedToSaveStoreView+"&getAllowedStoreViews="+getAllowedStoreViews+"&getreceipts="+getreceipts;

		var cachedResponse = httpCache.get(url);
		if(cachedResponse && $scope.parentobj.privatedata.stores && cachedResponse.data.status){
			$scope.parentobj.pageIsLoading = 0;
			$scope.parentobj.setlog('',0);
			$scope.parentobj.privatedata.stores.details = cachedResponse.data.stores;
			$scope.parentobj.privatedata.stores.headers = cachedResponse.data.headers;
			$scope.pagination.totalItems = cachedResponse.data.totalcount;
			$scope.isAllowedToSaveStoreView = cachedResponse.data.isAllowedToSaveStoreView;
			$scope.privatedata.availableStoreViews = cachedResponse.data.availableStoreViews;
		}else if(!$scope.parentobj.privatedata.stores.details || ($scope.parentobj.privatedata.stores.details && ($scope.currentUrl && url != $scope.currentUrl))){
			$scope.currentUrl = url;
			$http({
				method : "GET",
				cache: false,
				url : url,
				headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
			}).then(function submitSuccess(response) {
				$scope.parentobj.pageIsLoading = 0;
				if(response.data.status){
					$scope.parentobj.setlog('',0);
					$scope.parentobj.privatedata.stores.details = response.data.stores;
					if(response.data.headers && response.data.headers.length>0){
						$scope.parentobj.privatedata.stores.headers = response.data.headers;
					}
					if(response.data.receipts && response.data.receipts.rows && response.data.receipts.rows.length>0 && getreceipts){
						$scope.privatedata.availableReceipts = response.data.receipts.rows;
					}
					if(response.data.totalcount>0){
						$scope.pagination.totalItems = response.data.totalcount;
					}
					var enableGridMenu = false;
					if(response.data.isAllowedToSaveStoreView){
						$scope.isAllowedToSaveStoreView = response.data.isAllowedToSaveStoreView;
						enableGridMenu = true;
						$scope.showSaveTableView();
					}
					
					if(response.data.availableStoreViews){
						$scope.privatedata.availableStoreViews = response.data.availableStoreViews;
					}
					
					$scope.parentobj.updateGrid($scope, 'storesGridOption', 'storesGridApi', enableGridMenu, getIsAllowedToSaveStoreView, $scope.parentobj.privatedata.stores, 'gridRowMenu', 'storesDetail_storeId', $scope.parentobj.privatedata.initialSelectedStores);
					$scope.changeTableView($scope.privatedata.storeSelectedView);
				}else{
					$scope.parentobj.setlog(response.data.msg,0);
				}
			}, function submitError(response) {
				$scope.parentobj.setlog(response,0);
				$scope.parentobj.pageIsLoading = 0;
			});
		}else{
			$scope.parentobj.setlog("",0);
			$scope.parentobj.pageIsLoading = 0;
		}
		
	};
        	$scope.parentobj.initGrid($scope, $scope.parentobj.getStores, 'storesGridOption', 'storesGridApi', 'storeSelectedView', 'availableStoreViews', true, $scope.parentobj.privatedata.selectedStoresArray,'storesDetail_storeId');
	
	
	if(!$scope.pagination.totalItems){
		$scope.parentobj.setlog('Getting Store List From Server',1);
		$scope.parentobj.getStores(1,1);
	}
	
	$scope.makeBrandArray = function(brandString){
		var brandArray = [];
		angular.forEach(brandString.split(','), function(value, key) {
			brandArray.push({brandId: value});
		});
		return brandArray;
	}
	$scope.makeReceiptsArray = function(receiptsString){
		var receiptsArray = [];
		if(receiptsString){
			angular.forEach(receiptsString.split(','), function(value, key) {
				if(value){
					let receiptArray = value.split(':');
					receiptsArray.push({storesIdreceiptsId_receiptId: receiptArray[1], storesIdreceiptsId_receiptType: receiptArray[0]});
				}
			});
		}
		return receiptsArray;
	}
	$scope.storeBrandSelected = function(item, key){
		if(item.brandId){
			if(!selectedArray.storeBrands[item.brandId]){
				selectedArray.storeBrands[item.brandId] = {brandId:item.brandId, status:"1"};
			}else{
				if(selectedArray.storeBrands[item.brandId].status== "0"){
					delete selectedArray.storeBrands[item.brandId];
				}
			}
		}
	}
	
	$scope.storeBrandUnselected = function(item, key){
		if(item.brandId){
			if(!selectedArray.storeBrands[item.brandId]){
				selectedArray.storeBrands[item.brandId] = {brandId:item.brandId, status:"0"};
			}else{
				if(selectedArray.storeBrands[item.brandId].status== "1"){
					delete selectedArray.storeBrands[item.brandId];
				}
			}
		}
	}
	
	$scope.parentobj.OpenStoreDetails = function(store, action="edit", template='editStore.html'){
		store.brands = $scope.makeBrandArray(store['storesIdbrandsId_brandIdGroup']);
		if(!store.receipts){
			store.receipts = $scope.makeReceiptsArray(store['storesIdreceiptsId_receiptIdTypeGroup']);
		}
		$scope.privatedata.record = angular.copy(store);
		selectedArray = [];
		selectedArray.storeBrands = {};
		var originalStoreData = angular.copy(store);
		$mdDialog.show({
			scope: $scope,        // use parent scope in template
	   		preserveScope: true,
			controller: DialogController,
			templateUrl: template,
			parent: angular.element(document.body),
			targetEvent: store,
			clickOutsideToClose: false,
			locals: {
				originalStoreData: originalStoreData,
				storeScope: store,
				actionScope: action
			},
			multiple: true,
			openFrom: "#edit_"+$scope.privatedata.record['storesDetail_storeId'],
			closeTo: "#edit_"+$scope.privatedata.record['storesDetail_storeId']
		})
		.then(function(store) {
			
		}, function() {
	
		});
	};
			
	$scope.parentobj.getStoreDetails = function(store,action="edit") {
	
		$scope.parentobj.pageIsLoading = 1;
		$scope.parentobj.setlog("Getting Store Details",1);
		if(action=="edit"){
			if(store['storesDetail_storeId']){
				var storeParameters = {
					action: "getStoreDetails", 
					storeId: store['storesDetail_storeId']
				};
				
				$http({
					method : "POST",
					url : "/stores.php",
					data: storeParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					$scope.parentobj.pageIsLoading = 0;
					if(response.data.status){
						angular.extend(store,response.data.store.rows[0]);
						$scope.parentobj.setlog('',0);
						$scope.parentobj.OpenStoreDetails(store);
					}else{
						$scope.parentobj.setlog(response.data.msg,0);
					}
				}, function submitError(response) {
					$scope.parentobj.setlog(response,0);
					$scope.parentobj.pageIsLoading = 0;
				});
				
				
			}else{
				$scope.parentobj.setlog("Error in finding requested store",0);
				$scope.parentobj.pageIsLoading = 0;
			}
		}else{
			$scope.parentobj.setlog("Creating new store",0);
			$scope.parentobj.pageIsLoading = 0;
			store = {
				'storesDetail_storeId':'',
				'storesDetail_storeCode': '',
				'storesDetail_storeName': '',
				'storesStatus_status': '1',
				'storesStatus_onlineInsertTime': moment().format('YYYY-MM-DD'),
				'storesIdbrandsId_brandIdGroup':'',
				'brands': [],
				'receipts': [
					{storesIdreceiptsId_receiptId:'', storesIdreceiptsId_receiptType: 'Z'},
					{storesIdreceiptsId_receiptId:'', storesIdreceiptsId_receiptType: 'X'},
					{storesIdreceiptsId_receiptId:'', storesIdreceiptsId_receiptType: 'sales'},
				]
			};
			$scope.parentobj.OpenStoreDetails(store, action);
		}
	};
	function DialogController(storeScope, actionScope, $scope, $mdDialog, originalStoreData, $mdToast, $http, $templateCache) {
		var recordscope;
		$scope.hide = function() {
			$mdDialog.hide();
		};

		$scope.clickToClose  = function() {
			recordscope = angular.copy($scope.privatedata.record);
			for (var j = 0, lenj = recordscope.brands.length; j < lenj; j++) {
				var itemKeys = Object.keys(recordscope.brands[j]);
				for (var i = 0, len = itemKeys.length; i < len; i++) {
					if(itemKeys[i] != 'brandId'){
						recordscope.brands[j][itemKeys[i]] = undefined;
					}
				}
			};
			$scope.parentobj.setlog('',0);

			if(angular.equals(recordscope,originalStoreData)){
				$mdDialog.cancel();
			}else{
				confirmDialogClose($mdDialog,$scope.clickToSave,$mdDialog.cancel);
			}
		};
			
		$scope.clickToSave = function() {
			if($scope.privatedata.record['storesStatus_status'] === '0' || $scope.privatedata.record['storesStatus_status'] === 0){
				var confirm = $mdDialog.confirm()
					.title('Confirm Delete')
					.textContent('You are disabling this store and it will not be shown in the list. Are you sure?')
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
				$scope.saveStore();
			}
		};
		
		$scope.saveStore = function(){
			recordscope = angular.copy($scope.privatedata.record);
			for (var j = 0, lenj = recordscope.brands.length; j < lenj; j++) {
				var itemKeys = Object.keys(recordscope.brands[j]);
				for (var i = 0, len = itemKeys.length; i < len; i++) {
					if(itemKeys[i] != 'brandId'){
						recordscope.brands[j][itemKeys[i]] = undefined;
					}
				}
			};
			if(angular.equals(recordscope,originalStoreData)){
				showMainformToast('noChangesMade.html', $mdToast);
				$mdDialog.hide();
			}else{
				$scope.editStore($mdDialog, $http, $mdToast, $templateCache)
			}
		}
		$scope.editStore = function($mdDialog, $http, $mdToast, $templateCache) {
			$scope.parentobj.setlog("Sending data to server",1);
			$scope.formDisabled = true;
			$scope.responseErrText = '';
			$scope.storeDetails.$valid = true;
			var options = [];
			var changeArray = {};

			if($scope.storeDetails.modified){
				if(recordscope['storesDetail_storeCode'] != originalStoreData['storesDetail_storeCode'] || recordscope['storesDetail_storeName'] != originalStoreData['storesDetail_storeName']){
					changeArray.changeDetails = 1;
				}else{
					delete recordscope['storesDetail_storeName'];
					delete recordscope['storesDetail_storeCode'];
				}
				if(recordscope['storesStatus_status'] != originalStoreData['storesStatus_status'] || actionScope ==="create"){
					changeArray.changeStatus = 1;
				}
			}else{
				delete recordscope['storesDetail_storeName'];
				delete recordscope['storesDetail_storeCode'];
				delete recordscope['storesStatus_status'];
			}
			
			if($scope.storeContact.modified){
				changeArray.changeContact = 1;
			}else{
				delete recordscope['storesContact_addressLine1'];
				delete recordscope['storesContact_addressLine2'];
				delete recordscope['storesContact_city'];
				delete recordscope['storesContact_state'];
				delete recordscope['storesContact_country'];
				delete recordscope['storesContact_phone'];
				delete recordscope['storesContact_cell'];
				delete recordscope['storesContact_email'];
				delete recordscope['storesContact_zipcode'];
			}
			
			if($scope.storeReceipts.modified){
				changeArray.changeReceipts = 1;
			}else{
				delete recordscope.receipts;
			}
			var storeBrands = recordscope.brands;
			delete recordscope.brands;
			if(Object.keys(selectedArray.storeBrands).length){
				changeArray.changeBrands = 1;
				recordscope.brands = selectedArray.storeBrands;
			}
			
			delete recordscope['storesIdbrandsId_brandIdGroup'];
			delete recordscope['storesStatus_onlineInsertTime'];
			
			if(recordscope[0]){
				delete recordscope[0];
			}
			recordscope.action = actionScope;
			var storeParameters = {
				action: "saveDetails", 
				options: recordscope,
				changeArray: changeArray
			};

			if($scope.storeDetails.$valid){
				$http({
					method : "POST",
					url : "/stores.php",
					data: storeParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					if(response.data.status){
						var i = 0;
						if(actionScope ==="create"){
							if(response.data['addStore'].status){
								storeScope['storesDetail_storeId'] = response.data['addStore'].storesId_storeId;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data['addStore'].msg;
							}
						}
						
						angular.forEach(changeArray, function(value, key) {
							if(response.data[key].status && value){
								i = i + 1;
								if(key==='changeDetails'){
									storeScope['storesDetail_storeName'] = recordscope['storesDetail_storeName'];
									storeScope['storesDetail_storeCode'] = recordscope['storesDetail_storeCode'];
								}else if(key === 'changeStatus'){
									storeScope['storesStatus_status'] = recordscope['storesStatus_status'];
								}else if(key === 'changeBrands'){
									storeScope['brands'] = storeBrands;
									var brandNameGroup = [];
									var brandCodeGroup = [];
									var brandIdGroup = [];
									angular.forEach(storeBrands, function(val,key){
									
										var brandDetails = $filter('filter')($scope.parentobj.privatedata.brandsWithDetails, {brandId: val.brandId})[0];
										brandNameGroup.push(brandDetails['brandName']);
										brandCodeGroup.push(brandDetails['brandCode']);
										brandIdGroup.push(brandDetails['brandId']);
									});
									storeScope['storesIdbrandsId_brandIdGroup'] = brandIdGroup.join(',');
									storeScope['brandsDetail_brandCodeGroup'] = brandCodeGroup.join(',');
									storeScope['brandsDetail_brandNameGroup'] = brandNameGroup.join(',');
								}else if(key === 'changeContact'){
									storeScope['storesContact_phone'] = recordscope['storesContact_phone'];
									storeScope['storesContact_cell'] = recordscope['storesContact_cell'];
									storeScope['storesContact_email'] = recordscope['storesContact_email'];
									storeScope['storesContact_addressLine1'] = recordscope['storesContact_addressLine1'];
									storeScope['storesContact_addressLine2'] = recordscope['storesContact_addressLine2'];
									storeScope['storesContact_city'] = recordscope['storesContact_city'];
									storeScope['storesContact_state'] = recordscope['storesContact_state'];
									storeScope['storesContact_country'] = recordscope['storesContact_country'];
									storeScope['storesContact_zipcode'] = recordscope['storesContact_zipcode'];
								}
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}
						});
						if(actionScope ==="create" && response.data['addStore'].status){
							$scope.parentobj.privatedata.stores.details.rows.push(storeScope);
							$scope.pagination.totalItems = $scope.pagination.totalItems + 1;
						}
						var keys = Object.keys(changeArray);
						var len = keys.length;
						if(i === len){
							$mdDialog.hide();
							$mdToast.show({
								hideDelay   : 5000,
								position    : 'top right',
								controller  : 'ToastCtrl',
								templateUrl : 'storeChangeOK.html'
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