app.controller('parentobj.controllers.receipts',function($scope, $filter, $window, $http, $cacheFactory, $state, $mdDialog, i18nService, uiGridConstants, $templateCache, guid) {
	$scope.privatedata = {};
	if(!$scope.parentobj.selectedReceiptsArray){
		$scope.parentobj.selectedReceiptsArray = {};
	}
	if(!$scope.parentobj.privatedata.initialSelectedReceipt){
		$scope.parentobj.privatedata.initialSelectedReceipt = [];
	}
	$scope.saveNewState = 0;
	
	//Change the field name to field id
	$scope.tableProperties = {showFieldNames : 1};
	$scope.$watch('tableProperties.showFieldNames', function() {
		if($scope.parentobj.privatedata.receipts.headers){
			angular.forEach($scope.parentobj.privatedata.receipts.headers, function(value, key){
		        	if($scope.tableProperties.showFieldNames){
		        		$scope.parentobj.privatedata.receipts.headers[key]['displayName'] = value['fieldName'];
		        	}else{
		        		$scope.parentobj.privatedata.receipts.headers[key]['displayName'] = value['fieldId'];
		        	}
        		});
        		$scope.receiptsGridOption.columnDefs = $scope.parentobj.privatedata.receipts.headers;
        		$scope.receiptsGridApi.core.notifyDataChange(uiGridConstants.dataChange.COLUMN);
        	}
	},true);
	
	if(!$scope.parentobj.privatedata.receipts){
		$scope.parentobj.privatedata.receipts = {};
	}
	
	$scope.changeTableView = function(view){
		if(view && view.gridState){
			$scope.receiptsGridApi.saveState.restore( $scope, view.gridState );
		}
		if(!$scope.receiptsGridOption.paginationPageSize){
			$scope.receiptsGridOption.paginationPageSize = $scope.pagination.pageSize;
		}
	}
	$scope.showSaveTableView = function(){
		if($scope.isAllowedToSaveReceiptsView){
			$scope.saveNewState = 1;
		}
	}
	
	$scope.parentobj.getReceipts = function(getCount=1,getHeaders=0) {
		var start = ($scope.pagination.currentPage - 1) * $scope.pagination.pageSize;
		var getIsAllowedToSaveReceiptsView = 0;
		var getAllowedReceiptsViews = 0;
		
		if(!$scope.parentobj.privatedata.receipts.headers){
			getHeaders = 1;
		}
		
		if(!$scope.isAllowedToSaveReceiptsView){
			getIsAllowedToSaveReceiptsView = 1;
		}
		
		if(!$scope.privatedata.availableReceiptsViews){
			$scope.privatedata.availableReceiptsViews = [];
			getAllowedReceiptsViews = 1;
		}
		var httpCache = $cacheFactory.get('$http');
		let params = {
			action: 'searchReceiptTemplates',
			start: start,
			limit: $scope.pagination.pageSize,
			orderby: $scope.pagination.orderBy,
			status: 1,
			getcount: getCount,
			getheaders: getHeaders,
			getAllowedReceiptsViews: getAllowedReceiptsViews,
			getAllowedReceiptsViews: getAllowedReceiptsViews
		};
		var url = "/receipts.php?action=searchReceiptTemplates&start="+start+"&limit="+$scope.pagination.pageSize+"&orderby="+$scope.pagination.orderBy+"&getcount="+getCount+"&getheaders="+getHeaders+"&getAllowedReceiptsViews="+getAllowedReceiptsViews+"&getAllowedReceiptsViews="+getAllowedReceiptsViews;

		var cachedResponse = httpCache.get(url);
		if(cachedResponse && $scope.parentobj.privatedata.receipts && cachedResponse.data.status){
		
		}else if(!$scope.parentobj.privatedata.receipts.details || ($scope.parentobj.privatedata.receipts.details && ($scope.currentUrl && url != $scope.currentUrl))){
			$scope.currentUrl = url;
			$http({
				method : "POST",
				cache: false,
				data: params,
				url : "/receipts.php",
				headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
			}).then(function submitSuccess(response) {
				$scope.parentobj.pageIsLoading = 0;
				if(response.data.status){
					$scope.parentobj.setlog('',0);
					$scope.parentobj.privatedata.receipts.details = response.data.receipts;
					if(response.data.headers && response.data.headers.length>0){
						$scope.parentobj.privatedata.receipts.headers = response.data.headers;
					}
					if(response.data.totalcount>0){
						$scope.pagination.totalReceipts = response.data.totalcount;
					}
					var enableGridMenu = false;
					if(response.data.isAllowedToSaveReceiptsView){
						$scope.isAllowedToSaveReceiptsView = response.data.isAllowedToSaveReceiptsView;
						enableGridMenu = true;
						$scope.showSaveTableView();
					}
					
					if(response.data.availableReceiptsViews){
						$scope.privatedata.availableReceiptsViews = response.data.availableReceiptsViews;
					}
					$scope.parentobj.updateGrid($scope,'receiptsGridOption','receiptsGridApi', enableGridMenu, getIsAllowedToSaveReceiptsView, $scope.parentobj.privatedata.receipts, 'gridRowMenu', 'receiptsDetail_receiptId');
					$scope.changeTableView($scope.privatedata.receiptsSelectedView);
					
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
	
	$scope.parentobj.initGrid($scope, $scope.parentobj.getReceipts, 'receiptsGridOption', 'receiptsGridApi', 'receiptsSelectedView', 'availableReceiptsViews', false, $scope.parentobj.privatedata.selectedReceiptsArray,'receiptsDetail_receiptId');
	
	
	if(!$scope.pagination.totalItems){
		$scope.parentobj.setlog('Getting Receipt List From Server',1);
		$scope.parentobj.getReceipts(1,1);
	}
	
	$scope.parentobj.getAvailableModules = function(){
		$scope.parentobj.setlog('Getting available Modules...',1);
		$http({
			method : "GET",
			url : "/receipts.php?action=getavailablemodules",
			headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
		}).then(function submitSuccess(response) {
			$scope.parentobj.pageIsLoading = 0;
			if(response.data.status){
				$scope.parentobj.availableModules = response.data.rows;
				$scope.parentobj.setlog('',0);
			}else{
				$scope.parentobj.setlog(response.data.msg,0);
			}
		}, function submitError(response) {
			$scope.parentobj.setlog(response,0);
			$scope.parentobj.pageIsLoading = 0;
		});
	}
	if(!$scope.parentobj.availableModules){
		$scope.parentobj.getAvailableModules();
	}
	
	$scope.parentobj.getReceiptDetails = function(receipt, action='edit'){
		$scope.parentobj.pageIsLoading = 1;
		$scope.parentobj.setlog("Getting Receipt Details",1);
		if(action=="edit"){
			if(receipt['receiptsDetail_receiptId']){
				var receiptParameters = {
					action: "getReceiptsDetails", 
					properties: ["receiptsDetail_receiptData"],
					receiptId: receipt['receiptsDetail_receiptId']
				};
				
				$http({
					method : "POST",
					url : "/receipts.php",
					data: receiptParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					$scope.parentobj.pageIsLoading = 0;
					if(response.data.status){						
						angular.extend(receipt,response.data.receipts.rows[0]);
						$scope.parentobj.setlog('',0);
						$scope.parentobj.OpenReceiptDetails(receipt);
					}else{
						$scope.parentobj.setlog(response.data.msg,0);
					}
				});
			}else{
				$scope.parentobj.setlog("Error in finding requested receipt",0);
				$scope.parentobj.pageIsLoading = 0;
			}
		}else{
			$scope.parentobj.setlog("Creating new Receipt",0);
			$scope.parentobj.pageIsLoading = 0;
			receipt = {
				'receiptsDetail_receiptId':'',
				'receiptsDetail_receiptName': '',
				'receiptsStatus_status': '1',
				'receiptsDetail_receiptData': {
					options: {
						gridType: 'verticalFixed', //'scrollVertical'
						margin: 0,
						compactType: 'none',
						minCols: 5,
						maxCols: 30,
						minRows: 5,
						maxRows: 30000,
						outerMargin: false,
						fixedRowHeight: 10,
						defaultItemRows: 3,
						scrollSensitivity: 10,
						mobileBreakpoint: 10,
						draggable: {
							delayStart: 0, // milliseconds to delay the start of resize, useful for touch interaction
							enabled: true, // enable/disable draggable items
							ignoreContentClass: 'gridster-item-content',
						},
						resizable: {
							delayStart: 0, // milliseconds to delay the start of resize, useful for touch interaction
							enabled: true, // enable/disable resizable items
						},
						swap: true, // allow items to switch position if drop on top of another
						pushItems: true, // push items when resizing and dragging
						disablePushOnDrag: false, // disable push on drag
						disablePushOnResize: false, // disable push on resize
						pushResizeItems: false, // on resize of item will shrink adjacent items
						disableWindowResize: true // disable the window on resize listener. This will stop grid to recalculate on window resize.
					},
					screens: [
						{
							screenId: guid.newuuid('receiptsDetails_screenId'),
							screenName: 'main',
							items:[]
						}
					]
				}
			};
			$scope.parentobj.OpenReceiptDetails(receipt, action);
		}
	};
	
	$scope.parentobj.OpenReceiptDetails = function(receipt, action="edit", template='editReceipt.html'){
		$scope.privatedata.record = angular.copy(receipt);
		var originalReceiptData = angular.copy(receipt);
		$mdDialog.show({
			scope: $scope,
	   		preserveScope: true,
			controller: DialogController,
			templateUrl: template,
			parent: angular.element(document.body),
			clickOutsideToClose: false,
			locals: {
				originalReceiptData: originalReceiptData,
				receipt: receipt,
				actionScope: action
			},
			multiple: true,
			openFrom: "#edit_"+$scope.privatedata.record['receiptsDetail_receiptId'],
			closeTo: "#edit_"+$scope.privatedata.record['receiptsDetail_receiptId']
		})
		.then(function() {
		
		}, function() {
	
		});
	};
	
	function DialogController(receipt, actionScope, $scope, $mdDialog, originalReceiptData, $mdToast, $http, $templateCache) {
		var recordscope;
		$scope.parentobj.openReceiptVisualEditor = function(data){

			var originalData = angular.copy(data);
			$mdDialog.show({
		   		preserveScope: false,
				controller: VisualEditorDialogController,
				templateUrl: 'receiptVisualEditor.html',
				parent: angular.element(document.body),
				clickOutsideToClose: false,
				locals: {
					data: data,
					receipt: receipt,
					originalData: originalData,
					availableModules: $scope.parentobj.availableModules,
					makeModule: $scope.parentobj.makeModule
				},
				multiple: true,
				openFrom: "#openVisualEditor",
				closeTo: "#openVisualEditor"
			})
			.then(function(data) {
			
			}, function() {
		
			});
		};
		$scope.clickToSave = function(receipt){
			if($scope.privatedata.record['receiptsStatus_status'] === '0' || $scope.privatedata.record['receiptsStatus_status'] === 0){
				var confirm = $mdDialog.confirm()
					.title('Confirm Delete')
					.textContent('You are disabling this receipt and it will not be shown in the list. Are you sure?')
					.ariaLabel('confirm delete')
					.targetEvent()
					.multiple(true)
					.ok('Yes, disable it!')
					.cancel('No! Do NOT disable!');

				$mdDialog.show(confirm).then(function() {
					$scope.saveReceipt();
				}, function() {
					showMainformToast('noChangesMade.html', $mdToast);
					$mdDialog.hide();
				});
			}else{
				$scope.saveReceipt();
			}
		}
		$scope.clickToClose = function(receipt){
			recordscope = angular.copy($scope.privatedata.record);
			$scope.parentobj.setlog('',0);

			if(angular.equals(recordscope,originalReceiptData)){
				$mdDialog.cancel();
			}else{
				confirmDialogClose($mdDialog,$scope.clickToSave,$mdDialog.cancel, $scope);
			}
		}
		
		$scope.saveReceipt = function(){
			recordscope = angular.copy($scope.privatedata.record);
			if(angular.equals(recordscope,originalReceiptData)){
				showMainformToast('noChangesMade.html', $mdToast);
				$mdDialog.hide();
			}else{
				$scope.editReceipt($mdDialog, $http, $mdToast, $templateCache)
			}
		}
		$scope.editReceipt = function($mdDialog, $http, $mdToast, $templateCache) {
			$scope.parentobj.setlog("Sending data to server",1);
			$scope.formDisabled = true;
			$scope.responseErrText = '';
			$scope.receiptDetails.$valid = true;
			var options = [];
			var changeArray = {};

			if($scope.receiptStatus.modified || actionScope ==="create"){
				changeArray.changeStatus = 1;
			}else{
				delete recordscope['receiptsStatus_status'];
			}
			if($scope.receiptDetails.modified || recordscope['receiptsDetail_receiptData']!=originalReceiptData['receiptDetail_receiptData']){
				changeArray.changeDetails = 1;
			}else{
				delete recordscope['receiptsDetail_receiptData'];
				delete recordscope['receiptsDetail_receiptName'];
			}
			
			recordscope.action = actionScope;
			var receiptParameters = {
				action: "saveDetails", 
				options: recordscope,
				changeArray: changeArray
			};

			if($scope.receiptDetails.$valid){
				$http({
					method : "POST",
					url : "/receipts.php",
					data: receiptParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					console.log(response);
					if(response.data.status){
						var i = 0;
						if(actionScope ==="create"){
							if(response.data['addReceipt'].status){
								receipt['receiptsDetail_receiptId'] = response.data['addReceipt'].receiptsId_receiptId;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data['addReceipt'].msg;
							}
						}
						angular.forEach(changeArray, function(value, key) {
							if(response.data[key].status && value){
								i = i + 1;
								if(key==='changeDetails'){
									receipt['receiptsDetail_receiptName'] = recordscope['receiptsDetail_receiptName'];
									receipt['receiptsDetail_receiptData'] = recordscope['receiptsDetail_receiptData'];
								}else if(key === 'changeStatus'){
									receipt['receiptsStatus_status'] = recordscope['receiptsStatus_status'];
								}
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}
						});
						if(actionScope ==="create" && response.data['addReceipt'].status){
							if($scope.parentobj.privatedata.receipts){
								if($scope.parentobj.privatedata.receipts.details){
									if($scope.parentobj.privatedata.receipts.details.rows){
										$scope.parentobj.privatedata.receipts.details.rows.push(receipt);
									}else{
										$scope.parentobj.privatedata.receipts.details.rows = {};
										$scope.parentobj.privatedata.receipts.details.rows.push(receipt);
									}
								}
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
								templateUrl : 'receiptChangeOK.html'
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
	
	function VisualEditorDialogController(receipt, data, originalData, availableModules, makeModule, $scope, $mdDialog, $mdToast, $http, $templateCache) {
		$scope.data = data;
		$scope.selectedScreen = $filter('filter')(data.screens, {screenName: 'main'})[0];
		$scope.openGridsterOptions = function(options){
			$mdDialog.show({
		   		preserveScope: false,
				controller: GridsterOptionsDialogController,
				templateUrl: 'GridsterOptions.html',
				parent: angular.element(document.body),
				targetEvent: options,
				clickOutsideToClose: false,
				fullscreen: true,
				locals: {
					options: options,
					data: data
				},
				multiple: true,
				openFrom: "#openGridsterOptions",
				closeTo: "#openGridsterOptions"
			})
			.then(function(data) {
			
			}, function() {
		
			});
		};
		$scope.addNewScreen = function(){
			$mdDialog.show({
		   		preserveScope: false,
				controller: addNewScreenDialogController,
				templateUrl: 'addNewScreen.html',
				parent: angular.element(document.body),
				targetEvent: data,
				clickOutsideToClose: false,
				fullscreen: false,
				locals: {
					data: data
				},
				multiple: true,
				openFrom: "#addNewScreen",
				closeTo: "#addNewScreen"
			})
			.then(function(data) {
			
			}, function() {
		
			});
		}
		
		$scope.openElementSelection = function(){
			$mdDialog.show({
		   		preserveScope: false,
				controller: GridsterElementsDialogController,
				templateUrl: 'GridsterElements.html',
				parent: angular.element(document.body),
				targetEvent: data,
				clickOutsideToClose: false,
				fullscreen: true,
				locals: {
					data: data,
					availableModules: availableModules,
					makeModule: makeModule,
					selectedScreen: $scope.selectedScreen
				},
				multiple: true,
				openFrom: "#openElementSelection",
				closeTo: "#openElementSelection"
			})
			.then(function(data) {
			
			}, function() {
		
			});
		}
		
		$scope.clickToSave = function(data){
			receipt['receiptsDetail_receiptData'] = data;
			$mdDialog.hide();
		}
		$scope.clickToClose = function(data){
			$mdDialog.cancel();
		}
	}
	function addNewScreenDialogController(data, $scope,  $mdDialog, guid){
		$scope.clickToClose = function(){
			$mdDialog.cancel();
		}
		$scope.clickToSave = function(){
			let newScreenId = guid.newuuid('receiptsDetails_screenId');
			data.screens.push({screenName: $scope.screenName, screenId: newScreenId, items:[]});
			console.log({screenName: $scope.screenName, screenId: newScreenId, items:[]});
			$mdDialog.hide();
		}
	}
	function GridsterElementsDialogController(data, availableModules, makeModule, selectedScreen, $scope, $mdDialog, $mdToast, $http){
		$scope.makeModule = makeModule;
		$scope.availableModules = availableModules;
		$scope.clickToClose = function(){
			$mdDialog.cancel();
		}
		$scope.addModule = function(item){
			selectedScreenIndex = data.screens.findIndex(screen => screen.screenId == selectedScreen.screenId);
			if(!data.screens[selectedScreenIndex].items){
				data.screens[selectedScreen.screenId].items = [];
			}
			let pushedItem = {};
			if(item.condition != ''){
				pushedItem.condition = item.condition;
			}else{
				pushedItem.condition = true;
			}
			pushedItem.element = item.element;
			data.screens[selectedScreenIndex].items.push(pushedItem);
			$mdDialog.hide();
		}
		
	}
	function GridsterOptionsDialogController(options, data, $scope, $mdDialog, $mdToast, $http){
		
		$scope.options = options;
		if(!$scope.options.margins){
			$scope.options.margins = [];
		}
		$scope.clickToSave = function(options){
			data.options = options;
			$mdDialog.hide();
		}
		$scope.clickToClose = function(options){
			$mdDialog.cancel();
		}
	}
	
	$scope.parentobj.makeModule = function(type, parameters, callback){
		let output = '';
		let item = {};
		switch (type) {
			case 'fixed':
				var confirm = $mdDialog.confirm({
					multiple: true,
					controller: GeneralDialogController,
					templateUrl: 'modules_fixed.html',
					parent: angular.element(document.body)
				});

				$mdDialog.show(confirm).then(function(scope) {
					if(!scope.text){scope.text='';}
					if(!scope.class){scope.class='';}
					output = "<div class='"+scope.class+"'>"+scope.text+"</div>";

					item.element = output;
					if(scope.hasOwnProperty('condition')){
						item.condition = scope.condition;
					}else{
						item.condition = '';
					}
					callback(item);
					
				}, function() {
					
				});
			break;
			case 'table':
				switch (parameters) {
					case 'sales':
						output = "<module-items-table params='{}'></module-items-table>";
					break;
					case 'payments':
						output = "<module-items-payment-table params='{}'></module-items-payment-table>";
					break;
				}
				item.element = output;
				if(scope.hasOwnProperty('condition')){
					item.condition = scope.condition;
				}else{
					item.condition = '';
				}
				callback(item);
			break;
			case 'global':
				var confirm = $mdDialog.confirm({
					multiple: true,
					controller: GeneralDialogController,
					templateUrl: 'modules_time.html',
					parent: angular.element(document.body)
				});

				$mdDialog.show(confirm).then(function(scope) {
					if(parameters=="time"){
						if(!scope.text){scope.text='';}
						if(!scope.class){scope.class='';}
						if(!scope.format){scope.format='yyyy/MM/dd H:mm:ss';}
						output = "<div class='"+scope.class+"'>"+scope.text+"<digital-clock format='"+scope.format+"'></digital-clock></div>";
					}
					item.element = output;
					if(scope.hasOwnProperty('condition')){
						item.condition = scope.condition;
					}else{
						item.condition = '';
					}
					callback(item);
					
				}, function() {
					
				});
			break;
		}
	}
	function GeneralDialogController($scope, $mdDialog) {
		$scope.hide = function() {
			$mdDialog.hide($scope);
		};

		$scope.cancel = function() {
			$mdDialog.cancel();
		};
	}
});