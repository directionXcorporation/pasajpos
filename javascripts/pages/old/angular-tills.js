app.controller('parentobj.controllers.tills',function($scope, $filter, $window, $http, $cacheFactory, $state, $mdDialog, i18nService, uiGridConstants, $sce) {
//Object.defineProperty($scope, 'myVariable', { value: 'childControllerValue', writable: true });
	$scope.privatedata = {};
	if(!$scope.parentobj.selectedTillsArray){
		$scope.parentobj.selectedTillsArray = {};
	}
	$scope.saveNewState = 0;

	//Change the field name to field id
	$scope.tableProperties = {showFieldNames : 1};
	$scope.$watch('tableProperties.showFieldNames', function() {
		if($scope.parentobj.privatedata.tills.headers){
			angular.forEach($scope.parentobj.privatedata.tills.headers, function(value, key){
		        	if($scope.tableProperties.showFieldNames){
		        		$scope.parentobj.privatedata.tills.headers[key]['displayName'] = value['fieldName'];
		        	}else{
		        		$scope.parentobj.privatedata.tills.headers[key]['displayName'] = value['fieldId'];
		        	}
        		});
        		$scope.tillsGridOption.columnDefs = $scope.parentobj.privatedata.tills.headers;
        		$scope.tillsGridApi.core.notifyDataChange(uiGridConstants.dataChange.COLUMN);
        	}
	},true);
	
	if(!$scope.parentobj.privatedata.tills){
		$scope.parentobj.privatedata.tills = {};
	}
	
	/*$scope.loadStores = function() {
		$scope.storePage = "Loading...";
		$http.get('/stores.php').then(function(response) {
			$scope.storePage = $sce.trustAsHtml(response.data);
		});
	}*/
	
	$scope.changeTableView = function(view){
		if(view && view.gridState){
			$scope.tillsGridApi.saveState.restore( $scope, view.gridState );
		}
		if(!$scope.tillsGridOption.paginationPageSize){
			$scope.tillsGridOption.paginationPageSize = $scope.pagination.pageSize;
		}
	}
	$scope.showSaveTableView = function(){
		if($scope.isAllowedToSaveTillView){
			$scope.saveNewState = 1;
		}
	}
	$scope.parentobj.getTills = function(getCount=1,getHeaders=0) {
		var start = ($scope.pagination.currentPage - 1) * $scope.pagination.pageSize;
		var getIsAllowedToSaveTillView = 0;
		var getAllowedTillViews = 0;

		if(!$scope.parentobj.privatedata.tills.headers){
			getHeaders = 1;
		}
		
		if(!$scope.isAllowedToSaveTillView){
			getIsAllowedToSaveTillView = 1;
		}
		
		if(!$scope.privatedata.availableTillViews){
			$scope.privatedata.availableTillViews = [];
			getAllowedTillViews = 1;
		}
		var httpCache = $cacheFactory.get('$http');
		var url = "/tills.php?action=getalltills&start="+start+"&limit="+$scope.pagination.pageSize+"&orderby="+$scope.pagination.orderBy+"&getcount="+getCount+"&getheaders="+getHeaders+"&getIsAllowedToSaveTillView="+getIsAllowedToSaveTillView+"&getAllowedTillViews="+getAllowedTillViews;

		var cachedResponse = httpCache.get(url);
		if(cachedResponse && $scope.parentobj.privatedata.tills && cachedResponse.data.status){
			$scope.parentobj.pageIsLoading = 0;
			$scope.parentobj.setlog('',0);
			$scope.parentobj.privatedata.tills.details = cachedResponse.data.tills;
			$scope.parentobj.privatedata.tills.headers = cachedResponse.data.headers;
			$scope.pagination.totalItems = cachedResponse.data.totalcount;
			$scope.isAllowedToSaveTillView = cachedResponse.data.isAllowedToSaveTillView;
			$scope.privatedata.availableTillViews = cachedResponse.data.availableTillViews;
		}else if(!$scope.parentobj.privatedata.tills.details || ($scope.parentobj.privatedata.tills.details && ($scope.currentUrl && url != $scope.currentUrl))){
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
					$scope.parentobj.privatedata.tills.details = response.data.tills;
					if(response.data.headers && response.data.headers.length>0){
						$scope.parentobj.privatedata.tills.headers = response.data.headers;
					}
					if(response.data.totalcount>0){
						$scope.pagination.totalItems = response.data.totalcount;
					}
					var enableGridMenu = false;
					if(response.data.isAllowedToSaveTillView){
						$scope.isAllowedToSaveTillView = response.data.isAllowedToSaveTillView;
						enableGridMenu = true;
						$scope.showSaveTableView();
					}
					
					if(response.data.availableTillViews){
						$scope.privatedata.availableTillViews = response.data.availableTillViews;
					}
					$scope.parentobj.updateGrid($scope,'tillsGridOption','tillsGridApi', enableGridMenu, getIsAllowedToSaveTillView, $scope.parentobj.privatedata.tills, 'gridRowMenu', 'tillsDetail_tillId');
					$scope.changeTableView($scope.privatedata.tillSelectedView);
					
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
	
	$scope.parentobj.initGrid($scope, $scope.parentobj.getTills, 'tillsGridOption', 'tillsGridApi', 'tillSelectedView', 'availableTillViews', false, $scope.parentobj.privatedata.selectedTillsArray,'tillsDetail_tillId');
	
	
	if(!$scope.pagination.totalItems){
		$scope.parentobj.setlog('Getting Till List From Server',1);
		$scope.parentobj.getTills(1,1);
	}
	
	$scope.parentobj.OpenTillDetails = function(till, action="edit", template='editTill.html'){
		$scope.privatedata.record = angular.copy(till);
		selectedArray = [];
		var originalTillData = angular.copy(till);
		$mdDialog.show({
			scope: $scope,        // use parent scope in template
	   		preserveScope: true,
			controller: DialogController,
			templateUrl: template,
			parent: angular.element(document.body),
			targetEvent: till,
			clickOutsideToClose: false,
			locals: {
				originalTillData: originalTillData,
				tillScope: till,
				actionScope: action
			},
			multiple: true,
			openFrom: "#edit_"+$scope.privatedata.record['tillsDetail_tillId'],
			closeTo: "#edit_"+$scope.privatedata.record['tillsDetail_tillId']
		})
		.then(function(till) {
		
		}, function() {
	
		});
	};
	
	$scope.parentobj.getTillDetails = function(till,action="edit") {
		$scope.parentobj.pageIsLoading = 1;
		$scope.parentobj.setlog("Getting Till Details",1);
		if(action=="edit"){
			if(till['tillsDetail_tillId']){
				var tillParameters = {
					action: "getTillDetails", 
					tillId: till['tillsDetail_tillId']
				};
				
				$http({
					method : "POST",
					url : "/tills.php",
					data: tillParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					$scope.parentobj.pageIsLoading = 0;
					if(response.data.status){
						angular.extend(till,response.data.till);
						$scope.parentobj.setlog('',0);
						$scope.parentobj.privatedata.initialSelectedStores = [response.data.till.rows[0].tillsDetail_storeId];
						$scope.parentobj.OpenTillDetails(till);
					}else{
						$scope.parentobj.setlog(response.data.msg,0);
					}
				}, function submitError(response) {
					$scope.parentobj.setlog(response,0);
					$scope.parentobj.pageIsLoading = 0;
				});
				
				
			}else{
				$scope.parentobj.setlog("Error in finding requested till",0);
				$scope.parentobj.pageIsLoading = 0;
			}
		}else{
			$scope.parentobj.setlog("Creating new Till",0);
			$scope.parentobj.pageIsLoading = 0;
			till = {
				'tillsDetail_tillId':'',
				'tillsDetail_tillCode': '',
				'tillsDetail_tillNumber': '',
				'tillsDetail_insertBy_userId': '',
				'tillsStatus_status': '1',
				'tillsDetail_onlineInsertTime': moment().format('YYYY-MM-DD'),
				'tillsDetail_storeId':'',
				'storesDetail_storeCode': '',
				'storesDetail_storeName': ''
			};
			$scope.parentobj.OpenTillDetails(till, action);
		}
	};
	
	function DialogController(tillScope, actionScope, $scope, $mdDialog, originalTillData, $mdToast, $http, $templateCache) {
		var recordscope;
		$scope.hide = function() {
			$mdDialog.hide();
		};

		$scope.clickToClose  = function() {
			recordscope = angular.copy($scope.privatedata.record);
			$scope.parentobj.setlog('',0);

			if(angular.equals(recordscope,originalTillData)){
				$mdDialog.cancel();
			}else{
				confirmDialogClose($mdDialog,$scope.clickToSave,$mdDialog.cancel);
			}
		};
			
		$scope.clickToSave = function() {
			for (key in $scope.parentobj.privatedata.selectedStoresArray){
				if ($scope.parentobj.privatedata.selectedStoresArray.hasOwnProperty(key)){			
					if($scope.parentobj.privatedata.selectedStoresArray[key].status=='1'){
						$scope.privatedata.record['tillsDetail_storeId'] = $scope.parentobj.privatedata.selectedStoresArray[key]['storesDetail_storeId'];
					}
				}
			}

			if($scope.privatedata.record['tillsStatus_status'] === '0' || $scope.privatedata.record['tillsStatus_status'] === 0){
				var confirm = $mdDialog.confirm()
					.title('Confirm Delete')
					.textContent('You are disabling this till and it will not be shown in the list. Are you sure?')
					.ariaLabel('confirm delete')
					.targetEvent()
					.multiple(true)
					.ok('Yes, disable it!')
					.cancel('No! Do NOT disable!');

				$mdDialog.show(confirm).then(function() {
					$scope.saveTill();
				}, function() {
					showMainformToast('noChangesMade.html', $mdToast);
					$mdDialog.hide();
				});
			}else{
				$scope.saveTill();
			}
		};
		
		$scope.saveTill = function(){
			recordscope = angular.copy($scope.privatedata.record);
			if(angular.equals(recordscope,originalTillData)){
				showMainformToast('noChangesMade.html', $mdToast);
				$mdDialog.hide();
			}else{
				$scope.editTill($mdDialog, $http, $mdToast, $templateCache)
			}
		}
		$scope.editTill = function($mdDialog, $http, $mdToast, $templateCache) {
			$scope.parentobj.setlog("Sending data to server",1);
			$scope.formDisabled = true;
			$scope.responseErrText = '';
			$scope.tillDetails.$valid = true;
			var options = [];
			var changeArray = {};
			if($scope.tillDetails.modified || recordscope['tillsDetail_storeId'] != originalTillData['tillsDetail_storeId']){
				if(recordscope['tillsDetail_tillCode'] != originalTillData['tillsDetail_tillCode'] || recordscope['tillsDetail_tillNumber'] != originalTillData['tillsDetail_tillNumber'] || recordscope['tillsDetail_storeId'] != originalTillData['tillsDetail_storeId']){
					changeArray.changeDetails = 1;
				}else{
					delete recordscope['tillsDetail_tillCode'];
					delete recordscope['tillsDetail_tillNumber'];
					delete recordscope['tillsDetail_storeId'];
				}
				if(recordscope['tillsStatus_status'] != originalTillData['tillsStatus_status'] || actionScope ==="create"){
					changeArray.changeStatus = 1;
				}
			}else{
				delete recordscope['tillsDetail_tillCode'];
				delete recordscope['tillsDetail_tillNumber'];
				delete recordscope['tillsDetail_storeId'];
				delete recordscope['tillsStatus_status'];
			}
			
			if(recordscope[0]){
				delete recordscope[0];
			}
			delete recordscope.storesDetail_storeCode;
			delete recordscope.storesDetail_storeName;
			delete tillsDetail_insertBy_userId;

			recordscope.action = actionScope;
			var tillParameters = {
				action: "saveDetails", 
				options: recordscope,
				changeArray: changeArray
			};
			if($scope.tillDetails.$valid){
				$http({
					method : "POST",
					url : "/tills.php",
					data: tillParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					if(response.data.status){
						var i = 0;
						if(actionScope ==="create"){
							if(response.data['addTill'].status){
								tillScope['tillsDetail_tillId'] = response.data['addTill'].tillsId_tillId;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data['addTill'].msg;
							}
						}
						
						angular.forEach(changeArray, function(value, key) {
							if(response.data[key].status && value){
								i = i + 1;
								if(key==='changeDetails'){
									tillScope['tillsDetail_tillNumber'] = recordscope['tillsDetail_tillNumber'];
									tillScope['tillsDetail_tillCode'] = recordscope['tillsDetail_tillCode'];
									tillScope['tillsDetail_storeId'] = recordscope['tillsDetail_storeId'];
								}else if(key === 'changeStatus'){
									tillScope['tillsStatus_status'] = recordscope['tillsStatus_status'];
								}
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}
						});
						if(actionScope ==="create" && response.data['addTill'].status){
							if($scope.parentobj.privatedata.tills.details){
								$scope.parentobj.privatedata.tills.details.rows.push(tillScope);
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
								templateUrl : 'tillChangeOK.html'
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