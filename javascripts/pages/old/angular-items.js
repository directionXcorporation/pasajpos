app.controller('parentobj.controllers.items',function($scope, $filter, $window, $http, $cacheFactory, $state, $mdDialog, i18nService, uiGridConstants, $sce) {

	$scope.privatedata = {};
	if(!$scope.parentobj.selectedItemsArray){
		$scope.parentobj.selectedItemsArray = {};
	}
	if(!$scope.parentobj.afterItemSingleSelectCallbackFunction){
		$scope.parentobj.afterItemSingleSelectCallbackFunction = undefined;
	}
	if(!$scope.parentobj.privatedata.initialSelectedItems){
		$scope.parentobj.privatedata.initialSelectedItems = [];
	}
	$scope.saveNewState = 0;
	
	$scope.makeInitialSelectedBrands = function(brandarray){
		var keybrand = [];
		angular.forEach(brandarray, function(value, key) {
			keybrand.push({brandId: value});
		});
		return keybrand;
	}
	
	//Change the field name to field id
	$scope.tableProperties = {showFieldNames : 1};
	$scope.$watch('tableProperties.showFieldNames', function() {
		if($scope.parentobj.privatedata.items.headers){
			angular.forEach($scope.parentobj.privatedata.items.headers, function(value, key){
		        	if($scope.tableProperties.showFieldNames){
		        		$scope.parentobj.privatedata.items.headers[key]['displayName'] = value['fieldName'];
		        	}else{
		        		$scope.parentobj.privatedata.items.headers[key]['displayName'] = value['fieldId'];
		        	}
        		});
        		$scope.itemsGridOption.columnDefs = $scope.parentobj.privatedata.items.headers;
        		$scope.itemsGridApi.core.notifyDataChange(uiGridConstants.dataChange.COLUMN);
        	}
	},true);
	
	if(!$scope.parentobj.privatedata.items){
		$scope.parentobj.privatedata.items = {};
	}
	
	$scope.changeTableView = function(view){
		if(view && view.gridState){
			$scope.itemsGridApi.saveState.restore( $scope, view.gridState );
		}
		if(!$scope.itemsGridOption.paginationPageSize){
			$scope.itemsGridOption.paginationPageSize = $scope.pagination.pageSize;
		}
	}
	$scope.showSaveTableView = function(){
		if($scope.isAllowedToSaveItemView){
			$scope.saveNewState = 1;
		}
	}
	$scope.parentobj.getItems = function(getCount=1,getHeaders=0) {
		var start = ($scope.pagination.currentPage - 1) * $scope.pagination.pageSize;
		var getIsAllowedToSaveItemView = 0;
		var getAllowedItemViews = 0;

		if(!$scope.parentobj.privatedata.items.headers){
			getHeaders = 1;
		}
		
		if(!$scope.isAllowedToSaveItemView){
			getIsAllowedToSaveItemView = 1;
		}
		
		if(!$scope.privatedata.availableItemViews){
			$scope.privatedata.availableItemViews = [];
			getAllowedItemViews = 1;
		}
		var httpCache = $cacheFactory.get('$http');
		var url = "/items.php?action=getallitems&start="+start+"&limit="+$scope.pagination.pageSize+"&orderby="+$scope.pagination.orderBy+"&getcount="+getCount+"&getheaders="+getHeaders+"&getIsAllowedToSaveItemView="+getIsAllowedToSaveItemView+"&getAllowedItemViews="+getAllowedItemViews;

		var cachedResponse = httpCache.get(url);
		if(cachedResponse && $scope.parentobj.privatedata.items && cachedResponse.data.status){
			$scope.parentobj.pageIsLoading = 0;
			$scope.parentobj.setlog('',0);
			$scope.parentobj.privatedata.items.details = cachedResponse.data.items;
			$scope.parentobj.privatedata.items.headers = cachedResponse.data.headers;
			$scope.pagination.totalItems = cachedResponse.data.totalcount;
			$scope.isAllowedToSaveItemView = cachedResponse.data.isAllowedToSaveItemView;
			$scope.privatedata.availableItemViews = cachedResponse.data.availableItemViews;
		}else if(!$scope.parentobj.privatedata.items.details || ($scope.parentobj.privatedata.items.details && ($scope.currentUrl && url != $scope.currentUrl))){
			$scope.currentUrl = url;
			var parameters = {
				action: 'getallitems',
				start: start,
				limit: $scope.pagination.pageSize,
				orderBy: $scope.pagination.orderBy,
				getCount: getCount,
				getHeaders: getHeaders,
				getIsAllowedToSaveItemView: getIsAllowedToSaveItemView,
				getAllowedItemViews: getAllowedItemViews
			};

			$http({
				method : "POST",
				cache: false,
				url : '/items.php',
				data: parameters,
				headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
			}).then(function submitSuccess(response) {
				$scope.parentobj.pageIsLoading = 0;
				if(response.data.status){
					$scope.parentobj.setlog('',0);
					$scope.parentobj.privatedata.items.details = response.data.items;
					if(response.data.headers && response.data.headers.length>0){
						$scope.parentobj.privatedata.items.headers = response.data.headers;
					}
					if(response.data.totalcount>0){
						$scope.pagination.totalItems = response.data.totalcount;
					}
					var enableGridMenu = false;
					if(response.data.isAllowedToSaveItemView){
						$scope.isAllowedToSaveItemView = response.data.isAllowedToSaveItemView;
						enableGridMenu = true;
						$scope.showSaveTableView();
					}
					
					if(response.data.availableItemViews){
						$scope.privatedata.availableItemViews = response.data.availableItemViews;
					}
					$scope.parentobj.updateGrid($scope,'itemsGridOption','itemsGridApi', enableGridMenu, getIsAllowedToSaveItemView, $scope.parentobj.privatedata.items, 'gridRowMenu', 'itemsDetail_itemId');
					$scope.changeTableView($scope.privatedata.itemSelectedView);
					
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
	}
	
	$scope.parentobj.initGrid($scope, $scope.parentobj.getItems, 'itemsGridOption', 'itemsGridApi', 'itemSelectedView', 'availableItemViews', false, $scope.parentobj.privatedata.selectedItemsArray,'itemsDetail_itemId',$scope.parentobj.afterItemSingleSelectCallbackFunction);
	
	
	if(!$scope.pagination.totalItems){
		$scope.parentobj.setlog('Getting Item List From Server',1);
		$scope.parentobj.getItems(1,1);
	}
	
	$scope.parentobj.OpenItemDetails = function(item, action="edit", template='editItem.html'){
		$scope.privatedata.record = angular.copy(item);
		selectedArray = [];
		var originalItemData = angular.copy(item);
		$mdDialog.show({
			scope: $scope,        // use parent scope in template
	   		preserveScope: true,
			controller: DialogController,
			templateUrl: template,
			parent: angular.element(document.body),
			targetEvent: item,
			clickOutsideToClose: false,
			locals: {
				originalItemData: originalItemData,
				itemScope: item,
				actionScope: action
			},
			multiple: true,
			openFrom: "#edit_"+$scope.privatedata.record['itemsDetail_itemId'],
			closeTo: "#edit_"+$scope.privatedata.record['itemsDetail_itemId']
		})
		.then(function(item) {
		
		}, function() {
	
		});
	};
	
	$scope.parentobj.getItemDetails = function(item,action="edit") {
		$scope.parentobj.pageIsLoading = 1;
		$scope.parentobj.setlog("Getting Item Details",1);
		if(action=="edit"){
			if(item['itemsDetail_itemId']){
				var itemParameters = {
					action: "getItemDetails", 
					properties: ["price","inventory"],
					itemId: item['itemsDetail_itemId']
				};
				
				$http({
					method : "POST",
					url : "/items.php",
					data: itemParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					$scope.parentobj.pageIsLoading = 0;
					if(response.data.status){
						var price = [];
						var inventory = [];
						if(response.data.price.status){
							price = response.data.price.rows;
						}else{
							$scope.parentobj.setlog(response.data.price.msg,0);
						}
						response.data.item.itemPrices = price;
						
						if(response.data.inventory){
							if(response.data.inventory.status){
								inventory = response.data.inventory.rows;
							}else{
								$scope.parentobj.setlog(response.data.inventory.msg,0);
							}
						}
						response.data.item.itemInventories = inventory;
						
						angular.extend(item,response.data.item.rows[0]);
						$scope.parentobj.setlog('',0);
						if(response.data.item.rows[0].itemsIdbrandsId_brandIdGroup){
							$scope.parentobj.privatedata.initialSelectedBrands = $scope.makeInitialSelectedBrands(response.data.item.rows[0].itemsIdbrandsId_brandIdGroup.split(','));
						}else{
							$scope.parentobj.privatedata.initialSelectedBrands = [];
						}
						$scope.parentobj.OpenItemDetails(item);
					}else{
						$scope.parentobj.setlog(response.data.msg,0);
					}
				}, function submitError(response) {
					$scope.parentobj.setlog(response,0);
					$scope.parentobj.pageIsLoading = 0;
				});
				
				
			}else{
				$scope.parentobj.setlog("Error in finding requested item",0);
				$scope.parentobj.pageIsLoading = 0;
			}
		}else{
			$scope.parentobj.setlog("Creating new Item",0);
			$scope.parentobj.pageIsLoading = 0;
			$scope.parentobj.privatedata.initialSelectedBrands = [];
			$scope.parentobj.privatedata.selectedBrandsArray = {};
			item = {
				'brands':{},
				'itemsDetail_itemId':'',
				'itemsDetail_itemCode': '',
				'itemsDetail_itemBarcode': '',
				'itemsDetail_itemExternalId': '',
				'itemsDetail_itemDescription': '',
				'itemsDetail_insertBy_userId': '',
				'itemsStatus_status': '1',
				'itemsDetail_onlineInsertTime': moment().format('YYYY-MM-DD'),
				'itemsIdbrandsId_brandIdGroup':'',
				'brandsDetail_brandNameGroup': '',
				'brandsDetail_brandCodeGroup': '',
				'itemPrices': []
			};
			$scope.parentobj.OpenItemDetails(item, action);
		}
	};
	
	function DialogController(itemScope, actionScope, $scope, $mdDialog, originalItemData, $mdToast, $http, $templateCache) {
		var recordscope;
		$scope.hide = function() {
			$mdDialog.hide();
		};

		$scope.clickToClose  = function() {
			recordscope = angular.copy($scope.privatedata.record);
			$scope.parentobj.setlog('',0);

			if(angular.equals(recordscope,originalItemData)){
				$mdDialog.cancel();
			}else{
				confirmDialogClose($mdDialog,$scope.clickToSave,$mdDialog.cancel);
			}
		};
			
		$scope.clickToSave = function() {
			if($scope.privatedata.record['itemsStatus_status'] === '0' || $scope.privatedata.record['itemsStatus_status'] === 0){
				var confirm = $mdDialog.confirm()
					.title('Confirm Delete')
					.textContent('You are disabling this item and it will not be shown in the list. Are you sure?')
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
				$scope.saveItem();
			}
		};
		
		$scope.saveItem = function(){
			recordscope = angular.copy($scope.privatedata.record);
			if(angular.equals(recordscope,originalItemData) && !Object.keys($scope.parentobj.privatedata.selectedBrandsArray).length){
				showMainformToast('noChangesMade.html', $mdToast);
				$mdDialog.hide();
			}else{
				recordscope.brands = $scope.parentobj.privatedata.selectedBrandsArray;
				$scope.editItem($mdDialog, $http, $mdToast, $templateCache)
			}
		}
		$scope.editItem = function($mdDialog, $http, $mdToast, $templateCache) {
			$scope.parentobj.setlog("Sending data to server",1);
			$scope.formDisabled = true;
			$scope.responseErrText = '';
			$scope.itemDetails.$valid = true;
			var options = {};
			var thirdpartyParameters = {};
			var changeArray = {};
			var thirdpartyChange = {};

			if(recordscope['itemsDetail_itemCode'] != originalItemData['itemsDetail_itemCode'] || recordscope['itemsDetail_itemBarcode'] != originalItemData['itemsDetail_itemBarcode'] || recordscope['itemsDetail_itemExternalId'] != originalItemData['itemsDetail_itemExternalId'] || recordscope['itemsDetail_itemName'] != originalItemData['itemsDetail_itemName'] || recordscope['itemsDetail_itemDescription'] != originalItemData['itemsDetail_itemDescription']){
				changeArray.changeDetails = 1;
			}else{
				delete recordscope['itemsDetail_itemCode'];
				delete recordscope['itemsDetail_itemBarcode'];
				delete recordscope['itemsDetail_itemExternalId'];
				delete recordscope['itemsDetail_itemName'];
				delete recordscope['itemsDetail_itemDescription'];
			}
			if(recordscope['itemsStatus_status'] != originalItemData['itemsStatus_status'] || actionScope ==="create"){
				changeArray.changeStatus = 1;
			}else{
				delete recordscope['itemsStatus_status'];
			}
			
			if(recordscope.brands){
				if(Object.keys(recordscope.brands).length){
					changeArray.changeBrands = 1;
				}
			}

			if($scope.itemPrices.modified){
				changeArray.changePrices = 1;
			}
			
			if($scope.itemInventories.modified){
				thirdpartyChange.changeInventory = 1;
				thirdpartyParameters.shot = recordscope.itemInventories;
			}
			
			if(recordscope[0]){
				delete recordscope[0];
			}

			delete itemsDetail_insertBy_userId;

			recordscope.action = actionScope;
			var itemParameters = {
				action: "saveDetails", 
				options: recordscope,
				changeArray: changeArray,
				thirdpartyChange: thirdpartyChange,
				thirdpartyParameters: thirdpartyParameters
			};

			if($scope.itemDetails.$valid){
				$http({
					method : "POST",
					url : "/items.php",
					data: itemParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
				console.log(response.data);
					if(response.data.status){
						var i = 0;
						if(actionScope ==="create"){
							if(response.data['addItem'].status){
								itemScope['itemsDetail_itemId'] = response.data['addItem'].itemsId_itemId;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data['addItem'].msg;
							}
						}
						
						angular.forEach(changeArray, function(value, key) {
							var errMsg = '';
							if(response.data[key]){
								if(response.data[key].status && value){
									i = i + 1;
									if(key==='changeDetails'){
										itemScope['itemsDetail_itemDescription'] = recordscope['itemsDetail_itemDescription'];
										itemScope['itemsDetail_itemBarcode'] = recordscope['itemsDetail_itemBarcode'];
										itemScope['itemsDetail_itemCode'] = recordscope['itemsDetail_itemCode'];
										itemScope['itemsDetail_itemId'] = recordscope['itemsDetail_itemId'];
										itemScope['itemsDetail_itemName'] = recordscope['itemsDetail_itemName'];
									}else if(key === 'changeStatus'){
										itemScope['itemsStatus_status'] = recordscope['itemsStatus_status'];
									}else if(key === 'changeBrands'){
										itemScope['brandsDetail_brandCodeGroup'] = '';
										itemScope['brandsDetail_brandNameGroup'] = '';
										itemScope['itemsIdbrandsId_brandIdGroup'] = '';
									}else if(key === 'changePrices'){
										itemScope['itemPrices'] = recordscope['itemPrices'];
									}else if(key === 'changeInventory'){
										itemScope['itemInventories'] = recordscope['itemInventories'];
									}
									$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
								}else{
									$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
								}
							}else{
								console.log(response);
							}
						});
						
						if(actionScope ==="create" && response.data['addItem'].status){
							if($scope.parentobj.privatedata.items.details){
								$scope.parentobj.privatedata.items.details.rows.push(itemScope);
							}
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
								templateUrl : 'itemChangeOK.html'
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
	
	$scope.addCurrency = function(itemPriceArray){
		itemPriceArray.push({itemsPrice_currencyId:'',itemsPrice_price:'',itemsPrice_validFrom:'',itemsPrice_status:'1'});
	}
	$scope.addInventory = function(itemInventoryArray){
		itemInventoryArray.push({inventoryShotDetail_storeId:'',inventoryShotDetail_quantityOnHand:'',inventoryShotHeader_markdownId:''});
	}
});