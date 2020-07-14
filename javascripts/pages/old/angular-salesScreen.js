app.controller('parentobj.controllers.salesScreen',function($scope, $filter, $window, $http, $cacheFactory, $state, $mdDialog, i18nService, uiGridConstants, $templateCache, guid) {
	$scope.privatedata = {};
	if(!$scope.parentobj.selectedSalesScreenArray){
		$scope.parentobj.selectedSalesScreenArray = {};
	}
	if(!$scope.parentobj.privatedata.initialSelectedSalesScreen){
		$scope.parentobj.privatedata.initialSelectedSalesScreen = [];
	}
	$scope.saveNewState = 0;
	$scope.makeInitialSelectedStores = function(storesarray){
		var keystore = [];
		angular.forEach(storesarray, function(value, key) {
			keystore.push(value);
		});
		return keystore;
	}
	
	//Change the field name to field id
	$scope.tableProperties = {showFieldNames : 1};
	$scope.$watch('tableProperties.showFieldNames', function() {
		if($scope.parentobj.privatedata.salesScreen.headers){
			angular.forEach($scope.parentobj.privatedata.salesScreen.headers, function(value, key){
		        	if($scope.tableProperties.showFieldNames){
		        		$scope.parentobj.privatedata.salesScreen.headers[key]['displayName'] = value['fieldName'];
		        	}else{
		        		$scope.parentobj.privatedata.salesScreen.headers[key]['displayName'] = value['fieldId'];
		        	}
        		});
        		$scope.salesScreenGridOption.columnDefs = $scope.parentobj.privatedata.salesScreen.headers;
        		$scope.salesScreenGridApi.core.notifyDataChange(uiGridConstants.dataChange.COLUMN);
        	}
	},true);
	
	if(!$scope.parentobj.privatedata.salesScreen){
		$scope.parentobj.privatedata.salesScreen = {};
	}
	
	$scope.changeTableView = function(view){
		if(view && view.gridState){
			$scope.salesScreenGridApi.saveState.restore( $scope, view.gridState );
		}
		if(!$scope.salesScreenGridOption.paginationPageSize){
			$scope.salesScreenGridOption.paginationPageSize = $scope.pagination.pageSize;
		}
	}
	$scope.showSaveTableView = function(){
		if($scope.isAllowedToSaveSalesScreenView){
			$scope.saveNewState = 1;
		}
	}
	
	$scope.parentobj.getSalesScreen = function(getCount=1,getHeaders=0) {
		var start = ($scope.pagination.currentPage - 1) * $scope.pagination.pageSize;
		var getIsAllowedToSaveSalesScreenView = 0;
		var getAllowedSalesScreenViews = 0;
		
		if(!$scope.parentobj.privatedata.salesScreen.headers){
			getHeaders = 1;
		}
		
		if(!$scope.isAllowedToSaveSalesScreenView){
			getIsAllowedToSaveSalesScreenView = 1;
		}
		
		if(!$scope.privatedata.availableSalesScreenViews){
			$scope.privatedata.availableSalesScreenViews = [];
			getAllowedSalesScreenViews = 1;
		}
		var httpCache = $cacheFactory.get('$http');
		var url = "/salesScreen.php?action=getallsalesscreen&start="+start+"&limit="+$scope.pagination.pageSize+"&orderby="+$scope.pagination.orderBy+"&getcount="+getCount+"&getheaders="+getHeaders+"&getIsAllowedToSaveSalesScreenView="+getIsAllowedToSaveSalesScreenView+"&getAllowedSalesScreenViews="+getAllowedSalesScreenViews;
		
		var cachedResponse = httpCache.get(url);
		if(cachedResponse && $scope.parentobj.privatedata.salesScreen && cachedResponse.data.status){
		
		}else if(!$scope.parentobj.privatedata.salesScreen.details || ($scope.parentobj.privatedata.salesScreen.details && ($scope.currentUrl && url != $scope.currentUrl))){
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
					$scope.parentobj.privatedata.salesScreen.details = response.data.salesScreen;
					if(response.data.headers && response.data.headers.length>0){
						$scope.parentobj.privatedata.salesScreen.headers = response.data.headers;
					}
					if(response.data.totalcount>0){
						$scope.pagination.totalSalesScreen = response.data.totalcount;
					}
					var enableGridMenu = false;
					if(response.data.isAllowedToSaveItemView){
						$scope.isAllowedToSaveSalesScreenView = response.data.isAllowedToSaveSalesScreenView;
						enableGridMenu = true;
						$scope.showSaveTableView();
					}
					
					if(response.data.availableSalesScreenViews){
						$scope.privatedata.availableSalesScreenViews = response.data.availableSalesScreenViews;
					}
					$scope.parentobj.updateGrid($scope,'salesScreenGridOption','salesScreenGridApi', enableGridMenu, getIsAllowedToSaveSalesScreenView, $scope.parentobj.privatedata.salesScreen, 'gridRowMenu', 'salesScreenDetail_salesScreenId');
					$scope.changeTableView($scope.privatedata.salesScreenSelectedView);
					
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
	
	$scope.parentobj.initGrid($scope, $scope.parentobj.getSalesScreen, 'salesScreenGridOption', 'salesScreenGridApi', 'salesScreenSelectedView', 'availableSalesScreenViews', false, $scope.parentobj.privatedata.selectedSalesScreenArray,'salesScreenDetail_salesScreenId');
	
	
	if(!$scope.pagination.totalItems){
		$scope.parentobj.setlog('Getting Sales Screen List From Server',1);
		$scope.parentobj.getSalesScreen(1,1);
	}
	
	$scope.parentobj.getAvailableModules = function(){
		$scope.parentobj.setlog('Getting available Modules...',1);
		$http({
			method : "GET",
			url : "/salesScreen.php?action=getavailablemodules",
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
	
	$scope.parentobj.getSalesScreenDetails = function(salesScreen, action='edit'){
		$scope.parentobj.pageIsLoading = 1;
		$scope.parentobj.setlog("Getting Sales Screen Details",1);
		if(action=="edit"){
			if(salesScreen['salesScreenDetail_salesScreenId']){
				var salesScreenParameters = {
					action: "getSalesScreenDetails", 
					properties: ["data"],
					salesScreenId: salesScreen['salesScreenDetail_salesScreenId']
				};
				
				$http({
					method : "POST",
					url : "/salesScreen.php",
					data: salesScreenParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
				
					$scope.parentobj.pageIsLoading = 0;
					if(response.data.status){						
						angular.extend(salesScreen,response.data.salesScreen.rows[0]);
						$scope.parentobj.setlog('',0);
						if(response.data.salesScreen.rows[0].salesScreenIdstoresId_storeIdGroup){
							$scope.parentobj.privatedata.initialSelectedStores = $scope.makeInitialSelectedStores(response.data.salesScreen.rows[0].salesScreenIdstoresId_storeIdGroup.split(','));
						}else{
							$scope.parentobj.privatedata.initialSelectedStores = [];
						}
						$scope.parentobj.OpenSalesScreenDetails(salesScreen);
					}else{
						$scope.parentobj.setlog(response.data.msg,0);
					}
				});
			}else{
				$scope.parentobj.setlog("Error in finding requested sales screen",0);
				$scope.parentobj.pageIsLoading = 0;
			}
		}else{
			$scope.parentobj.setlog("Creating new Sales Screen",0);
			$scope.parentobj.pageIsLoading = 0;
			$scope.parentobj.privatedata.initialSelectedStores = [];
			$scope.parentobj.privatedata.selectedStoresArray = {};
			salesScreen = {
				'stores':{},
				'salesScreenDetail_salesScreenId':'',
				'salesScreenDetail_salesScreenName': '',
				'salesScreenStatus_status': '1',
				'salesScreenDetail_salesScreenData': {
					options: {
						gridType: 'fit',
						margin: 0,
						compactType: 'none',
						minCols: 5,
						maxCols: 30,
						minRows: 5,
						maxRows: 30,
						outerMargin: true,
						scrollSensitivity: 10,
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
						disableWindowResize: false // disable the window on resize listener. This will stop grid to recalculate on window resize.
					},
					screens: [
						{
							screenId: guid.newuuid('salesScreenDetails_eenId'),
							screenName: 'main',
							items:[]
						}
					]
				}
			};
			$scope.parentobj.OpenSalesScreenDetails(salesScreen, action);
		}
	};
	
	$scope.parentobj.OpenSalesScreenDetails = function(salesScreen, action="edit", template='editSalesScreen.html'){
		$scope.privatedata.record = angular.copy(salesScreen);
		var originalSalesScreenData = angular.copy(salesScreen);
		$mdDialog.show({
			scope: $scope,
	   		preserveScope: true,
			controller: DialogController,
			templateUrl: template,
			parent: angular.element(document.body),
			targetEvent: salesScreen,
			clickOutsideToClose: false,
			locals: {
				originalSalesScreenData: originalSalesScreenData,
				salesScreen: salesScreen,
				actionScope: action
			},
			multiple: true,
			openFrom: "#edit_"+$scope.privatedata.record['salesScreenDetail_salesScreenId'],
			closeTo: "#edit_"+$scope.privatedata.record['salesScreenDetail_salesScreenId']
		})
		.then(function(salesScreen) {
		
		}, function() {
	
		});
	};
	
	function DialogController(salesScreen, actionScope, $scope, $mdDialog, originalSalesScreenData, $mdToast, $http, $templateCache) {
		var recordscope;
		$scope.parentobj.openSalesScreenVisualEditor = function(data){

			var originalData = angular.copy(data);
			$mdDialog.show({
		   		preserveScope: false,
				controller: VisualEditorDialogController,
				templateUrl: 'salesScreenVisualEditor.html',
				parent: angular.element(document.body),
				targetEvent: salesScreen,
				clickOutsideToClose: false,
				locals: {
					data: data,
					salesScreen: salesScreen,
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
		$scope.clickToSave = function(salesScreen){
			$scope.privatedata.record['stores'] = $scope.parentobj.privatedata.selectedStoresArray;
			
			if($scope.privatedata.record['salesScreenStatus_status'] === '0' || $scope.privatedata.record['salesScreenStatus_status'] === 0){
				var confirm = $mdDialog.confirm()
					.title('Confirm Delete')
					.textContent('You are disabling this sales screen and it will not be shown in the list. Are you sure?')
					.ariaLabel('confirm delete')
					.targetEvent()
					.multiple(true)
					.ok('Yes, disable it!')
					.cancel('No! Do NOT disable!');

				$mdDialog.show(confirm).then(function() {
					$scope.saveSalesScreen();
				}, function() {
					showMainformToast('noChangesMade.html', $mdToast);
					$mdDialog.hide();
				});
			}else{
				$scope.saveSalesScreen();
			}
		}
		$scope.clickToClose = function(salesScreen){
			recordscope = angular.copy($scope.privatedata.record);
			$scope.parentobj.setlog('',0);

			if(angular.equals(recordscope,originalSalesScreenData)){
				$mdDialog.cancel();
			}else{
				confirmDialogClose($mdDialog,$scope.clickToSave,$mdDialog.cancel, $scope);
			}
		}
		
		$scope.saveSalesScreen = function(){
			recordscope = angular.copy($scope.privatedata.record);
			if(angular.equals(recordscope,originalSalesScreenData)){
				showMainformToast('noChangesMade.html', $mdToast);
				$mdDialog.hide();
			}else{
				$scope.editSalesScreen($mdDialog, $http, $mdToast, $templateCache)
			}
		}
		$scope.editSalesScreen = function($mdDialog, $http, $mdToast, $templateCache) {
			$scope.parentobj.setlog("Sending data to server",1);
			$scope.formDisabled = true;
			$scope.responseErrText = '';
			$scope.salesScreenDetails.$valid = true;
			var options = [];
			var changeArray = {};

			if($scope.salesScreenStatus.modified || actionScope ==="create"){
				changeArray.changeStatus = 1;
			}else{
				delete recordscope['salesScreenStatus_status'];
			}
			if($scope.salesScreenDetails.modified || recordscope['salesScreenDetail_salesScreenData']!=originalSalesScreenData['salesScreenDetail_salesScreenData']){
				changeArray.changeDetails = 1;
			}else{
				delete recordscope['salesScreenDetail_salesScreenData'];
				delete recordscope['salesScreenDetail_salesScreenName'];
			}
			
			//TODO originalSalesScreenData['salesScreenIdstoresId_storeIdGroup']!=recordscope['stores'] check for id of status 1s and count status 0s
			var storeSize = Object.keys(recordscope['stores']).length;
			if(storeSize>0){
				changeArray.changeStores = 1;
			}
			recordscope.action = actionScope;
			var salesScreenParameters = {
				action: "saveDetails", 
				options: recordscope,
				changeArray: changeArray
			};

			if($scope.salesScreenDetails.$valid){
				$http({
					method : "POST",
					url : "/salesScreen.php",
					data: salesScreenParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					if(response.data.status){
						var i = 0;
						if(actionScope ==="create"){
							if(response.data['addSalesScreen'].status){
								salesScreen['salesScreenDetail_salesScreenId'] = response.data['addSalesScreen'].salesScreenId_salesScreenId;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data['addSalesScreen'].msg;
							}
						}
						angular.forEach(changeArray, function(value, key) {
							if(response.data[key].status && value){
								i = i + 1;
								if(key==='changeDetails'){
									salesScreen['salesScreenDetail_salesScreenName'] = recordscope['salesScreenDetail_salesScreenName'];
									salesScreen['salesScreenDetail_salesScreenData'] = recordscope['salesScreenDetail_salesScreenData'];
								}else if(key === 'changeStatus'){
									salesScreen['salesScreenStatus_status'] = recordscope['salesScreenStatus_status'];
								}
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}
						});
						if(actionScope ==="create" && response.data['addSalesScreen'].status){
							if($scope.parentobj.privatedata.salesScreen){
								if($scope.parentobj.privatedata.salesScreen.details){
									if($scope.parentobj.privatedata.salesScreen.details.rows){
										$scope.parentobj.privatedata.salesScreen.details.rows.push(salesScreen);
									}else{
										$scope.parentobj.privatedata.salesScreen.details.rows = {};
										$scope.parentobj.privatedata.salesScreen.details.rows.push(salesScreen);
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
								templateUrl : 'salesScreenChangeOK.html'
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
	
	function VisualEditorDialogController(salesScreen, data, originalData, availableModules, makeModule, $scope, $mdDialog, $mdToast, $http, $templateCache) {
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
			salesScreen['salesScreenDetail_salesScreenData'] = data;
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
			let newScreenId = guid.newuuid('salesScreenDetails_eenId');
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
		$scope.addModule = function(element){
			selectedScreenIndex = data.screens.findIndex(screen => screen.screenId == selectedScreen.screenId);
			if(!data.screens[selectedScreenIndex].items){
				data.screens[selectedScreen.screenId].items = [];
			}
			
			data.screens[selectedScreenIndex].items.push({'element': element});
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
		var output = '';
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
					callback(output);
					
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
				callback(output);
			break;
			case 'button':
				var baseClass = "";
				if(parameters=="rect"){
					baseClass = "salesScreen-button";
				}else{
					baseClass = "md-fab salesScreen-button";
				}
				var confirm = $mdDialog.confirm({
					multiple: true,
					controller: GeneralDialogController,
					templateUrl: 'modules_button.html',
					parent: angular.element(document.body)
				});

				$mdDialog.show(confirm).then(function(scope) {
					if(!scope.text){scope.text='';}
					if(!scope.class){scope.class='md-raised md-primary';}
					output = "<md-button class='"+baseClass+" "+scope.class+"' data-ng-click='"+scope.action+"'>"+scope.text+"</md-button>";
					/*var parameters = {
						baseClass: baseClass,
						class: scope.class,
						action: scope.action,
						text: scope.text
					};
					output = "<module-button parameters='"+parameters+"'></module-button>";*/
					callback(output);
					
				}, function() {
					
				});
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
					callback(output);
					
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