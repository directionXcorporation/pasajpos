var app = angular.module("pasaj", ["ngRoute",'ui.router',"ngSanitize","ngMaterial","ngFitText","RecursionHelper",'ngInputModified','ngMessages', 'ngCookies', 'md.data.table','angular-toArrayFilter', 'angular-sumByKey',"ui.grid",'ngMask','ui.grid.resizeColumns', 'ui.grid.autoResize', 'ui.grid.moveColumns','ui.grid.saveState','ui.grid.pagination', 'ui.grid.pinning', 'ui.grid.grouping', 'ui.grid.exporter', 'ui.grid.selection', 'ui.grid.edit', 'ui.grid.cellNav', 'angular-gridster2', 'angular-bind-html-compile', 'lokijs', 'LocalStorageModule', 'ngTable', 'material.components.keyboard','AngularPrint','pasvaz.bindonce','angular.bind.notifier', 'angularMoment']);
var loadedDirectives = [];
function getObject(objectType, objectName) {
    var result  = '',
        invokes = app._invokeQueue;

    for(var i=0; i<invokes.length; ++i) {
        if(invokes[i][1] == "directive") {
            result = invokes[i][2][0];
        }
    }
    return result;
}

			app.config(function($stateProvider) {
				var mainState = {
					name: 'main',
					url: '',
					loginRequired: 0,
					params: {
                        
                    },
                    abstract: true,
					redirect: 'dynaPage'
				}

				var dynaPageState = {
					name: 'dynaPage',
					url: '/:pageCode/:mainObjectId',
					loginRequired: 1,
					params: {
                        pageCode: {type: 'string', squash: "", value: 'home'},
                        mainObjectId: {type: 'string', squash: true, value: null, dynamic: false},
                        mainData: {array:true, squash: true, value: null, dynamic: false},
                        accessWithoutLogin: {type: 'int', squash: true, value: 0, dynamic: false}
                    },
					views : {
						'main@' : {
							templateUrl : function (params) {
								if(params.pageCode && !params.modalPageId){
									var fullUrl = "/dynaPage.php?pageCode="+params.pageCode;
									return fullUrl;
								}
							       	//return a valid Url to the template, and angular will load it for you
							},
							controller: DynamicRoutes
						}
					}
				}

				$stateProvider.state(mainState);
				$stateProvider.state(dynaPageState);
});
function DynamicRoutes($scope, $state, Auth, logService){
    logService.setlog(["state changed with following parameters:",$state], "debug");
    if(!$scope.parentobj){
        $scope.parentobj = {
            controllers: {}
        };
    }
    if(!$scope.parentobj.controllers.dynaPage){
        $scope.parentobj.controllers.dynaPage = {};
    }
	if($state.current.name == 'main'){
        
	}
}
app.factory('sharedata', function(lodash) {
    var list = {};

    return {
        addItem: addItem,
        getList: getList,
        clearList: clearList,
        searchList: searchList
    };
    function clearList(limit=0, pageId='mainform') {
        if(list && list[pageId] && list[pageId].length > limit){
            list[pageId].length = limit;
        }
    }
    function addItem(item, pageId='mainform') {
        if(!list[pageId]){
            list[pageId] = [];
        }
        list[pageId].push(item);
    }

    function getList(limit=0, pageId='mainform') {
        let t = angular.copy(list[pageId]);
        if(limit>-1){
            clearList(limit, pageId);
        }
        return t;
    }
    
    function searchList(pageId, searchCondition, limit=0){
        let result = lodash.filter(list[pageId], searchCondition);
        if(limit == 1 && result && result.length){
            result = [result[result.length - 1]];
        }
        if(limit>-1){
            clearList(limit, pageId);
        }
        return result;
    }
});

app.factory('Auth', function($q, httpService, $rootScope, logService, localDb, loginService, onlineStatus){
	var currentUser;

    return {
        clearUser: clearUser,
        setUser: setUser,
        isLoggedIn: isLoggedIn,
        getUser: getUser,
        CheckLoggedIn: CheckLoggedIn
    };

	function clearUser(){
	        logService.setlog("requesting to clear loggedin User", "debug");
	        return localDb.initilizeDb().then(function(db){
	            loginService.removeLoggedinUser(db);
	            return setUser(undefined);
	        });
	}
	function setUser(aUser){
	        currentUser = aUser;
	        return 1;
	}
	function isLoggedIn(){
	    	if(currentUser){
	    		return currentUser;
	    	}else{
	    		return false;
	    	}
	}
	function getUser(){
	        if(!currentUser){
	            clearUser();
	        }
	    	return currentUser;
	}
	function CheckLoggedIn() {
	    	return $q(function (resolve, reject) {
    			if(onlineStatus.isOnline()){
    			    httpService.request({
                        url: '/login.php?action=checkLoginStatus', 
                        method: "GET",
                        params: {}  
                    })
    				.then(function(response) {
    					setUser(response.data.userId);
    					if (isLoggedIn()) {
    						resolve();
    					} else {
    					    clearUser().then(function(){
    					    	reject();
    					    });
    					}
    				},
    				function(error){
    				    clearUser().then(function(){
    					    reject(error);
    				    });
    				});
    			}else{
    				if(getUser()){
    					resolve();
    				}else{
    				    localDb.initilizeDb().then(function(db){
    				        let user = loginService.getLoggedInUserDetails(db, 0);
                            if(user && user.userId){
                                setUser(user.userId);
                                resolve();
                            }else{
                                clearUser().then(function(){
                                    reject();
                                });
                            }
    				    });
    				}
    			}
		    });
	}
});
app.factory('httpService', function($q, $http, logService, onlineStatus){
    return {
        request: request
    };

	function request(options={}){
	    logService.setlog(["requesting network data", options], "debug");
	   // if(onlineStatus.isOnline()){
    	    return $http(options).then(function(response){
    	        return response;
    	    }, function submitError(response) {
                return response;
        	});
	   /* }else{
	        return $q(function (resolve, reject) {
	            resolve();
	        })
	    }*/
	}
});
app.run(function ($state, $transitions, Auth, $log, $q, loginService, $location, logService) {
	if($location.url().trim() == ''){
		$state.go("dynaPage",{pageCode: 'home', "accessWithoutLogin": 1});
	}
	var loginRequired = {
		to: function(state) {
			return state.loginRequired;
		}
	}
	$transitions.onBefore( {}, function($transitions) {
		let toState = $transitions.targetState();
		return $q(function (resolve, reject) {
			return Auth.CheckLoggedIn().then(function(){
				resolve();
			}, function(){
			    logService.setlog(["changing state to the following",{toState: toState}], "debug");
			    if(loginRequired && !toState.params().accessWithoutLogin){
				    return loginService.showLogin('usernameLogin')
					.then(function (response) {
						if(response.userId){
						    Auth.setUser(response.userId);
							resolve();
						}else{
							reject();
						}
					})
					.catch(function () {
						reject();
					});
			    }else{
			        Auth.clearUser().then(function(){
			            logService.setlog("user is logged out but page does not require login", "debug");
					    resolve();
			        });
			    }
			});
		});
	});

});

			app.config(function ($mdThemingProvider, $mdIconProvider) {
			    $mdThemingProvider.theme('minimal')
                    //.primaryPalette('blue')
                    .accentPalette('grey');
			    $mdThemingProvider.theme("error-toast");
			    $mdThemingProvider.theme("offlineTheme")
			      .primaryPalette('blue-grey')
			      .accentPalette('blue-grey');
			    $mdThemingProvider.setDefaultTheme('default');
			    $mdThemingProvider.alwaysWatchTheme(false);
			});
			
			app.factory('onlineStatus', ["$window", "$rootScope", function ($window, $rootScope) {
			    var onlineStatus = {};
			
			    onlineStatus.onLine = $window.navigator.onLine;
			
			    onlineStatus.isOnline = function() {
			        return onlineStatus.onLine;
			    }
			
			    $window.addEventListener("online", function () {
			        onlineStatus.onLine = true;
			        $rootScope.$digest();
			    }, true);
			
			    $window.addEventListener("offline", function () {
			        onlineStatus.onLine = false;
			        $rootScope.$digest();
			    }, true);
			
			    return onlineStatus;
			}]);
			// -------------------------------------------------- //
		        // -------------------------------------------------- //
		        // I provide a request-transformation method that is used to prepare the outgoing
		        // request as a FORM post instead of a JSON packet.
		        app.factory(
		            "transformRequestAsFormPost",
		            function() {
		                // I prepare the request data for the form post.
		                function transformRequest( data, getHeaders ) {
		                    var headers = getHeaders();
		                    headers[ "Content-type" ] = "application/x-www-form-urlencoded; charset=utf-8";
		                    return( serializeData( data ) );
		                }
		                // Return the factory value.
		                return( transformRequest );
		                // ---
		                // PRVIATE METHODS.
		                // ---
		                // I serialize the given Object into a key-value pair string. This
		                // method expects an object and will default to the toString() method.
		                // --
		                // NOTE: This is an atered version of the jQuery.param() method which
		                // will serialize a data collection for Form posting.
		                // --
		                // https://github.com/jquery/jquery/blob/master/src/serialize.js#L45
		                function serializeData( data ) {
		                    // If this is not an object, defer to native stringification.
		                    if ( ! angular.isObject( data ) ) {
		                        return( ( data == null ) ? "" : data.toString() );
		                    }
		                    var buffer = [];
		                    // Serialize each key in the object.
		                    for ( var name in data ) {
		                        if ( ! data.hasOwnProperty( name ) ) {
		                            continue;
		                        }
		                        var value = data[ name ];
		                        buffer.push(
		                            encodeURIComponent( name ) +
		                            "=" +
		                            encodeURIComponent( ( value == null ) ? "" : value )
		                        );
		                    }
		                    // Serialize the buffer and clean it up for transportation.
		                    var source = buffer
		                        .join( "&" )
		                        .replace( /%20/g, "+" )
		                    ;
		                    return( source );
		                }
		            }
			);
			
			app.controller('mainform', function($scope, $filter, $location, $window, httpService, $timeout, $mdSidenav, $log, $mdDialog, $mdToast, $templateCache, $transitions, $interval, $state, Auth, uiGridConstants, uiGridExporterConstants, uiGridExporterService, localDb, externalDevice, logService, menuService, loginService, lodash) {
			    let vm = this;
				externalDevice.sendData("%clearScreen%","LINE_DISPLAY").then(function(){
				externalDevice.sendData("Till Clsoed","LINE_DISPLAY");
				});
				$scope.dynamicTheme = "default";
				
				if(!$scope.parentobj){
					$scope.parentobj = {};
				}
				if(!$scope.parentobj.controllers){
					$scope.parentobj.controllers = {};
				}
				if(!$scope.parentobj.privatedata){
					$scope.parentobj.privatedata = {};
				}

				$scope.$watch("appIsOnline", function(newValue, oldValue){
					let msg = '';
					if(newValue){
						$scope.dynamicTheme = 'default';
						msg = "You are back online";
					}else{
						$scope.dynamicTheme = 'offlineTheme';
						msg = "You are offline, but you can continue sales";
					}
					logService.setlog(msg, "info", 0);
					$timeout(function(){logService.clearlog();}, 2500);
				});
				$scope.privatedata = {};
				$scope.expanded = {};
				$scope.parentobj.privatedata.invalidateddata = {};
				$scope.parentobj.privatedata.brandsWithDetails = [];
				
				$scope.parentobj.currentpage = "";
				$scope.parentobj.globalmsg = '';
				$scope.parentobj.isloading = 0;
				$scope.parentobj.checkLength = function(arr){
					if(arr){
						return arr.length;
					}else{
						return 0;
					}
				}
				var showLogin = function(method = ''){
				    if(!method){
				        method = "usernameLogin";
				    }
				    return loginService.logout().then(function(logoutresponse){
				        logService.setlog(["Current user loggedout",logoutresponse], "debug");
    				    return loginService.showLogin(method).then(function(response){
    				        if(response.userId){
        				        return loginService.getLoggedInUserDetails(0).then(function(user){
        				            if(user && user.menu && user.userGroups){
            						    $scope.parentobj.privatedata.menu = lodash.sortBy(user.menu, 'dynaPageMenu_menuOrder');
            							$scope.parentobj.privatedata.allgroups = user.userGroups;
            							$state.reload();
                                    }else{
                                        menuService.getAllowedMenus([], 'groupId').then(function(menu){
                                            $scope.parentobj.privatedata.menu = menu;
                                            $scope.parentobj.privatedata.allgroups = [];
                                            $state.reload();
                                        });
                                    }
        				        });
    				        }else{
    				            return menuService.getAllowedMenus([], 'groupId').then(function(menu){
                                    $scope.parentobj.privatedata.menu = menu;
                                    $scope.parentobj.privatedata.allgroups = [];
                                    $state.reload();
                                });
    				        }
    				    });
				    });
				}
				$scope.createEmptyMenu = function(showLogin=1, showRegister=1){
				    let menu = [];
				    let loginMenu = {
				        'dynaPageMenu_internal': 1,
				        'dynaPageMenu_menuCode': 'login',
				        'dynaPageMenu_menuIcon': 'lock',
				        'dynaPageMenu_menuName': 'Login',
				        'dynaPageMenu_menuOrder': 0,
				        'dynaPageMenu_pageId': '',
				        'dynaPageMenu_parentCode': '',
				        'dynaPageMenu_status': 1,
				        'dynaPageMenu_lang': 'en',
				        'dynaPageMenu_menuAction': 'showLogin()',
				        'dynaPageDetails_availableOffline': 1
				    };
				    let registerMenu = {
				        'dynaPageMenu_internal': 1,
				        'dynaPageMenu_menuCode': 'login',
				        'dynaPageMenu_menuIcon': 'perm_contact_calendar',
				        'dynaPageMenu_menuName': 'Register',
				        'dynaPageMenu_menuOrder': 1,
				        'dynaPageMenu_pageId': '',
				        'dynaPageMenu_parentCode': '',
				        'dynaPageMenu_status': 1,
				        'dynaPageMenu_lang': 'en',
				        'dynaPageMenu_menuAction': 'showLogin()',
				        'dynaPageDetails_availableOffline': 1
				    };
				    if(showLogin){
				        menu.push(loginMenu);
				    }
				    if(showRegister){
				        menu.push(registerMenu);
				    }
				    return menu;
				}
				$scope.$watch(Auth.isLoggedIn, function (value, oldValue) {
				    logService.setlog(["login state changed",{oldVal: oldValue, newVal:value}]);
					if(!value && oldValue) {
						delete $scope.parentobj.privatedata;
						$scope.parentobj.privatedata = {};
						delete $scope.privatedata;
						$scope.privatedata = {};
						//$templateCache.removeAll();
						$log.info("New Disconnect");
						if($state.current.loginRequired){
							
						}
					}else if(value && !oldValue) {
						delete $scope.parentobj.privatedata;
						$scope.parentobj.privatedata = {};
						delete $scope.privatedata;
						$scope.privatedata = {};
						$log.info("New Connect");
						//Do something when the user is connected
					}else if(value){
						$log.info("Old Connect");
					}else if(!oldValue && !value){
						$log.info("Refreshed");
					}
					if(!$scope.gettingUserData){
						if(oldValue != value || !$scope.parentobj.privatedata.menu){
							$scope.gettingUserData = 1;
							logService.setlog("Fetching Menu Data ...", "info", 1);
							loginService.getLoggedInUserDetails(0).then(function(user){
							    console.log(user);
                                    if(user && user.menu && user.userGroups){
    							        $scope.parentobj.privatedata.menu = lodash.sortBy(user.menu, 'dynaPageMenu_menuOrder');
    							        $scope.parentobj.privatedata.allgroups = user.userGroups;
                                    }else{
                                        menuService.getAllowedMenus([], 'groupId').then(function(menu){
                                            console.log(menu);
                                            $scope.parentobj.privatedata.menu = lodash.sortBy(menu, 'dynaPageMenu_menuOrder');
                                            $scope.parentobj.privatedata.allgroups = [];
                                        });
                                    }
                                    $scope.gettingUserData = 0;
							        logService.clearlog();
							});
						}
					}
				}, true);
				
				$transitions.onStart({}, function(trans) {
					$scope.parentobj.pageIsLoading = 1;
				});
				$transitions.onSuccess({}, function() {
					$scope.parentobj.pageIsLoading = 0;
				});
				$scope.closeMainNavBar = function () {
				      // Component lookup should always be available since we are not using `ng-if`
				      $mdSidenav('mainsidemenuholder').close()
				        .then(function () {

				        });
				
				};
				$scope.sideNavOpen = 1;
				$scope.toggleMainNavBar = function () {
				      // Component lookup should always be available since we are not using `ng-if`
				      if($scope.sideNavOpen){
    				      $scope.sideNavOpen = 0;
    				      $mdSidenav('mainsidemenuholder').close()
    				        .then(function () {
    
    				        });
				      }else{
				          $scope.sideNavOpen = 1;
    				      $mdSidenav('mainsidemenuholder').open()
    				        .then(function () {
    
    				        });
				      }
				      
				
				};
				
				$scope.parentobj.updateGrid = function($scope, gridOptionVar, gridApiVar, enableGridMenu, getIsAllowedToSaveStoreView, data, rowMenuTemplate, uniqueRowId, initialSelectedVals=[]){
					if(getIsAllowedToSaveStoreView){
						$scope[gridOptionVar].enableGridMenu = enableGridMenu;
					}
					if(data){
						if(data.headers){
							$scope[gridOptionVar].columnDefs = data.headers;
						}
						if(data.details){
							$scope[gridOptionVar].data = data.details.rows;
						}
					}
					if(rowMenuTemplate){
						var actionColumn = $filter('filter')($scope[gridOptionVar].columnDefs, {name: 'Actions'})[0];
						if(actionColumn){
							var index = $scope[gridOptionVar].columnDefs.indexOf(actionColumn);
							if (index != -1) {
								$scope[gridOptionVar].columnDefs.splice(index, 1);
							}
						}
						$scope[gridOptionVar].columnDefs.push(
							{
								name: 'Actions',
								displayName: 'Actions',
								enableHiding: false,
								enableCellEdit: false,
								width: 50,
								enableFiltering: false,
								enableSorting: false,
								enableGrouping: false,
								pinnedRight:true,
								cellTemplate: rowMenuTemplate+'.html'
							}
						);
					}
					$scope[gridApiVar].core.notifyDataChange(uiGridConstants.dataChange.OPTIONS);
					//$scope[gridApiVar].core.notifyDataChange(uiGridConstants.dataChange.ALL);
					if($scope[gridApiVar].selection){
						$timeout(function () {
							angular.forEach(initialSelectedVals, function(val,key){
								var obj = {};
								obj[uniqueRowId] = val;
								var initialSelectedRowId = $filter('filter')($scope[gridOptionVar].data, obj)[0];
								//$scope[gridApiVar].grid.modifyRows($scope[gridOptionVar].data);
								if(initialSelectedRowId){
									$scope[gridApiVar].selection.selectRow(initialSelectedRowId, true);
								}
							});
						},1);
					}
				}

				$scope.parentobj.initGrid = function($scope, callback, gridOptionVar, gridApiVar, selectedView=false, availableViews=false, setMultiSelect=false, selectedRowsArray=[],uniqueRowId='',callbackOnSelectIfSingleSelect=undefined){
					//Pagination
					$scope.pagination = {
						paginationPageSizes: [2, 25, 50, 100, 150, 300],
						currentPage: 1,
						pageSize: 2,
						totalItems: 0,

						getTotalPages: function () {
							return Math.ceil(this.totalItems / this.pageSize);
						},
						pageSizeChange: function () {
							this.currentPage = 1
							callback(0,0);
						},
						firstPage: function () {
							if (this.currentPage > 1) {
								this.currentPage = 1
								callback(0,0);
							}
						},
						nextPage: function () {
							if (this.currentPage < this.getTotalPages()) {
								this.currentPage++;
								callback(0,0);
							}
						},
						previousPage: function () {
							if (this.currentPage > 1) {
								this.currentPage--;
								callback(0,0);
							}
						},
						lastPage: function () {
							if (this.currentPage >= 1) {
								this.currentPage = this.getTotalPages();
								callback(0,0);
							}
						}
					};
					
					if(!$scope[gridOptionVar]){
						$scope.pagination.orderBy = '';
						
						$scope[gridOptionVar] = {
							saveFocus: false,
							saveScroll: false,
							saveSelection: false,
							saveGroupingExpandedStates: false,
							savePagination: false,
							saveFilter: true,
							saveGrouping: false,
							saveOrder: true,
							savePinning: true,
							saveRowIdentity: undefined,
							saveSort: true,
							saveTreeView: true,
							saveVisible: true,
							saveWidths: true,
							useExternalPagination: true,
							useExternalSorting: true,
							enableSorting: true,
							enablePagination: false,
							enablePaginationControls: false,
							paginationPageSize: $scope.pagination.pageSize,
							paginationPageSizes: $scope.pagination.paginationPageSizes,
							virtualizationThreshold: 25,
							columnVirtualizationThreshold: 10,
							excessRows: 4,
							scrollThreshold: 4,
							excessColumns: 4 ,
							horizontalScrollThreshold: 2,
							scrollThrottle: 70,
							enableColumnResizing: true,
							multiSelect: setMultiSelect,
							enableRowSelection: true,
							enableFiltering: true,
							enableGridMenu: false,
							showColumnFooter: true,
							fastWatch: true,
							exporterMenuCsv: true,
							exporterMenuPdf: true,
							exporterCsvFilename: 'myFile.csv',
							exporterPdfDefaultStyle: {fontSize: 9},
							exporterPdfTableStyle: {margin: [30, 30, 30, 30]},
							exporterPdfTableHeaderStyle: {fontSize: 12, bold: true, italics: true, color: 'blue'},
							exporterPdfHeader: { text: "Pasaj POS", style: 'headerStyle' },
							exporterPdfFooter: function ( currentPage, pageCount ) {
								return { text: currentPage.toString() + ' of ' + pageCount.toString(), style: 'footerStyle' };
							},
							exporterPdfCustomFormatter: function ( docDefinition ) {
								docDefinition.styles.headerStyle = { fontSize: 22, bold: true };
								docDefinition.styles.footerStyle = { fontSize: 10, bold: true };
								return docDefinition;
							},
							exporterPdfOrientation: 'portrait',
							exporterPdfPageSize: 'LETTER',
							exporterPdfMaxGridWidth: 500,
							exporterCsvLinkElement: angular.element(document.querySelectorAll(".custom-csv-link-location")),
							showGridFooter: true,
							gridFooterTemplate:'gridFooterTemplate.html',
							onRegisterApi: function onRegisterApi(gridApi) {
								gridApi.core.on.sortChanged( $scope, function( grid, sortColumns ) {
									var newState = gridApi.saveState.save();
									if(availableViews && selectedView){
										if($scope.privatedata[availableViews].findIndex(x=>angular.equals(x.gridState,newState))==-1){
											newState =  {viweName:'',gridState:''};
											if($scope.privatedata[availableViews].findIndex(x=>x.gridState==='')==-1){
												$scope.privatedata[availableViews].push(newState);
												$scope.privatedata[selectedView] = newState;
											}else{
												$scope.privatedata[selectedView] = newState;
											}
										}else{
											
										}
									}
									var sortArray = [];
									angular.forEach(sortColumns, function(value, key){
										var sortDirection = '';
										switch( value.sort.direction ) {
											case uiGridConstants.ASC:
												sortDirection = "ASC";
											break;
											case uiGridConstants.DESC:
												sortDirection = "DESC";
											break;
										}
										sortArray[value.sort.priority] = value.name.replace("_", ".")+" "+sortDirection;
									});
									$scope.pagination.orderBy = sortArray.join(",");
									if($scope.pagination.orderBy){
										callback(0,0);
									}
								});
								if(gridApi.selection){
									gridApi.selection.on.rowSelectionChanged($scope, function(row) {
									
										if(uniqueRowId && uniqueRowId != '' && row.entity[uniqueRowId]){
											if(row.isSelected){
												if(row.entity[uniqueRowId]){
													if(callbackOnSelectIfSingleSelect){
														callbackOnSelectIfSingleSelect(row.entity)
													}
													if(!selectedRowsArray[row.entity[uniqueRowId]]){
														if(!setMultiSelect){
															for (key in selectedRowsArray){
																if (selectedRowsArray.hasOwnProperty(key)){			
																	if(selectedRowsArray[key].status=='1'){
																		delete selectedRowsArray[key];
																	}
																}
															}
														}
														selectedRowsArray[row.entity[uniqueRowId]] = {};
														selectedRowsArray[row.entity[uniqueRowId]][uniqueRowId] =  row.entity[uniqueRowId];
														selectedRowsArray[row.entity[uniqueRowId]].status = "1";
													}else{
														if(selectedRowsArray[row.entity[uniqueRowId]].status == "0"){
															delete selectedRowsArray[row.entity[uniqueRowId]];
														}
													}
												}
											}else{
												if(row.entity[uniqueRowId]){
													if(!selectedRowsArray[row.entity[uniqueRowId]]){
														selectedRowsArray[row.entity[uniqueRowId]] = {};
														selectedRowsArray[row.entity[uniqueRowId]][uniqueRowId] =  row.entity[uniqueRowId];
														selectedRowsArray[row.entity[uniqueRowId]].status = "0";
													}else{
														if(selectedRowsArray[row.entity[uniqueRowId]].status == "1"){
															delete selectedRowsArray[row.entity[uniqueRowId]];
														}
													}
												}
											}
										}
									});
								}
								$scope[gridApiVar] = gridApi;
							},
							data: [],
							columnDefs: [{ field: ' '}]
						};
					}
				}
				$scope.parentobj.menuReturn = function (menuCode) {
        			if($scope.expanded[menuCode]){
        				$scope.expanded[menuCode] = !$scope.expanded[menuCode];
        			}
        			var filteredData_parent = $scope.parentobj.nestedFilter($scope.parentobj.privatedata.menu,"dynaPageMenu_menuCode",menuCode);
        			if(filteredData_parent[0]){
        				var parentCode_filteredData = filteredData_parent[0].dynaPageMenu_parentCode;
        				var filteredData = $scope.parentobj.nestedFilter($scope.parentobj.privatedata.menu,"dynaPageMenu_parentCode",parentCode_filteredData);
        				$scope.parentobj.privatedata.homemenu = filteredData;
        			}else{
        				
        			}
        		};
        		$scope.parentobj.homeIconClick = function(menuCode,nodes,nodeLength){
        			if(nodeLength){
        				$scope.parentobj.privatedata.homemenu = nodes;
        				$scope.parentobj.privatedata.homemenu.dynaPageMenu_parentCode = menuCode;
        				
        			}
        		}
				$scope.parentobj.menuClick = function (node) {
				    let pageCode = node.dynaPageMenu_pageId;
				    let menuCode = node.dynaPageMenu_menuCode;
				    let nodes = node.nodes || [];
				    let nodeLength = nodes.length || 0;
				    let parentCode = node.dynaPageMenu_parentCode || '';
				    let menuAction = node.dynaPageMenu_menuAction || '';
				    let accessWithoutLogin = node.dynaPageDetails_accessWithoutLogin || 0;
				    
				    logService.setlog(["menu clicked",{node: node, pageCode:pageCode,menuCode:menuCode,nodeLength:nodeLength,nodes:nodes,parentCode:parentCode, menuAction:menuAction, accessWithoutLogin: accessWithoutLogin}], "debug");
					if(nodeLength){
						$scope.expanded[menuCode] = !$scope.expanded[menuCode];
						if($scope.expanded[menuCode]){
							var filteredData = $scope.parentobj.nestedFilter($scope.parentobj.privatedata.menu,"dynaPageMenu_parentCode",parentCode);
							angular.forEach(filteredData , function(value, key) {
								if($scope.expanded[value['dynaPageMenu_menuCode']]){
									if(value['dynaPageMenu_menuCode'] != menuCode){
										$scope.expanded[value['dynaPageMenu_menuCode']] = !$scope.expanded[value['dynaPageMenu_menuCode']];
									}
								}
							});
							if($scope.parentobj.homeIconClick){
								$scope.parentobj.homeIconClick(menuCode,nodes,nodeLength);
							}
						}else{
							var filteredData = $scope.parentobj.nestedFilter($scope.parentobj.privatedata.menu,"dynaPageMenu_parentCode",menuCode);
							angular.forEach(filteredData , function(value, key) {
								if($scope.expanded[value['dynaPageMenu_menuCode']]){
									$scope.expanded[value['dynaPageMenu_menuCode']] = !$scope.expanded[value['dynaPageMenu_menuCode']];
								}
							});
							$scope.parentobj.menuReturn(menuCode);
						}
					}else{
					    if(menuAction){
					       eval(menuAction);
					    }else if(pageCode){
							$state.go("dynaPage", { "pageCode": pageCode, "accessWithoutLogin": accessWithoutLogin});
						}else{
							$state.go("dynaPage",{pageCode: 'home', "accessWithoutLogin": 1});
						}
					}
				}

				function DialogControllerConfirmDelete($scope, $mdDialog) {
					$scope.answer = function(answer) {
						$mdDialog.hide(answer);
					};
				}
				function saveTableViewDialogController($scope, availableViews, parentobj, $mdDialog, tableCode, gridState, $mdToast, httpService, $templateCache) {
					$scope.allgroups = parentobj.privatedata.allgroupsWithDetailsOriginal;
					$scope.selectedViewGroups = [];
					$scope.tableViewName = '';
					$scope.privateView = '1';
					$scope.viewGroupsQuery = {
						page: 1,
						limit: 10,
						order: 'onlineInsertTime'
					};
					$scope.CloseNewTableView  = function() {
						showMainformToast('noChangesMade.html', $mdToast);
						logService.clearlog();
						$mdDialog.cancel();
					}
					
					$scope.saveTableView = function() {
						$scope.formDisabled = 1;
						var saveViewData = {
							action: 'addNewTableView',
							viewName: $scope.tableViewName,
							tableCode: tableCode,
							gridState: gridState,
							savePrivate: $scope.privateView,
							userGroups: $scope.selectedViewGroups
						};

						httpService.request({
							method : "POST",
							data: saveViewData,
							url : "/users.php",
							headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
						}).then(function submitSuccess(response) {
							$scope.formDisabled = 0;
							if(response.data.status){
								$mdToast.show({
									hideDelay   : 5000,
									position    : 'top right',
									controller  : 'ToastCtrl',
									templateUrl : 'tableViewChangeOK.html'
								});
								availableViews.push({viewName :$scope.tableViewName,gridState:gridState});
								logService.clearlog();
								$mdDialog.hide();
							}else{
								logService.setlog(response.data.msg, "error", 0);
							}
						}, function submitError(response) {
							logService.setlog(response, "error", 0);
						});
						
					}
				}
				$scope.parentobj.saveTableView = function(tableCode,gridState,elementId,availableViews){
					$mdDialog.show({
	          				preserveScope: false,
						controller: saveTableViewDialogController,
						templateUrl: 'saveTableView.html',
						parent: angular.element(document.body),
						targetEvent: tableCode,
						clickOutsideToClose: false,
						locals: {
							tableCode: tableCode,
							gridState: gridState,
							parentobj: $scope.parentobj,
							availableViews: availableViews
						},
						multiple: true,
						openFrom: '#'+elementId,
						closeTo: '#'+elementId
					})
					.then(function(tableCode){
						
					}, function() {
				
					});
				}
				$scope.parentobj.deleteSelectedView = function(viewId){
				
				}
				$scope.parentobj.confirmDeleteSelectedView = function(viewId,elementId){
					$mdDialog.show({
						controller: DialogControllerConfirmDelete,
						templateUrl: 'confirmDelete.html',
						parent: angular.element(document.body),
						clickOutsideToClose:true,
						multiple: true
					})
					.then(function(answer) {
						if(answer==='no'){
							showMainformToast('noChangesMade.html', $mdToast);
							$mdDialog.cancel();
						}else if(answer==='yes'){
							$scope.parentobj.deleteSelectedView(viewId);
						}
					}, function() {
						
					});
				}
				$scope.parentobj.nestedFilter = function(searcharray,searchKey,searchVal){
					if(!angular.isArray(searcharray)){
						var objArray = [];
						angular.forEach(searcharray, function(element) {
							objArray.push(element);
						});
						searcharray = objArray;
					}
					var results = {};
					var filterExpression = {};
    					filterExpression[searchKey] = searchVal;
					angular.extend(results, $filter('filter')(searcharray, filterExpression,true));
					if(!results.length){
						angular.forEach(searcharray, function(value, key) {
							if(value.nodes){
								if(value.nodes.length){
									angular.extend(results, $scope.parentobj.nestedFilter(value.nodes,searchKey,searchVal));
								}
							}
						});
					}
					return results;
				}
					
				$scope.parentobj.getBrands = function($scope) {
					if(!$scope.query){
						$scope.query = {
							order: 'onlineInsertTime',
							limit: 10,
							page: 1
						};
					}
					var start = ($scope.query.page - 1) * $scope.query.limit;
					httpService.request({
						method : "GET",
						url : "/brands.php?action=getallbrands",
						headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
					}).then(function submitSuccess(response) {
						$scope.parentobj.pageIsLoading = 0;
						if(response.data.status){
							logService.clearlog();
							$scope.parentobj.privatedata.brandsWithDetails = response.data.brands;
						}else{
							logService.setlog(response.data.msg, "error", 0);
						}
					}, function submitError(response) {
						logService.setlog(response, "error", 0);
					});
					
				};
				
				$scope.parentobj.getCountryList = function($scope) {
					logService.setlog("Getting Country List", "info", 1);
					httpService.request({
						method : "GET",
						url : "/store.php?action=getcountrylist",
						headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
					}).then(function submitSuccess(response) {
						$scope.parentobj.pageIsLoading = 0;
						if(response.data.status){
							logService.clearlog();
							$scope.parentobj.privatedata.brandsWithDetails = response.data.brands;
						}else{
							logService.setlog(response.data.msg, "error", 0);
						}
					}, function submitError(response) {
						logService.setlog(response, "error", 0);
					});
					
				};
			});
app.controller('LeftCtrl', function ($scope, $timeout, $mdSidenav, $log) {
	$scope.close = function () {
		$mdSidenav('mainsidemenuholder').close()
		.then(function () {
			$log.debug("close LEFT is done");
		});
	};
});

function flatListGroupBy(dataList,uniqueTag,groupByTag,outputGroupTag,manipulateTag){
	var tree = [];
	var tree_arrayValue = [];
	if(dataList){
		for (var i = 0, len = dataList.length; i < len; i++) {
			if(tree[dataList[i][uniqueTag]]){
				var arr = {
					[uniqueTag] : dataList[i][groupByTag]
				};
				tree[dataList[i][uniqueTag]][outputGroupTag].push(arr);
			}else{
				tree[dataList[i][uniqueTag]] = dataList[i];
				tree[dataList[i][uniqueTag]][outputGroupTag] = [];
				var arr = {
					[uniqueTag] : dataList[i][groupByTag]
				};
				tree[dataList[i][uniqueTag]][outputGroupTag].push(arr);
			}
			
		}
	}
	
	var array_keys = new Array();
	var array_values = new Array();

	for (var key in tree) {
		if(tree[key]){
			if(tree[key][outputGroupTag]){
				for (var i = 0, len = tree[key][outputGroupTag].length; i < len; i++) {
					if(tree[key][outputGroupTag][i]){
						if(tree[key][outputGroupTag][i][uniqueTag]){
							var arr = {
								[uniqueTag] : tree[key][outputGroupTag][i][uniqueTag],
								[manipulateTag]: tree[tree[key][outputGroupTag][i][uniqueTag]][manipulateTag]
							};
							tree[key][outputGroupTag][i] = arr;
						}
					}
				}
			}
		}
		tree_arrayValue.push(tree[key]);
	}
	return tree_arrayValue;
}
function flatListToTreeViewData(dataList,idTag,parentIdTag,childrenTag) {
	    var tree = [],
	        mappedArr = {},
	        arrElem,
	        mappedElem;
	
	    for (var i = 0, len = dataList.length; i < len; i++) {
	        arrElem = dataList[i];
	        mappedArr[arrElem[idTag]] = arrElem;
	        mappedArr[arrElem[idTag]][childrenTag] = [];
	    }
	
	    for (var id in mappedArr) {
	        if (mappedArr.hasOwnProperty(id)) {
	            mappedElem = mappedArr[id];
	
	            //array of children.
	            if (mappedElem.parent_groupId) {
	                mappedArr[mappedElem[parentIdTag]][childrenTag].push(mappedElem);
	            }else {
	                tree.push(mappedElem);
	            }
	        }
	    }
	    return tree;
}
function confirmDialogClose(mdDialog,clickToSave, cancel, scope=$scope){
	mdDialog.show({
		controller: DialogController2,
		templateUrl: 'confirmClose.html',
		parent: angular.element(document.body),
		clickOutsideToClose:false,
		multiple: true
	})
	.then(function(answer) {
		if(answer==='close'){
			if(angular.isFunction(cancel)){
				cancel();
			}else{
				scope.$eval(cancel);
			}
		}else if(answer==='save'){
			if(angular.isFunction(clickToSave)){
				clickToSave();
			}else{
				scope.$eval(clickToSave);
			}
		}
	}, function() {
			
	});
}
function DialogController2($scope, $mdDialog) {
	$scope.answer = function(answer) {
		$mdDialog.hide(answer);
	};
}
function showMainformToast(template, $mdToast){
		$mdToast.show({
			hideDelay   : 5000,
			position    : 'top right',
			controller  : 'ToastCtrl',
			templateUrl : template
		});
}
app.directive('acceptallzeroasdate', function() {
  return { restrict: 'A',
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {

      if(ngModel) { // Don't do anything unless we have a model

        ngModel.$parsers.push(function (value) {
        	if(moment(value).isValid()){
	        	var formattedDate = moment(value).format('YYYY-MM-DD');
	        	if(formattedDate=="0000-00-00"){formattedDate = '';}
	        	return formattedDate;
	        }else{
	        	return '';
	        }
        });

        ngModel.$formatters.push(function (value) {
        	if(moment(value).isValid()){
			var formattedDate = moment(value).format("YYYY-MM-DD");
			if(formattedDate=="0000-00-00"){formattedDate = '';}
			return formattedDate;
		}else{
			return '';
		}
        });

      }
    }
  };
});
app.controller('ToastCtrl', function($scope, $mdToast) {
      $scope.closeToast = function() {
        $mdToast
          .hide()
          .then(function() {
 
          });
      };
});
app.config(function ($mdDateLocaleProvider) {

    $mdDateLocaleProvider.formatDate = function (date) {
    	if(moment(date).isValid()){
        	var formattedDate = moment(date).format('YYYY-MM-DD');
        	if(formattedDate=="0000-00-00"){formattedDate = '';}
        	return formattedDate;
        }else{
        	return '';
        }
    };
    $mdDateLocaleProvider.parseDate = function(dateString) {
    	if(moment(dateString).isValid()){
	    var m = moment(dateString, 'YYYY-MM-DD', true);
	    return m.isValid() ? m.toDate() : '';
	}else{
		return '';
	}
    };

});

/*
//REPLACING UI-GRID SELECT WITH ANGULAR MATERIAL SELECT
app.run([
  '$templateCache',
  function($templateCache) {

  $templateCache.put('ui-grid/selectionRowHeaderButtons', "<div class=\"ui-grid-selection-row-header-buttons \" ng-class=\"{'ui-grid-row-selected': row.isSelected}\" >" + "<md-checkbox style=\"margin: 0; vertical-align: middle\"  ng-model=\"row.isSelected\" ng-click=\"selectButtonClick(row, $event);row.isSelected=!row.isSelected\"></md-checkbox> &nbsp; </div>");
      $templateCache.put('ui-grid/selectionSelectAllButtons', "<div class=\"ui-grid-selection-row-header-buttons \" ng-class=\"{'ui-grid-all-selected': grid.selection.selectAll}\" ng-if=\"grid.options.enableSelectAll\"> <md-checkbox style=\"margin: 0; vertical-align: middle\" type=\"checkbox\" ng-model=\"grid.selection.selectAll\" ng-click=\"headerButtonClick($event);grid.selection.selectAll=!grid.selection.selectAll\"></md-checkbox> </div>");
  }
]);*/
app.constant('globals', {
	db: undefined,
	roundingMethod: {
		type: 'round',
		exp: -2 
	}
});
app.factory('localDb', ['Loki', '$q', 'globals', '$log', function(Loki, $q, globals, $log) {
	var db = {
		initilizeDb: function(){
			var idbAdapter = new LokiIndexedAdapter('pasaj');
			// use paging only if you expect a single collection to be over 50 megs or so
			var pa = new Loki.LokiPartitioningAdapter(idbAdapter, { paging: false});

			let offlineDb = new Loki('pasaj.db',{
				adapter : idbAdapter,
				autoload: false,
				autosave: false, 
				autosaveInterval: 4000,
				throttledSaves: true
			});
			this.createAllTables(["items", "loggedinUser", "customers", "salesHeader", "functions", "functionGroup", "tillSettings", "tillOperation", "tableMaps", "dataToExport", "storeUsers", "paymentMethods", "tableView", "freeTable", "storesDetail", "groupsDetail", "functionRestrictions", "tillsDetail", "promotionsDetail", "markdownsDetail", "dynaPages", "dynaPageMenu"], offlineDb);
			return $q(function (resolve, reject) {
				offlineDb.loadDatabase({}, function(result) {
					globals.db = offlineDb;
					resolve(offlineDb);
				});
			});
		},
		createAllTables: function(tableNames, offlineDb){
			for(var i=0; i<tableNames.length;i++){
				this.createTables(tableNames[i], offlineDb);
			}
		},
		createTables: function(tableName, offlineDb){
			let table = {};
			switch(tableName){
				case 'items':
					table = offlineDb.addCollection('items', {
						unique: ['itemsDetail_itemBarcode'],
						indices: [] 
					});
				break;
				case 'loggedinUser':
					table = offlineDb.addCollection('loggedinUser', {
						unique: ['usersDetail_userId'],
						indices: ['pinCode'] 
					});
				break;
				case 'customers':
					table = offlineDb.addCollection('customers', {
						unique: ['usersDetail_userId'],
						indices: ['usersDetail_cellphone'] 
					});
				break;
				case 'salesHeader':
					table = offlineDb.addCollection('salesHeader', {
						unique: ['salesHeader_offlineSalesHeaderId'],
						indices: ['salesHeader_offlineReceiptNumber'] 
					});
				break;
				case 'storesDetail':
				    table = offlineDb.addCollection('storesDetail', {
						unique: ['storesDetail_storeId'],
						indices: ['storesDetail_storeCode'] 
					});
				break;
				case 'functions':
					table = offlineDb.addCollection('functions', {
						unique: ['dynaPageFunction_functionId'],
						indices: [] 
					});
				break;
				case 'functionRestrictions':
				    table = offlineDb.addCollection('functionRestrictions', {
						unique: ['dynaFunctionRestrictions_restrictionId'],
						indices: [] 
					});
				break;
				case 'functionGroup':
					table = offlineDb.addCollection('functionGroup', {
					});
				break;
				case 'settings':
					table = offlineDb.addCollection('settings', {
						unique: ['id'],
						indices: [] 
					});
				break;
				case 'tillSettings':
					table = offlineDb.addCollection('tillSettings', {
						unique: ['tillsDetail_tillId']
					});
				break;
				case 'promotionsDetail':
				    table = offlineDb.addCollection('promotionsDetail', {
						unique: ['promotionsDetail_promotionId']
					});
				break;
				case 'markdownsDetail':
				    table = offlineDb.addCollection('markdownsDetail', {
						unique: ['markdownsDetail_markdownId']
					});
				break;
				case 'paymentMethods':
					table = offlineDb.addCollection('paymentMethods', {
						unique: ['paymentMethods_methodId']
					});
				break;
				
				case 'tillOperation':
					table = offlineDb.addCollection('tillOperation', {
						unique: ['operationId'],
						indices: ['operationType'] 
					});
				break;
				case 'tableView':
					table = offlineDb.addCollection('tableView', {
						unique: ['tableViewDetail_viewId'],
						indices: ['tableViewDetail_tableCode'] 
					});
				break;
				case 'freeTable':
					table = offlineDb.addCollection('freeTable', {
						unique: ['dynaPageFreeTable_propertyCode'],
						indices: ['dynaPageFreeTable_categoryCode'] 
					});
				break;
				case 'tillsDetail':
				    table = offlineDb.addCollection('tillsDetail', {
						unique: ['tillsDetail_tillId'],
						indices: [] 
					});
				break;
				case 'tableMaps':
					table = offlineDb.addCollection('tableMaps', {
						unique: ['dynaPageTableMap_localTable'],
						indices: [] 
					});
				break;
				case 'dataToExport':
					table = offlineDb.addCollection('dataToExport', {
						unique: ['dataId'],
						indices: ['serverGroupName'] 
					});
				break;
				case 'storeUsers':
					table = offlineDb.addCollection('storeUsers', {
						unique: ['usersIdstoresId_userId'],
						indices: ['usersIdstoresId_storeId'] 
					});
				break;
				case 'groupsDetail':
					table = offlineDb.addCollection('groupsDetail', {
						unique: ['groupsDetail_groupId'],
						indices: [] 
					});
				break;
				case 'dynaPages':
				    table = offlineDb.addCollection('dynaPages', {
						unique: ['dynaPageDetails_pageId'],
						indices: [] 
					});
				break;
				case 'dynaPageMenu':
			        table = offlineDb.addCollection('dynaPageMenu', {
						unique: ['dynaPageMenu_menuCode'],
						indices: ['dynaPageMenu_pageId'] 
					});
				break;
			}
			return table;
		},
		getTable: function(tableName, offlineDb){
			let table = {};
			table = offlineDb.getCollection(tableName);
			if(!table && tableName){
				table = this.createTables(tableName, offlineDb);
			}
			return table;
		},
		clearTable: function(tableName, table=undefined){
			if(!table && tableName){
				let table = {};
				table = this.getTable(tableName);
			}
			table.chain().remove();
			table.removeDataOnly();
			return table;
		},
		insertItem: function(tableName, itemsToInsert=[], table=undefined){
		    try{
                let result = {};
    			if(itemsToInsert.length){
        			if(!table && tableName){
        				table = this.getTable(tableName);
        			}
        			result = table.insert(itemsToInsert);
    			}
    			return {result: result, status: 1};
		    }
			catch(e){
			    $log.error("error when inserting in: "+tableName);
			    $log.error(itemsToInsert);
			    $log.error(e);
			    return ({result: {}, status: false});
			}
		},
		updateItem: function(tableName, itemsToUpdate=[], table=undefined){
			try {
			    let result = {};
				if(itemsToUpdate.length){
					if(!table && tableName){
						let table = {};
						table = this.getTable(tableName);
					}
					result = table.update(itemsToUpdate);
				}
                return {result: result, status: 1};
			}
			catch(e) {
				$log.error("error when updating in: "+tableName);
				$log.error(itemsToUpdate);
			    $log.error(e);
			    return ({result: {}, status: false});
			}
		},
		deleteItem: function(tableName, itemsToDelete=[], table=undefined){
			try {
			    let result = {};
				if(itemsToDelete.length){
					if(!table && tableName){
						let table = {};
						table = this.getTable(tableName);
					}
					for (var i = 0; i < itemsToDelete.length; i++) {
                		result[i] = table.removeWhere(itemsToDelete[i]);
                	}
				}
                return {result: result, status: 1};
			}
			catch(e) {
				$log.error("error when deleting in: "+tableName);
				$log.error(itemsToDelete);
			    $log.error(e);
			    return ({result: {}, status: false});
			}
		},
		selectItem: function(tableName,key_value,limit=1, table=undefined, parameters=undefined, offlineDb= undefined, debug=0){
			if(!table && tableName && offlineDb){
				table = {};
				table = this.getTable(tableName, offlineDb);
			}
			let searchTerm = {};
			let result = {};
			if(limit==1 && !parameters){
				result =  table.findOne(key_value);
			}else if(parameters){
				result =  table.chain();
				if(parameters.sortBy){
					let sortBy = parameters.sortBy;
					if(sortBy.isdesc){
						result = result.simplesort(sortBy.column, true);
					}else{
						result = result.simplesort(sortBy.column);
					}
				}
				if(parameters.limit){
					result = result.limit(parameters.limit);
				}
				result = result.data();
			}
			return result;
		},
		advancedSelectItem: function(tableName, searchCondition, offlineDb, parameters={}, table=undefined, debug=0){
			if(!table && tableName && db){
				table = {};
				table = this.getTable(tableName, offlineDb);
			}

			var results = table.chain().find(searchCondition);
			if(parameters.sortBy){
				if(!parameters.sortIsDesc){
					parameters.sortIsDesc = false;
				}
				results = results.simplesort(parameters.sortBy, parameters.sortIsDesc);
			}
			if(parameters.start){
				results = results.offset(parameters.start);
			}
			if(parameters.limit){
				results = results.limit(parameters.limit);
			}
			results = results.data();
			return results;
		},
		saveDb: function(offlineDb){
			return $q(function (resolve, reject) {
				try {
					return offlineDb.throttledSaveDrain(function () {
						return offlineDb.saveDatabase(function(msg){
							resolve({status: 1, msg: msg});
						});
					});
				}
				catch(err) {
					reject({status:0, msg: err.message});
				}
			});
		}
	}
	return db;
}]);
app.service('externalDevice', function(httpService, $q, localDb, localStorageService, globals){
	this.sendData = function(data,device){
		return $q(function (resolve, reject) {
			if(device=="LINE_DISPLAY"){
				var editorExtensionId = "mcjfolflacfgepphdebmdkdldfldlijg";

				// Make a simple request:
				if(chrome && chrome.runtime){
				chrome.runtime.sendMessage(editorExtensionId, {
					data: data,
					port: "COM3"
				},  
				function (response){
					resolve(response);
				});
				}else{
					resolve("not chrome");
				}
			}
		});
	}
});
app.service('gridsterService', function (lodash) {
    this.initGridster = function(content={}){
        if(!content){
			content = {};
		}
		if(!content.options){
			content.options = {};
			content.options.customCss = "";
		}
			
		content.options.draggable = {enabled: false};
		content.options.resizable = {enabled: false};		
		content.options.disablePushOnDrag = true;
		content.options.disablePushOnResize = true;
		content.options.pushItems = false;
		content.options.swap = false;
		if(!content.screens || !lodash.size(content.screens)){
			content.screens = [
				{
					items: [],
					screenId: "main",
					screenName: "main"
				}
			];
		}
		return content;
    }
});

app.service('errorHandling', function(httpService, $q, $log, localDb, localStorageService, globals){
	this.saveError = function(table='',type='', retryTime=0,item=[],error=''){
		let errorArray = {
			table: table,
			type: type,
			retryTime: retryTime,
			item: item,
			error: error
		}
		$log.error(errorArray);
	}
});

app.config(function($logProvider) {
	$logProvider.debugEnabled(true);
});
app.service('menuService', function(localDb, logService, lodash){
    this.customizedMerge = function(mainElement, element, property='dynaPageMenu_menuCode', nodeProperty='nodes') {
        let condition = {};
        condition[property] = element[property];
        let i = lodash.findIndex(mainElement, condition);
        if(i>=0){
            if(element[nodeProperty] && element[nodeProperty][0]){
                if(mainElement[i][nodeProperty]){
                    mainElement[i][nodeProperty] = this.customizedMerge(mainElement[i][nodeProperty], element[nodeProperty][0], property, nodeProperty);
                }else{
                    mainElement[nodeProperty] = [];
                    mainElement[nodeProperty].push(element[nodeProperty][0]);
                }
            }
        }else{
            mainElement.push(element);
        }
        return mainElement;
    }
    this.createTree = function(element, all, parent_property='dynaPageMenu_parentCode', unique_Property='dynaPageMenu_menuCode', nodeProperty='nodes'){
        let formattedElement = {};
        if(element[parent_property]){
            let condition = {}
            condition[unique_Property] = element[parent_property];
            let parentElementIndex = lodash.findIndex(all, condition);
            if(parentElementIndex >= 0){
                let parentElement = angular.copy(all[parentElementIndex]);
                parentElement[nodeProperty] = [element];
                formattedElement = this.createTree(parentElement, all, parent_property, unique_Property, nodeProperty);
            }else{
                formattedElement = angular.copy(element);
            }
        }else{
            formattedElement = angular.copy(element);
        }
        return formattedElement;
    }
    this.formatMenu = function(rawMenu, allPageMenus){
        logService.setlog(["Formatting Menu", {rawMenu:rawMenu, allPageMenus:allPageMenus}], "debug");
        let menu = [];
        
        for(let i=0; i<rawMenu.length; i++){
            let newElement = this.createTree(rawMenu[i], allPageMenus);
            if(newElement){
                menu = this.customizedMerge(menu, newElement);
            }
        }
        logService.setlog(["Formatted Menu", {menu:menu}], "debug");
        return menu;
    }
    this.getAllowedMenus = function(userGroups=[], groupProperty=''){
        logService.setlog(["Getting allowed Menus for groups", {userGroups: userGroups, groupProperty: groupProperty}], "debug");
        let menu = [];
        if(groupProperty && userGroups && userGroups[0] && userGroups[0][groupProperty]){
            userGroups = userGroups.map(group => group[groupProperty]);
        }
        userGroups.push("");
        return localDb.initilizeDb().then(function(db){
            let dynaPages_searchCondition = {
                '$and': [
                    {
                        'dynaPageData_usersGroupId.dynaPageData_usersGroupId_groupId' : { '$in' : userGroups }
                    },
                    {
                        'dynaPageDetails_pageId': { '$ne' : '' }
                    }
                ]
            };
            logService.setlog(["Searching for Allowed Pages in dynaPages", {dynaPages_searchCondition: dynaPages_searchCondition, db: db}], "debug");
            let allowedPages = localDb.advancedSelectItem('dynaPages', dynaPages_searchCondition, db);
            logService.setlog(["Allowed Pages", allowedPages], "debug");
            if(allowedPages.length){
                let allowedPageIds = allowedPages.map(page => page.dynaPageDetails_pageId);
                let dynaPagesMenu_searchCondition = {
                    'dynaPageMenu_pageId' : { '$containsAny' : allowedPageIds }
                };
                logService.setlog(["Searching for Allowed Menus in dynaPageMenu", {dynaPagesMenu_searchCondition: dynaPagesMenu_searchCondition, db: db}], "debug");
                let allowedMenus = localDb.advancedSelectItem('dynaPageMenu', dynaPagesMenu_searchCondition, db);
                logService.setlog(["Allowed Menus for user", {allowedPageIds:allowedPageIds, allowedMenus:allowedMenus}], "debug");
                if(allowedMenus.length){
                    let dynaPageMenu_searchCondition = {
                    };
                    let allPageMenus = localDb.advancedSelectItem('dynaPageMenu', dynaPageMenu_searchCondition, db);
                    for(let j=0; j<allPageMenus.length; j++){
                        if(allPageMenus[j].dynaPageMenu_pageId){
                            let correspondingPage = allowedPages.find(page => page.dynaPageDetails_pageId === allPageMenus[j]['dynaPageMenu_pageId']);
                            if(correspondingPage){
                                allPageMenus[j]['dynaPageDetails_availableOffline'] = correspondingPage.dynaPageDetails_availableOffline;
                                allPageMenus[j]['dynaPageDetails_accessWithoutLogin'] = correspondingPage.dynaPageDetails_accessWithoutLogin;
                            }else{
                                allPageMenus.splice(j, 1);
                                j = j - 1;
                            }
                        }else{
                            allPageMenus[j]['dynaPageDetails_availableOffline'] = 1;
                            allPageMenus[j]['dynaPageDetails_accessWithoutLogin'] = 0;
                        }
                    }
                    menu = this.formatMenu(allowedMenus, allPageMenus);
                }
            }
            return menu;
        }.bind(this));
    }
});
app.service('receiptTemplatesService', function(httpService, localDb, localStorageService, globals, logService){
	this.getTillReceiptTemplates = function(storeId= '', tillId= ''){
		if(!storeId && tillId){
			let tillDetails = localStorageService.get("tillDetails_"+tillId);
			if(tillDetails && tillDetails.storesDetail_storeId){
				storeId = tillDetails.storesDetail_storeId;
			}
		}
		if(storeId){
			if(!globals.db){
				return localDb.initilizeDb().then(function(db){
					let receiptTemplatesDb = localDb.getTable('receiptTemplates', db);
					return this.downloadReceiptTemplates(storeId, receiptTemplatesDb, db);
				}.bind(this));
			}else{
				let receiptTemplatesDb = localDb.getTable('receiptTemplates', globals.db);
				return this.downloadReceiptTemplates(storeId, receiptTemplatesDb, globals.db);
			}
		}
	}
	this.downloadReceiptTemplates = function(storeId, receiptTemplatesDb, db){
		var requestTime = moment.utc().format("YYYY-MM-DD HH:mm:ss");
		let properties = {
			"simple": ["storesIdreceiptsId_storeId","storesIdreceiptsId_receiptType","receiptsDetail_receiptName","receiptsDetail_receiptData"],
			"concat": [{'columns':["storesIdreceiptsId.storeId","storesIdreceiptsId.receiptId"], 'name':"storesIdreceiptsId_storeId_receiptId"}]
		};
		let onlineInsertTime = "";
		
		if(localStorageService.get('lastReceiptTemplateDownload')){
				onlineInsertTime = localStorageService.get('lastReceiptTemplateDownload');
		}else{
			onlineInsertTime = "";
		}
		let allReceiptTemplatesCount = receiptTemplatesDb.data.length;
		var receiptTemplatesDownloadParameters = {
			action: 'searchReceiptTemplates',
			storesId: [{'storesDetail_storeId': storeId}],
			getcount: 0,
			getheaders: 0,
			getAllowedReceiptsViews: 0,
			getAllowedReceiptsViews: 0,
			onlineInsertTime_start: onlineInsertTime,
			properties: properties
		};
		if(onlineInsertTime == ""){
			receiptTemplatesDownloadParameters.status = 1;
		}
		return httpService.request({
			method : "POST",
			url : "/receipts.php",
			data: receiptTemplatesDownloadParameters,
			headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
		}).then(function submitSuccess(response) {
			if(response.data.status){
				if(!response.data.partial){
					logService.setlog("Re-saving whole receipt template data", "info", 1);
					receiptTemplatesDb.chain().remove();
					receiptTemplatesDb.removeDataOnly();
								
					var receiptTemplates = receiptTemplatesDb.insert(
						response.data.receipts.rows
					);
				}else{
					logService.setlog("Updating new receipt template data", "info", 1);
					var i,arrLen = response.data.receipts.rows.length;
					var receiptsToUpdate = [];
					var receiptsToInsert = [];
					var receiptsToDelete = [];
					for (i = 0; i < arrLen; i++) {
						let oldRecord = receiptTemplatesDb.findOne({storesIdreceiptsId_storeId_receiptId:response.data.receipts.rows[i]['storesIdreceiptsId_storeId_receiptId']});
						if(oldRecord){
							if(response.data.receipts.rows[i]['receiptsStatus_status'] == 0 || response.data.receipts.rows[i]['receiptsStatus_status'] == '0'){
								receiptsToDelete.push({storesIdreceiptsId_storeId_receiptId:oldRecord['storesIdreceiptsId_storeId_receiptId']});
							}else{
								angular.extend(oldRecord, response.data.receipts.rows[i]);
								receiptsToUpdate.push(oldRecord);
							}
						}else{
								
							receiptsToInsert.push(response.data.receipts.rows[i]);
						}
					}
	
					if(receiptsToUpdate.length){
						receiptTemplatesDb.update(receiptsToUpdate);
					}
					if(receiptsToInsert.length){
						receiptTemplatesDb.insert(receiptsToInsert);
					}
					if(receiptsToDelete.length){
						for (i = 0; i < receiptsToDelete.length; i++) {
							receiptTemplatesDb.removeWhere(receiptsToDelete[i]);
						}
					}
				}
				db.throttledSaveDrain(function () {
					db.saveDatabase(function(){
						localStorageService.set('lastReceiptTemplateDownload', requestTime);
					});
				});
				logService.clearlog();
					
				return ({status: 1, msg: '', receiptTemplatesDb: receiptTemplatesDb, storeId: storeId});
			}else{
				logService.setlog(response.data.msg, "error",0);
				return ({status: 0, msg: response.data.msg, receiptTemplatesDb: {}});
			}
		}, function submitError(response) {
			logService.setlog(response, "error",0);
			return ({status: 0, msg: response, receiptTemplatesDb: {}});
		});
	}
});
app.service('logService', function(lodash, $log){
    return {
        setlog: setlog,
        clearlog: clearlog
    };
    function setlog(msg='', type='info', pageIsLoading=0, showToUser=1) {
        //type: info, debug, error, warning
        $log.debug(msg);
    }
    function clearlog(){
        
    }
});
app.service('tableMapsService', function(httpService, $filter, $q, $timeout, $log, localDb, localStorageService, logService, guid, globals, lodash, $rootScope, onlineStatus){
	var exportedDataRow;
	var uniqueProperties = {};
	this.readTableMap = function(tableMapData){
		var tableMap = tableMapData;
		for(var i=0; i<tableMapData.length; i++){
			if(typeof tableMapData[i].dynaPageTableMap_columnMap === 'string'){
				tableMap[i].dynaPageTableMap_columnMap = lodash.attempt(JSON.parse.bind(null, tableMapData[i].dynaPageTableMap_columnMap));
			}
		}
		return tableMap;
	},
	this.getTableMaps = function(localTableName=[], inputDb=undefined){
	    if(inputDb && !globals.db){
	        globals.db = inputDb;
	    }
		if(!globals.db){
			return localDb.initilizeDb().then(function(db){
				let tableMapsDb = localDb.getTable('tableMaps', db);
				if(localTableName.length && onlineStatus.isOnline()){
					return this.downloadTableMaps(localTableName, tableMapsDb, db);
				}else{
					let res = {status: 1, msg: '', localTableName: localTableName, tableMapsDb: tableMapsDb, db:db};
					return res;
				}
			}.bind(this));
		}else{
			let tableMapsDb = localDb.getTable('tableMaps', globals.db);
			if(localTableName.length && onlineStatus.isOnline()){
				return this.downloadTableMaps(localTableName, tableMapsDb, globals.db);
			}else{
			    return $q(function (resolve, reject) {
    				let res = {status: 1, msg: '', localTableName: localTableName, tableMapsDb: tableMapsDb, db:globals.db};
    				resolve(res);
			    });
			}
		}
	},
	this.downloadTableMaps = function(localTableName=[], tableMapsDb, db){
		var requestTime = moment.utc().format("YYYY-MM-DD HH:mm:ss");
		var that = this;
		let onlineInsertTime = {};
		for(var i=0; i<localTableName.length; i++){
			if(localStorageService.get('lastTableMapDownload_'+localTableName[i])){
				onlineInsertTime[localTableName[i]] = localStorageService.get('lastTableMapDownload_'+localTableName[i]);
			}else{
				onlineInsertTime[localTableName[i]] = "";
			}
		}
			
		let properties = {
			"columnMap": 1
		};
		var tableMapDownloadParameters = {
			action: 'getTableMap',
			properties: properties,
			localTable: localTableName,
			onlineInsertTime_start: onlineInsertTime
		};
		return httpService.request({
			method : "POST",
			url : "/dynaPage.php",
			data: tableMapDownloadParameters,
			headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
		}).then(function submitSuccess(response) {
			if(response.data.status){
				var tableMapsToInsert = [];
				var tableMapsToUpdate = [];
				var tableMapsToDelete = [];
				if(response.data.tableMapDetails){
					let tableMapDetails = response.data.tableMapDetails;
					tableMapDetails = that.readTableMap(tableMapDetails);
					for(var i=0; i<tableMapDetails.length; i++){
						let oldRecord = tableMapsDb.findOne({dynaPageTableMap_localTable:tableMapDetails[i]['dynaPageTableMap_localTable']});
						if(oldRecord){
							if(tableMapDetails[i]['dynaPageTableMap_status'] == 0 || tableMapDetails[i]['dynaPageTableMap_status'] == '0'){
								tableMapsToDelete.push({dynaPageTableMap_id: oldRecord['dynaPageTableMap_id']});
							}else{
								lodash.merge(oldRecord, tableMapDetails[i]);
								tableMapsToUpdate.push(oldRecord);
							}
						}else{
							tableMapsToInsert.push(tableMapDetails[i]);
						}
					}
	
					if(tableMapsToUpdate.length){
						tableMapsDb.update(tableMapsToUpdate);
					}
					if(tableMapsToInsert.length){
						tableMapsDb.insert(tableMapsToInsert);
					}
					if(tableMapsToDelete.length){
						for (i = 0; i < tableMapsToDelete.length; i++) {
							tableMapsDb.removeWhere(tableMapsToDelete[i]);
						}
					}
					db.throttledSaveDrain(function () {
						db.saveDatabase(function(){
							for(var i=0; i<tableMapDetails.length; i++){
								localStorageService.set('lastTableMapDownload_'+tableMapDetails[i]['dynaPageTableMap_localTable'], requestTime);
							}
						});
					});
				}
				logService.clearlog();
					
				return ({status: 1, msg: '', localTableName: localTableName, tableMapsDb: tableMapsDb, db:db});
			}
		}, function submitError(response) {
			logService.setlog(response, "error",0);
			return ({status: 0, msg: response, localTableName: localTableName, tableMapsDb: {}, db:db});
		});
	},
	this.importDataFromServer = function(db, localTables, allowedTries=3, tried=0, lastResponse='', options={}){
		var that = this;
		var localTable;
		var saveToDatabase = 1;
		if(options.saveToDatabase !== undefined){
		    saveToDatabase = options.saveToDatabase;
		}
		return $q(function (resolve, reject) {
			if(Array.isArray(localTables) && localTables[0]){
				localTable = localTables[0];
			}else{
				localTable = localTables;
			}

			if(localTable && onlineStatus.isOnline()){
			    try{
    				localTable = localTable.dynaPageInitVariables_initialVal;
    				var localTableName = localTable.localTableName || "";
    				var postParameters = localTable.postParameters || {};
    				var dataField = localTable.dataField || "result";
    				var responseStatusField = localTable.responseStatusField || "status";
    				var needDataTransformation = localTable.needDataTransformation || "hard";
    				var partialOrFullField = localTable.partialOrFullField || "fullRefresh";
    				var url = localTable.url || "/dynaPage.php";
    				let sentReqDebugMsg = {
    				    url: url,
    				    postParameters: postParameters
    				}
    				logService.setlog(["Here is the request sent to server for "+localTableName, sentReqDebugMsg], "debug");
    				if(tried<allowedTries) {
    					return httpService.request({
    						method : "POST",
    						url : url,
    						data: postParameters,
    						headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
    					}).then(function submitSuccess(response) {
    					    logService.setlog(["Here is the response received from server for "+localTableName, response], "debug");
    					    let importDb;
    					    if(saveToDatabase){
    						    importDb = localDb.getTable(localTableName, db);
    					    }
    						let data = response.data;
    						if(lodash.get(data, responseStatusField)){
    							let recordsToInsert = [];
    							let recordsToUpdate = [];
    							let recordsToDelete = [];
    							let fullDataUpdate = lodash.get(data, partialOrFullField, 0);
    							
    							let tableMapDb = localDb.getTable('tableMaps', db);
    							
    							let tableMap = tableMapDb.findOne({dynaPageTableMap_localTable: localTableName});
    							if(!tableMap){
    							    logService.setlog(["could not find requested map "+localTableName, {tableMapDb: tableMapDb}], "warning");
    							}
    							let columnMap = lodash.get(tableMap, 'dynaPageTableMap_columnMap', undefined);
    							
    							logService.setlog(["Here is the data will be used to decode received response for "+localTableName, {columnMap: columnMap, 'dataField name for data': dataField, 'raw data': data, needDataTransformation: needDataTransformation, 'tableMap to get dynaPageTableMap_columnMap as columnMap': tableMap, 'tableMapDb to look for tableMap by dynaPageTableMap_localTable=localTableName': tableMapDb}], "debug");
    							
    							if(typeof columnMap === 'string'){
    								columnMap = lodash.attempt(JSON.parse.bind(null, columnMap));
    							}else if(typeof columnMap === 'object'){
    								columnMap = columnMap;
    							}
    							let rows;
    							
    							if(columnMap && columnMap[0]){
    								let mapDetails = columnMap[0];
    								let uniquefields = mapDetails.columnsMap.filter(item => item.index == "unique");
    								let statusFields = mapDetails.columnsMap.filter(item => item.index == "status");
    								
    								let dbLength;
    								if(saveToDatabase){
    								    dbLength = importDb.chain().data().length;
    								}
    								
    								if(needDataTransformation=='hard'){
    									rows = that.processDataForImport(data, columnMap, dataField);
    									logService.setlog(["Here is the hard decoded response for "+localTableName, rows], "debug");
    								}else if(needDataTransformation=='soft'){
    									let formattedRows = lodash.get(data, dataField, []);
    									rows = that.formatDataForImport_soft(formattedRows);
    								}else{
    									rows = lodash.get(data, dataField, []);
    								}
    								
    								if(fullDataUpdate && saveToDatabase){
    									importDb.chain().remove();
    									importDb.removeDataOnly();
    								}
    								
    								if(saveToDatabase){
        								let preparedRecords = that.prepareRecordsForLocalTable(rows, uniquefields, statusFields, columnMap, importDb);
        								recordsToInsert = preparedRecords.recordsToInsert;
        								recordsToUpdate = preparedRecords.recordsToUpdate;
        								recordsToDelete = preparedRecords.recordsToDelete;
    							    }
    							}else if(needDataTransformation){
    							    logService.setlog(["needDataTransformation was set to "+needDataTransformation+", but columnMap is not defined for "+localTableName,{columnMap: columnMap, 'dataField name for data': dataField, 'raw data': data, needDataTransformation: needDataTransformation, 'tableMap to get dynaPageTableMap_columnMap as columnMap': tableMap, 'tableMapDb to look for tableMap by dynaPageTableMap_localTable=localTableName': tableMapDb}], "warning");
    							}
    							let databaseChanged = 0;
    							
    							if(recordsToInsert && recordsToInsert.length){
    								importDb.insert(recordsToInsert);
    								databaseChanged = 1;
    							}
    							if(recordsToUpdate && recordsToUpdate.length){
    								importDb.update(recordsToUpdate);
    								databaseChanged = 1;
    							}
    							if(recordsToDelete && recordsToDelete.length){
    								for (var i = 0; i < recordsToDelete.length; i++) {
    									importDb.removeWhere(recordsToDelete[i]);
    								}
    								databaseChanged = 1;
    							}
    							
    							if(databaseChanged){
        							db.throttledSaveDrain(function () {
        								db.saveDatabase(function(){
        									resolve({status: 1, msg: '', importDb: importDb, db: db, response: response.data});
        								});
        							});
    							}else{
    							    resolve({status: 1, msg: '', importDb: {}, db: db, response: response.data, rows: rows, columnMap: columnMap});
    							}
    						}else{
    						    $log.error(response);
    						}
    					}, function submitError(response) {
    						logService.setlog(response, "error", 0);
    						tried = tried + 1;
    						that.importDataFromServer(db, localTable, allowedTries, tried, response, options);
    					});
    				}else{
    					reject({status: 0, msg: lastResponse, importDb: importDb, db: db, response: {}});
    				}
    			}
    			catch(e){
    			    $log.error(e);
    			    $log.error("Error while importing data");
    			    $log.error(localTable);
    			}
			}else{
				resolve({status: 1, msg: '', importDb: {}, db: db, response: {}});
			}
		});
	},
	this.prepareRecordsForLocalTable = function(rows, uniquefields, statusFields, columnMap, table, needDataTransformation='hard'){
	    let recordsToDelete = [];
	    let recordsToInsert = [];
        let recordsToUpdate = [];
	    for(var i=0; i<rows.length; i++){
        	let oldRecord = null;
        	let searchCondition = {};
        	for(var j=0; j<uniquefields.length; j++){
        		searchCondition[uniquefields[j]['localColumn']] = rows[i][uniquefields[j]['import']['serverColumn']];
        	}
        	if(lodash.size(searchCondition)){
        		oldRecord = table.findOne(searchCondition);
        	}
			
        	if(oldRecord){
        		let deleteRecord = 0;
        		for(var t=0; t<statusFields.length; t++){
        			let statusField = statusFields[t]['import']['serverColumn'];
        			let deleteStatusObject = statusFields[t].rules.find(item => item.name == "deleteStatus");
        			deleteStatus = 0;
        			if(deleteStatusObject){
        			    deleteStatus = deleteStatusObject.value || 0;
        			}
        			if(statusField && rows[i][statusField] == deleteStatus){
        				deleteRecord = 1;
        			}
        		}
        		if(deleteRecord){
        			recordsToDelete.push(searchCondition);
        		}else{
        			if(needDataTransformation=='hard'){
        				let a = angular.copy(oldRecord);
        				oldRecord = this.merge([oldRecord], [rows[i]], columnMap)[0];
        			}else{
						lodash.merge(oldRecord, rows[i]);
        			}
        			recordsToUpdate.push(oldRecord);
        		}
        	}else{
        		recordsToInsert.push(rows[i]);
			}
		}
		return {recordsToInsert: recordsToInsert, recordsToUpdate: recordsToUpdate, recordsToDelete: recordsToDelete};
	}
	this.merge = function(oldRecord, newRecord, tableMap, columnsMap=undefined, mapId = undefined){ // Our merge function
		if(lodash.isEqual(oldRecord, newRecord) || !newRecord || !newRecord.length){
			return oldRecord;
		}
		if(!columnsMap){
			columnsMap = tableMap[0].columnsMap;
		}
		
		if(!mapId){
			mapId = tableMap[0].mapId;
		}

		var result = []; // return result
		
		let uniqueColumnNames = columnsMap.filter(item => item.index == "unique");
		let statusColumnNames = columnsMap.filter(item => item.index == "status");

		for(var counter=0; counter<newRecord.length; counter++){
			let foundOldRow = null;
			let searchCondition = {};
			let resultIndex = result.length;
			let foundOldRowIndex = -1;
			
			for(var i=0; i<uniqueColumnNames.length; i++){
				searchCondition[uniqueColumnNames[i]['localColumn']] = newRecord[counter][uniqueColumnNames[i]['localColumn']];
			}
			if(lodash.size(searchCondition)){
				foundOldRowIndex = lodash.findIndex(oldRecord, searchCondition);
			}
			
			let deleteRecord = 0;
			for(var i=0; i<statusColumnNames.length; i++){
				let statusField = statusColumnNames[i]['localColumn'];
				let deleteStatusObject;
				if(statusColumnNames[i].rules){
					deleteStatusObject = statusColumnNames[i].rules.find(item => item.name == "deleteStatus");
				}else{
					deleteStatusObject = {};
				}
				let deleteStatus = 0;
				if(deleteStatusObject){
				    deleteStatus = deleteStatusObject.value || 0;
				}
				if(statusField && newRecord[counter][statusField] == deleteStatus){
					deleteRecord = 1;
				}
			}
			
			if(deleteRecord){
				continue;
			}
			if(foundOldRowIndex >-1){
			    for(var columnName in oldRecord[foundOldRowIndex]){      // for every property in obj1 
			        if((columnName !== null) && (columnName in newRecord[counter]) && (typeof oldRecord[foundOldRowIndex][columnName] === "object" || Array.isArray(oldRecord[foundOldRowIndex][columnName]))){
			        	if(typeof oldRecord[foundOldRowIndex][columnName] === "object" && oldRecord[foundOldRowIndex][columnName].constructor === Object){
			        		if(!result[resultIndex]){
							result[resultIndex] = {}
						}
						result[resultIndex][columnName] = lodash.merge(oldRecord[foundOldRowIndex][columnName],newRecord[counter][columnName]);
					}else if(Array.isArray(oldRecord[foundOldRowIndex][columnName])){
						if(!result[resultIndex]){
							result[resultIndex] = {}
						}
						if(!result[resultIndex][columnName]){
							result[resultIndex][columnName] = [];
						}
                        
						let currentMap = tableMap.find(map=>map.mapId == mapId);
						if(currentMap.collectionsMap){
							let collectionMapIdHolder = currentMap.collectionsMap.find(collection=>collection.localColumn == columnName);
							if(collectionMapIdHolder){
							    let collectionMapId = collectionMapIdHolder.mapId;
							
							    let collectionColumnsMap = tableMap.find(map=>map.mapId == collectionMapId).columnsMap;
							    result[resultIndex][columnName] = this.merge(oldRecord[foundOldRowIndex][columnName], newRecord[counter][columnName], tableMap, collectionColumnsMap, collectionMapId); // if it's an array
							}else{
    							let currentColumnColumnsMap = currentMap.columnsMap.find(column=>column.localColumn == columnName);
    							if(currentColumnColumnsMap.rules && currentColumnColumnsMap.rules.find(rule=>rule.name == "type" && rule.value == "jsonObject")){
    								result[resultIndex][columnName] = newRecord[counter][columnName];
    							}
    						}
						}else{
							let currentColumnColumnsMap = currentMap.columnsMap.find(column=>column.localColumn == columnName);
							if(currentColumnColumnsMap.rules && currentColumnColumnsMap.rules.find(rule=>rule.name == "type" && rule.value == "jsonObject")){
								result[resultIndex][columnName] = newRecord[counter][columnName];
							}
						}
					}
			        }else if(newRecord[counter] && (columnName !== null) && (columnName in newRecord[counter])){
			        	if(!result[resultIndex]){
						result[resultIndex] = {}
					}
			        	result[resultIndex][columnName] = newRecord[counter][columnName]; // add it to result
			        }else if(columnName !== null){
			        	if(!result[resultIndex]){
						result[resultIndex] = {}
					}
			        	result[resultIndex][columnName] = oldRecord[foundOldRowIndex][columnName]; // add it to result
			        }
			    }
			}
			for(columnName in newRecord[counter]){ // add the remaining properties from object 2
				if(result[resultIndex] && (columnName in result[resultIndex])){ //conflict
					continue;
				}
				if(!result[resultIndex]){
					result[resultIndex] = {}
				}
				result[resultIndex][columnName] = newRecord[counter][columnName];
			}
		}
		
		return result;
	}
	this.processDataForImport = function(data, tableMap, dataField){
		var formattedData = [];
		var mainMapId = tableMap[0].mapId;
		
		this.formatDataForImport(data, tableMap, mainMapId , dataField, formattedData);
		return formattedData;
	},
	this.formatDataForImport = function(data, tableMap, mapId, dataField, formattedData){
	    try{
    		var mainMap = tableMap.find(map=> map.mapId == mapId);
    		var serverGroupName = mainMap.columnsMap.find(columnMap=> columnMap && columnMap.import && columnMap.import.serverGroupName).import.serverGroupName;
    		var parentServerColumn;
    		var parentCollectionsMap;
    		var parentMapId;

    		logService.setlog(["Here is data of mapId "+mapId+" in "+serverGroupName+"=>"+dataField, data], "debug");
    		if(data && data[serverGroupName]){
    			let rows = lodash.get(data[serverGroupName], dataField, []);
    			for(var i=0; i<rows.length; i++) {
    				let result = this.formatImportDataRow(rows[i], mapId, tableMap);
    				if(result.parentServerColumns && result.parentServerColumns.length){
    				    let reverseResultColumns = result.parentServerColumns.reverse();
    				    let index = [];
    				    
    				    for(let j=0;j<reverseResultColumns.length;j++){
    				        let resultColumnDetails = reverseResultColumns[j];
    				        let parentMap = tableMap.find(map => map && map.collectionsMap && map.collectionsMap.find(collectionMap=>collectionMap.mapId == resultColumnDetails.mapId));
                    		if(parentMap){
                    			parentMapId = parentMap.mapId;
                    			parentCollectionsMap = parentMap.collectionsMap;
                    			if(parentCollectionsMap){
                    				let parentCollectionsMapDetails = parentCollectionsMap.find(collectionMap=>collectionMap && collectionMap.mapId == resultColumnDetails.mapId);
                    				if(parentCollectionsMapDetails && parentCollectionsMapDetails.localColumn){
                    				    parentLocalColumn = parentCollectionsMapDetails.localColumn;
                    				}else{
                    				    logService.setlog(["no localColumn for "+mapId+ " in "+parentMapId, {parentCollectionsMap: parentCollectionsMap, parentCollectionsMapDetails: parentCollectionsMapDetails}], "warning");
                    				}
                    			}
                    		}
                    		if(parentCollectionsMap && parentLocalColumn && parentMapId){
                    		    let index_element;
            				    logService.setlog(["finding index for "+mapId+" on joinValue "+resultColumnDetails.joinValue, { joinValue: resultColumnDetails.joinValue, mapId: mapId, resultColumnDetails:resultColumnDetails, result: result, formattedData: formattedData, index: index, data: lodash.get(formattedData, index, formattedData)}], "debug");
            				    try{
            					    index_element = lodash.get(formattedData, index, formattedData).findIndex(element=>element[resultColumnDetails.parentServerColumnName] == resultColumnDetails.joinValue);
            				    }
            				    catch(e){
            				        logService.setlog([e, "The above error was thrown with the following inputs:", {functoin: "formatDataForImport::findIndex", joinValue: resultColumnDetails.joinValue, mapId: mapId, resultColumnDetails:resultColumnDetails, result: result, formattedData: formattedData, index: index, data: lodash.get(formattedData, index, formattedData)}], "error");
            				    }
            					//debug:
            					//1- element[result.parentServerColumnName] should be element[result.parentLocalColumnName]
            					let no_index_element = 0;
            					if(index_element<0){
            					    no_index_element = 1;
            					    index_element = lodash.get(formattedData, index, []).length;
            					}else{
            					    index.push(index_element);
            					}

            					if(parentLocalColumn){
            					    index.push(parentLocalColumn);
            					}
            					
            					if(no_index_element){
            					    let index_t = angular.copy(index);
            					    index_t.push(resultColumnDetails.parentServerColumnName);
            					    lodash.set(formattedData, index_t, resultColumnDetails.joinValue);
            					    lodash.set(formattedData, index, {});
            					}
            					if(!lodash.get(formattedData, index, undefined)){
            					    lodash.set(formattedData, index, []);
            					}
    				        }
    				    }
    				    if(index.length){
            				lodash.get(formattedData, index).push(result.formattedDataRow);
    				    }else{
    				        formattedData.push(result.formattedDataRow);
    				    }
    				}else if((!result.parentServerColumns || !result.parentServerColumns.length)){
    					formattedData.push(result.formattedDataRow);
    				}
    			}
    		}
    		if(lodash.has(mainMap,'collectionsMap')){
    			let collectionsMap = mainMap.collectionsMap;
    			for(var i=0; i<collectionsMap.length; i++){
    				this.formatDataForImport(data, tableMap, collectionsMap[i].mapId, dataField, formattedData);
    			}
    		}
	    }
	    catch(e){
	        $log.error(e);
	        $log.error("The above error was thrown with the following inputs:");
	        $log.error({functoin: "formatDataForImport", data: data, tableMap: tableMap, mapId: mapId, dataField: dataField, formattedData: formattedData});
	    }
	},
	this.formatImportDataRow = function(dataRow, mapId, tableMap){
		let formattedDataRow = {};
		let parentServerColumnName;
		let joinValue;
		let mapElement = tableMap.find(map=> map.mapId == mapId);
		let columnsMap = mapElement.columnsMap;
		let mainIdColumnDetails =  columnsMap.find( columnMap => columnMap.rules && columnMap.rules.find(info=>info.name == "join"));
		let parentServerColumns = [];
		while(mainIdColumnDetails && mainIdColumnDetails.rules){
			parentServerColumnName = mainIdColumnDetails.rules.find(info=>info.name == "join").value.importServerColumn;
			if(parentServerColumnName){
    			joinValue = dataRow[mainIdColumnDetails.import.serverColumn];
    			if(!joinValue){
    			    logService.setlog(["function 'formatImportDataRow': No value is set for "+parentServerColumnName+" in 'dataRow["+mainIdColumnDetails.import.serverColumn+"]' for "+mapId, {mainIdColumnDetails: mainIdColumnDetails, dataRow: dataRow, tableMap: tableMap}], "warning");
    			}
    			parentServerColumns.push({parentServerColumnName: parentServerColumnName, joinValue: joinValue, mapId: mapId});
			}else{
			    logService.setlog(["function 'formatImportDataRow': No column name is set for join in 'mainIdColumnDetails' for "+mapId, {mainIdColumnDetails: mainIdColumnDetails, dataRow: dataRow, tableMap: tableMap}], "warning");
			}
			let mapElement2 = tableMap.find(map=> map.collectionsMap && map.collectionsMap.find(collection=> collection.mapId == mapId));
			
			mainIdColumnDetails = undefined;
			if(mapElement2 && mapElement2.mapId && mapElement2.columnsMap){
    			mapId = mapElement2.mapId;
    			let columnsMap2 = mapElement2.columnsMap;
    			mainIdColumnDetails =  columnsMap2.find( columnMap => columnMap.rules && columnMap.rules.find(info=>info.name == "join"));
			}
		}				
		formattedDataRow = this.formatImportDataRowColumns(dataRow, columnsMap);

		return {formattedDataRow: formattedDataRow, parentServerColumns: parentServerColumns};
	},
	this.formatImportDataRowColumns = function(dataRow, columnsMap){
		let importDataObject = {};
		for(var t=0; t<columnsMap.length; t++){
		    if(columnsMap[t].hasOwnProperty('import')){
    			let isRequired = 0;
    			let isJson = 0;
    			if(columnsMap[t].hasOwnProperty('rules')){
    				let isRequiredObject = columnsMap[t].rules.find(item => item.name == "isRequired");
    				if(isRequiredObject && isRequiredObject.value){
    					isRequired = 1;
    				}
    				let isJsonObject = columnsMap[t].rules.find(item => item.name == "type" && item.value == "jsonObject");
    				if(isJsonObject && isJsonObject.value){
    					isJson = 1;
    				}
    			}
    
    			let localColumnName = columnsMap[t].localColumn;
    			let serverGroupName = columnsMap[t]['import'].serverGroupName;
    			let serverColumnName = columnsMap[t]['import'].serverColumn;
    			
    			let serverData = lodash.get(dataRow, serverColumnName, undefined);
    			
    			if(serverData !== undefined ){
    				if(isJson){
    					let tempServerData = serverData;
    					try{
    					    tempServerData = JSON.parse(serverData);
    					}
    					catch(e){
    					    
    					}
    					serverData = tempServerData;
    				}
    				lodash.set(importDataObject, localColumnName, serverData);
    			}else {
    				if(isRequired){
    					return importDataObject;
    				}else{
    					//lodash.set(importDataObject, localColumnName, '');
    				}
    			}
    		}
		}
		
		return importDataObject;
	},
	this.preprocessDataForImport_delete = function(data, tableMap, fields){
		var mainServerGroup = tableMap[0].columnsMap[0].import.serverGroupName;
		var output = [];
		var rows = {};
		for (var key in data) {
			if (data.hasOwnProperty(key)) {
				rows[key] = lodash.get(data[key], fields.dataField, []);
			}
		}

		for (var key in rows) {
			if (rows.hasOwnProperty(key)) {	
				let mapDetails = tableMap.find(map => map.columnsMap.find(columnMap=> columnMap.import.serverGroupName == key) );
				if(mapDetails){
					let mainIdColumnDetails =  mapDetails.columnsMap.find( columnMap => columnMap.rules && columnMap.rules.find(info=>info.name == "join"));
					if(mainIdColumnDetails && mainIdColumnDetails.rules){
						let parentLocalColumnName = mainIdColumnDetails.rules.find(info=>info.name == "join").value.localColumn;
						let parentColumnMap = tableMap.find(map=> map.collectionsMap && map.collectionsMap.find(collection=> collection.mapId == mapDetails.mapId));
						let parentImportCols = parentColumnMap.columnsMap.find(map=>map.localColumn == parentLocalColumnName).import;
						let collectionInParent = parentColumnMap.collectionsMap.find(collection=> collection.mapId == mapDetails.mapId);
					
						for(var i=0; i<rows[key].length; i++){
							if(!rows[parentImportCols.serverGroupName]){
								rows[parentImportCols.serverGroupName] = [];
							}
							let searchCondition = {};
							for(var j=0; j<fields.uniquefields.length; j++){
								searchCondition[parentImportCols.serverColumn] = rows[key][i][mainIdColumnDetails.import.serverColumn];
							}
								
							let parentRecordIndex = lodash.findIndex(rows[parentImportCols.serverGroupName], searchCondition);
							if(parentRecordIndex >-1){
								rows[parentImportCols.serverGroupName][parentRecordIndex][collectionInParent.serverColumn] = rows[key][i];
							}else{
								let recordToUpdate = searchCondition;
								recordToUpdate[collectionInParent.serverColumn] = rows[key][i];
								rows[parentImportCols.serverGroupName].push(recordToUpdate);
							}
						}
					}
				}
			}
		}
		output = rows[mainServerGroup];
		return output;
	}
	this.formatDataForImport_soft = function(data){
		var formattedData = [];
		/*var outputArray = true;
		if(typeof data === "object" && data.constructor === Object){
			let temp = [];
			temp.push(data);
			data = temp;
			outputArray = false;
		}
		for(var i=0; i<data.length; i++) {
			let newData = this.formatImportDataRow_soft(data[i], tableMap);
			formattedData.push(newData);
		}
		if(!outputArray){
			formattedData = formattedData[0];
		}
		*/
		for(var i=0; i<data.length; i++) {
		    for(var key in data[i]){
		        if (data[i].hasOwnProperty(key)) {
		            if(typeof data[i][key] === "string"){
        				let temp = lodash.attempt(JSON.parse.bind(null, data[i][key]));
        				if(typeof temp === "object"){
        					data[i][key] = temp;
        				}
        			}
		        }
		    }
		}
		return formattedData;
	}
	this.formatImportDataRow_soft = function(dataRow, tableMap){
		var formattedDataRow = {};
		var mapElement = {};
		if(tableMap[0]){
			mapElement = tableMap[0];
		}else if(tableMap.mapId){
			mapElement = tableMap;
		}

		var columnMap = mapElement.columnsMap;
		formattedDataRow = this.formatImportDataRowColumns(dataRow, columnMap);

		if(lodash.has(mapElement,'collectionsMap')){
			for(var j=0; j<mapElement['collectionsMap'].length; j++){
				if(mapElement['collectionsMap'][j]['mapId']){
					let collectionMapElement = tableMap.find(item => item.mapId == mapElement['collectionsMap'][j]['mapId']);
					formattedDataRow[mapElement['collectionsMap'][j].localColumn] = [];
					if(lodash.has(mapElement['collectionsMap'][j], 'serverColumn')){
						let collectionData = dataRow[mapElement['collectionsMap'][j].serverColumn];
						if(collectionData && typeof collectionData === "string"){
							collectionData = lodash.attempt(JSON.parse.bind(null, collectionData));
						}
						if(collectionData instanceof Object){
							for(var i=0; i<collectionData.length; i++){
								let collectionDataRow = collectionData[i];
								formattedDataRow[mapElement['collectionsMap'][j].localColumn].push(this.formatImportDataRow_soft(collectionDataRow, collectionMapElement));
							}
						}
					}
				}
			}
		}
		return formattedDataRow;
	}
	//TO DO: MAKE RULES CONDITION IN IF() DYNAMIC
	this.exportToServer = function(db, allowedTries=3, limit=1000, start=0, size=0, tried=0){
		var that = this;
		return $q(function (resolve, reject) {
			var toExportDb = localDb.getTable('dataToExport', db);
			if(!size){
				size = toExportDb.chain().data().length;
			}
			//slice data to smaller chunks if it is larger tha size records
			if(start<size && tried<allowedTries) {
			
				let dataToExport = toExportDb.chain().simplesort("serverGroupName").offset(start).limit(limit).data();
				let dataToExportGrouped = dataToExport.reduce(function(obj,item){
					obj[item.serverGroupName] = obj[item.serverGroupName] || [];
					obj[item.serverGroupName].push(item);
					return obj;
				}, {});

				let exportToServerDataParameters = {
					action: 'exportToServer',
					dataToExport: dataToExportGrouped
				};
				logService.setlog(["Exporting data to server", {dataToExport: dataToExportGrouped}], "info");
				return httpService.request({
					method : "POST",
					url : "/dynaPage.php",
					data: exportToServerDataParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					if(response.data.status){
					    if(response.data.msg){
    					    for(var key in response.data.msg){
    					        if(response.data.msg.hasOwnProperty(key)){
    					            $log.warn(key+": "+response.data.msg[key]);
    					        }
    					    }
					    }
						let recordsToDelete = []; //delete synced data from to be sync local table
						if(response.data.processedIds){
							let processedIds = response.data.processedIds;
							for(var i=0; i<processedIds.length; i++){
								let oldRecord = toExportDb.findOne({dataId: processedIds[i]});
								if(oldRecord){
									recordsToDelete.push({dataId: oldRecord['dataId']});
								}
							}

							if(recordsToDelete.length){
								for (var i = 0; i < recordsToDelete.length; i++) {
									toExportDb.removeWhere(recordsToDelete[i]);
								}
							}
							db.throttledSaveDrain(function () {
								db.saveDatabase(function(){
									tried = 0;
									that.exportToServer(db, allowedTries, limit, start, size-limit, tried);
								});
							});
						}
					}
				}, function submitError(response) {
					tried = tried + 1;
					that.exportToServer(db, allowedTries, limit, start+limit, size, tried);
				});
			}else if(start<size && tried>=allowedTries){
				reject({status: 0, toExportDb: toExportDb, db: db, localDb: localDb});
			}else if(start>=size){
				resolve({status: 1, toExportDb: toExportDb, db: db, localDb: localDb});
			}
		});
	},
	this.insertDataToExport = function(dataToExport){
			if(!globals.db){
				return localDb.initilizeDb().then(function(db){
					let dataToExportDb = localDb.getTable('dataToExport', db);
					return this.insertForExport(dataToExport, dataToExportDb, db);
				}.bind(this));
			}else{
				let dataToExportDb = localDb.getTable('dataToExport', globals.db);
				return this.insertForExport(dataToExport, dataToExportDb, globals.db);
			}
	},
	this.insertForExport = function(dataToExport, dataToExportDb, db){
		return $q(function (resolve, reject) {
			var dataToUpdate = [];
			var dataToInsert = [];
			if(dataToExport.length){
				for(var i=0; i<dataToExport.length; i++){
					let oldRecord = dataToExportDb.findOne({dataId: dataToExport[i]['dataId']});
					if(oldRecord){
						dataToUpdate.push(oldRecord);
					}else{
						dataToInsert.push(dataToExport[i]);
					}
				}
			}

			if(dataToUpdate.length){
				dataToExportDb.update(dataToUpdate);
			}
			if(dataToInsert.length){
				dataToExportDb.insert(dataToInsert);
			}
			db.throttledSaveDrain(function () {
				db.saveDatabase(function(){
					resolve({status: 1, db: db, dataToExportDb: dataToExportDb});
				});
			});
		});
	},
	this.formatExportData = function(localData, localTableMapName, tableMapDb){
	    logService.setlog(["Formatting Data to export for "+localTableMapName, {localData: localData, tableMapDb: tableMapDb}], "debug");
		var exportedData = [];
		var tableMapDetails = tableMapDb.findOne({dynaPageTableMap_localTable: localTableMapName});
		var tableMap = tableMapDetails.dynaPageTableMap_columnMap;
		
		if(localData && !localData.length && localData.constructor === Object && Object.keys(localData).length > 0){
			let temp = localData;
			localData = [];
			localData.push(temp);
		}
		let mapElement = tableMap[0];
		for(var i=0; i<localData.length; i++){
			exportedData = this.formatExportDataRow(localData[i], tableMap, mapElement, exportedData);
		}
		return {exportedData: exportedData, tableMap: tableMap, localTableName: localTableMapName};
	},
	this.formatExportDataRow = function(localDataRow, tableMap, mapElement, exportedDataRow){
		if(!exportedDataRow){
			exportedDataRow = [];
		}
		var columnMap = mapElement.columnsMap;

		exportedDataRow = this.formatExportDataRowColumns(localDataRow, columnMap, exportedDataRow);

		if(mapElement.hasOwnProperty('collectionsMap')){
			for(var j=0; j<mapElement['collectionsMap'].length; j++){
				if(mapElement['collectionsMap'][j]['mapId']){
					let collectionMapElement = tableMap.find(item => item.mapId == mapElement['collectionsMap'][j]['mapId']);
					if(localDataRow[mapElement['collectionsMap'][j].localColumn]){
    					for(var i=0; i<localDataRow[mapElement['collectionsMap'][j].localColumn].length; i++){
    						let collectionDataRow = localDataRow[mapElement['collectionsMap'][j].localColumn][i];
    						exportedDataRow = this.formatExportDataRow(collectionDataRow, tableMap, collectionMapElement, exportedDataRow);
    					}
					}
				}
			}
		}
		return exportedDataRow;
	},
	this.formatExportDataRowColumns = function(localDataRow, columnsMap, exportedDataRow){
		let exportDataObject = {};
		for(var t=0; t<columnsMap.length; t++){
		    if(columnsMap[t].hasOwnProperty('export')){
    			let isRequired = 0;
    			let type = "string";
    			
    			if(columnsMap[t].hasOwnProperty('rules')){
    				let isRequiredObject = columnsMap[t].rules.find(item => item.name == "isRequired");
    				if(isRequiredObject && isRequiredObject.value){
    					isRequired = 1;
    				}
    				let typeObj = columnsMap[t].rules.find(item => item.name == "type");
    				if(typeObj && typeObj.value){
    					type = typeObj.value;
    				}
    			}
    		
    			let localColumnName = columnsMap[t].localColumn;
    			let localProperty = localColumnName.split(".");
    			
    			if(localDataRow.hasOwnProperty(localProperty[0])){
    				let localDataRowt = localDataRow;
    				for(var i=0; i<localProperty.length; i++){
    					localDataRowt = localDataRowt[localProperty[i]];
    				}
    				if(isRequired && (!localDataRowt || localDataRowt == '')){
    					return exportedDataRow;
    				}
    				if(lodash.size(columnsMap[t]['export'])){
        				if(typeof columnsMap[t]['export'] === 'object' && !Array.isArray(columnsMap[t]['export'])){
        				    columnsMap[t]['export'] = [columnsMap[t]['export']];
        				}
        				for(let j=0; j<columnsMap[t]['export'].length; j++){
            				let serverGroupName = columnsMap[t]['export'][j].serverGroupName;
            			    let serverColumnName = columnsMap[t]['export'][j].serverColumn;
            			    
            				if(!exportDataObject[serverGroupName]){
            					exportDataObject[serverGroupName] = {exportData: {}};
            				}
            				
            				if(type == "jsonObject"){
            					let temp = JSON.stringify(localDataRowt);
            					if(temp){
            						localDataRowt = temp;
            					}
            				}
            				exportDataObject[serverGroupName].exportData[serverColumnName] = localDataRowt;
        				}
        			}else{
        			    $log.info("You have not specified export rules for the following column map");
    				    $log.info(columnsMap[t]);
        			}
    			}
    		}
		}
		for (var serverGroupName in exportDataObject) {
			if (exportDataObject.hasOwnProperty(serverGroupName)) {
				exportedDataRow.push({serverGroupName: serverGroupName, exportData: exportDataObject[serverGroupName].exportData, dataId: guid.newuuid('exportServerGroupId')});
			}
		}
		
		return exportedDataRow;
	}
});
app.service('offlineItems', function(httpService, localDb, localStorageService, globals, lodash, logService){
		this.getItems = function(tillId){
			if(!globals.db){
				return localDb.initilizeDb().then(function(db){
					let itemsDb = localDb.getTable('items', db);
					return this.downloadItems(tillId, itemsDb, db);
				}.bind(this));
			}else{
				let itemsDb = localDb.getTable('items', globals.db);
				return this.downloadItems(tillId, itemsDb, globals.db);
			}
		},
		this.downloadItems = function(tillId, itemsDb ,db){
			var requestTime = moment.utc().format("YYYY-MM-DD HH:mm:ss");
			
			let onlineInsertTime = "";
			if(localStorageService.get('lastItemDownload')){
					onlineInsertTime = localStorageService.get('lastItemDownload');
			}else{
				onlineInsertTime = "";
			}
			let allItemsCount = itemsDb.data.length;
				
			var brandsId = [];
			if(localStorageService.get('tillBrands_'+tillId)){
				brandsId = localStorageService.get('tillBrands_'+tillId);
			}else{
				brandsId = [];
			}
			
			let properties = {
				"itemsDetail": ["itemName","itemBarcode"],
				"itemsPrice": ["itemId","price","currencyId","validFrom","status"],
				"concat": "itemsDetail.itemBarcode,itemsPrice.currencyId"
			};
			var itemsDownloadParameters = {
				action: 'getAllItemPrices',
				getCount: 1,
				properties: properties,
				tillId: tillId,
				brandsId: brandsId,
				allDataLength: allItemsCount,
				onlineInsertTime_start: onlineInsertTime
			};
		
			return httpService.request({
				method : "POST",
				url : "/items.php",
				data: itemsDownloadParameters,
				headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
			}).then(function submitSuccess(response) {
				if(response.data.status){
					localStorageService.set('lastItemDownload', requestTime);
					
					if(response.data.brandsId){
						localStorageService.set('tillBrands_'+tillId, response.data.brandsId);
						var tillDetails = localStorageService.get('tillDetails_'+tillId);
						if(!tillDetails){
							localStorageService.set('tillDetails_'+tillId, response.data.tillDetails);
						}
					}
					if(!response.data.partial){
						logService.setlog("Re-saving whole items data", "info",1);
						itemsDb.chain().remove();
						itemsDb.removeDataOnly();
									
						var items = itemsDb.insert(
							response.data.items.rows
						);
					}else{
						logService.setlog("Updating new items data", "info",1);
						var i,arrLen = response.data.items.rows.length;
						var itemsToUpdate = [];
						var itemsToInsert = [];
						for (i = 0; i < arrLen; i++) {
							let oldRecord = itemsDb.findOne({itemsDetail_itemBarcode_itemsPrice_currencyId:response.data.items.rows[i]['itemsDetail_itemBarcode_itemsPrice_currencyId']});
							if(oldRecord){
								lodash.merge(oldRecord,response.data.items.rows[i]);
								itemsToUpdate.push(oldRecord);
							}else{
								
								itemsToInsert.push(response.data.items.rows[i]);
							}
						}
	
						if(itemsToUpdate.length){
							itemsDb.update(itemsToUpdate);
						}
						if(itemsToInsert.length){
							itemsDb.insert(itemsToInsert);
						}
					}
					db.throttledSaveDrain(function () {
						db.saveDatabase();
					});
					logService.clearlog();
					
					return ({status: 1, msg: '', itemsDb: itemsDb, tillDetails: tillDetails});
				}else{
					logService.setlog(response.data.msg, "error",0);
					return ({status: 0, msg: response.data.msg, itemsDb: {}});
				}
			}, function submitError(response) {
				logService.setlog(response, "error",0);
				return ({status: 0, msg: response, itemsDb: {}});
			});
		}	
});

/* 
 * An Angular service which helps with creating recursive directives.
 * @author Mark Lagendijk
 * @license MIT
 */
angular.module('RecursionHelper', []).factory('RecursionHelper', ['$compile', function($compile){
	return {
		/**
		 * Manually compiles the element, fixing the recursion loop.
		 * @param element
		 * @param [link] A post-link function, or an object with function(s) registered via pre and post properties.
		 * @returns An object containing the linking functions.
		 */
		compile: function(element, link){
			// Normalize the link parameter
			if(angular.isFunction(link)){
				link = { post: link };
			}

			// Break the recursion loop by removing the contents
			var contents = element.contents().remove();
			var compiledContents;
			return {
				pre: (link && link.pre) ? link.pre : null,
				/**
				 * Compiles and re-adds the contents
				 */
				post: function(scope, element){
					// Compile the contents
					if(!compiledContents){
						compiledContents = $compile(contents);
					}
					// Re-add the compiled contents to the element
					compiledContents(scope, function(clone){
						element.append(clone);
					});

					// Call the post-linking function, if any
					if(link && link.post){
						link.post.apply(null, arguments);
					}
				}
			};
		}
	};
}]);

app.directive("treeselect", function(RecursionHelper) {
    return {
        restrict: "E",
        scope: {family: '=', record: '=', itemclick: '&'},
        template: 
        '<md-subheader class="md-primary"><div layout="row"><md-checkbox value="{{ family.groupId }}" data-ng-checked="(record.groups|filter:{groupId:family.groupId}:true).length > 0" data-ng-click="toggleselectedgroups(family.groupId, record)" layout="row">{{ family.groupName }}</md-checkbox>'+
			'<md-datepicker data-ng-if="(record.groups|filter:{groupId:family.groupId}:true).length > 0" data-ng-model="record.groups[record.groups.indexOf((record.groups|filter:{groupId:family.groupId}:true)[0])].accessStart" md-placeholder="Access Start Date" md-open-on-focus acceptallzeroasdate="true"></md-datepicker>' +
			'<md-datepicker data-ng-if="(record.groups|filter:{groupId:family.groupId}:true).length > 0" data-ng-model="record.groups[record.groups.indexOf((record.groups|filter:{groupId:family.groupId}:true)[0])].accessEnd" md-placeholder="Access End Date" md-open-on-focus acceptallzeroasdate="true"></md-datepicker>' +
		'</div></md-subheader>' + 
            '<ul class="child">' + 
                '<li data-ng-repeat="child in family.children" style="display: table;">' + 
                    '<treeselect family="child" record="record" itemclick="toggleselectedgroups(child.groupId,record)"></treeselect>' +
                '</li>' +
            '</ul>',
        compile: function(element) {
            return RecursionHelper.compile(element, function(scope, iElement, iAttrs, controller, transcludeFn){
                // Define your normal link function here.
                // Alternative: instead of passing a function,
                // you can also pass an object with 
                // a 'pre'- and 'post'-link function.
            });
        },
        controller: function ($scope,$filter){
		$scope.toggleselectedgroups = function(changedGroupId,record){
			if($filter('filter')(record.groups, {groupId:changedGroupId}, true).length>0){
				var object = $filter('filter')(record.groups, {groupId:changedGroupId}, true);
				var index = record.groups.indexOf(object[0]);
				if(index > -1){
					record.groups.splice(index,1);
				}
			}else{
				record.groups.push({groupId:changedGroupId});
			}
		};
        }
    };
});

app.directive("digitalClock", function($timeout, dateFilter) {
  return {
    restrict: 'E',
    scope: {format: '@'},
    link: function(scope, iElement) {
      (function updateClock() {
        iElement.text(dateFilter(new Date(), scope.format));
        $timeout(updateClock, 1000);
      })();
    }
  };
});

app.directive('jsonText', function() {

});
    
app.directive('moduleItemsTable', function() {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        templateUrl: 'modules_table.html'
    };
});
app.directive('moduleItemsPaymentTable', function() {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        templateUrl: 'modules_paymentTable.html'
    };
});
app.directive('moduleButton', function() {
    return {
        restrict: 'E',
        replace: true,
        transclude: false,
        scope: {parameters: '@'},
        template: "<md-button class='{{parameters.baseClass}} {{parameters.class}}' data-ng-click='{{parameters.action}}' style='width:87%;height:87%;'>{{parameters.text}}</md-button>"
    };
}); 
    
app.directive("currencyId", function($compile) {
	return {
		restrict: 'A',
		scope: true,
		terminal: true,
		priority: 1000,
		require: "?ngModel",
		compile: function(tElem, tAttrs) {
			tElem.removeAttr('currency-id'); 
			tElem.attr('mask', '@@@');
			tElem.attr('clean', 'false');
			tElem.attr('restrict', 'reject');
			tElem.attr('limit', 'true');
			tElem.attr('validate', 'true');
			return {
				pre: function preLink(scope, iElement, iAttrs, controller) { 

				},
				post: function postLink(scope, iElement, iAttrs, controller) { 
					$compile(iElement)(scope);
				}
			}
		}
	};
});

app.directive('checkMax', function() {
    return {
    restrict: 'A', //attribute or element,
    require: 'ngModel',
    link:function(s,e,a,ngModel){
        e.bind('keyup',function(event){
        if(parseFloat(ngModel.$modelValue)>parseFloat(a.max)){
          event.preventDefault();
          ngModel.$setViewValue(a.max);
          ngModel.$render();
        }
      });
    }
  };
});

app.directive("price", function($compile) {
	return {
		restrict: 'A',
		scope: true,
		terminal: true,
		priority: 1000,
		require: "?ngModel",
		compile: function(tElem, tAttrs) {
			tElem.removeAttr('price'); 
			tElem.attr('mask', '*?9?9?9?9?9?9?9?9.99');
			tElem.attr('clean', 'false');
			tElem.attr('restrict', 'reject');
			tElem.attr('limit', 'true');
			tElem.attr('validate', 'true');
			return {
				pre: function preLink(scope, iElement, iAttrs, controller) { 

				},
				post: function postLink(scope, iElement, iAttrs, controller) { 
					$compile(iElement)(scope);
				}
			}
		}
	};
});

app.directive("pasajphone", function($compile) {
	return {
		restrict: 'A',
		scope: true,
		terminal: true,
		priority: 5000,
		require: "?ngModel",
		compile: function(tElem, tAttrs) {
			tElem.removeAttr('pasajphone'); 
			tElem.attr('mask', '+1 (999) 999-9999');
			tElem.attr('clean', 'true');
			tElem.attr('restrict', 'reject');
			tElem.attr('limit', 'true');
			tElem.attr('validate', 'true');
			tElem.attr('ng-pattern', '/^[+][1] [(][0-9]{3}[)] [0-9]{3}-[0-9]{4}$/');
			return {
				pre: function preLink(scope, iElement, iAttrs, controller) { 

				},
				post: function postLink(scope, iElement, iAttrs, controller) { 
					$compile(iElement)(scope);
				}
			}
		}
	};
});

app.directive("pasajemail", function($compile) {
	return {
		restrict: 'A',
		scope: true,
		terminal: true,
		priority: 5000,
		require: "?ngModel",
		compile: function(tElem, tAttrs) {
			tElem.removeAttr('pasajemail'); 
			tElem.attr('ng-pattern', '/^.+@.+\..+$/');
			return {
				pre: function preLink(scope, iElement, iAttrs, controller) { 

				},
				post: function postLink(scope, iElement, iAttrs, controller) { 
					$compile(iElement)(scope);
				}
			}
		}
	};
});
app.directive('pasajnumber', function() {
  return {
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      ngModel.$parsers.push(function(value) {
        return '' + value;
      });
      ngModel.$formatters.push(function(value) {
        return parseFloat(value);
      });
    }
  };
});
app.directive("pasajdatetime", function($compile) {
	return {
		restrict: 'A',
		scope: true,
		terminal: true,
		priority: 5000,
		require: "?ngModel",
		compile: function(tElem, tAttrs) {
			tElem.removeAttr('pasajdatetime'); 
			tElem.attr('mask', '2099-19-39 29:59:59');
			tElem.attr('clean', 'false');
			tElem.attr('restrict', 'reject');
			tElem.attr('limit', 'true');
			tElem.attr('validate', 'true');
			tElem.attr('ng-pattern', '/^([0-9]{4}[-][0-9]{2}[-][0-9]{2}[ ][0-9]{2}[:][0-9]{2}[:][0-9]{2})/');
			return {
				pre: function preLink(scope, iElement, iAttrs, controller) { 

				},
				post: function postLink(scope, iElement, iAttrs, controller) { 
					$compile(iElement)(scope);
				}
			}
		}
	};
});

app.directive("pasajdate", function($compile) {
	return {
		restrict: 'A',
		scope: true,
		terminal: true,
		priority: 5000,
		require: "?ngModel",
		compile: function(tElem, tAttrs) {
			tElem.removeAttr('pasajdate'); 
			tElem.attr('mask', '2099-19-39');
			tElem.attr('clean', 'false');
			tElem.attr('restrict', 'reject');
			tElem.attr('limit', 'true');
			tElem.attr('validate', 'true');
			tElem.attr('ng-pattern', '/^([0-9]{4}[-][0-9]{2}[-][0-9]{2})/');
			return {
				pre: function preLink(scope, iElement, iAttrs, controller) { 

				},
				post: function postLink(scope, iElement, iAttrs, controller) { 
					$compile(iElement)(scope);
				}
			}
		}
	};
});

app.directive("pasajprice", function($compile) {
	return {
		restrict: 'A',
		scope: true,
		terminal: true,
		priority: 5000,
		require: "?ngModel",
		compile: function(tElem, tAttrs) {
			tElem.removeAttr('pasajprice'); 
			tElem.attr('ng-pattern', '/^([0-9]{1}[.][0-9]{2})|([0-9]{2}[.][0-9]{2})|([0-9]{3}[.][0-9]{2})|([0-9]{4}[.][0-9]{2})/');
			return {
				pre: function preLink(scope, iElement, iAttrs, controller) { 

				},
				post: function postLink(scope, iElement, iAttrs, controller) { 
					$compile(iElement)(scope);
				}
			}
		}
	};
});



app.directive("percent", function($compile) {
	return {
		restrict: 'A',
		scope: true,
		terminal: true,
		priority: 1000,
		require: "?ngModel",
		compile: function(tElem, tAttrs) {
			tElem.removeAttr('percent'); 
			tElem.attr('mask', '1?9?9?');
			tElem.attr('clean', 'false');
			tElem.attr('restrict', 'reject');
			tElem.attr('limit', 'true');
			tElem.attr('validate', 'true');
			return {
				pre: function preLink(scope, iElement, iAttrs, controller) { 

				},
				post: function postLink(scope, iElement, iAttrs, controller) { 
					$compile(iElement)(scope);
				}
			}
		}
	};
});

app.directive("quantity", function($compile) {
	return {
		restrict: 'A',
		scope: true,
		terminal: true,
		priority: 1000,
		require: "?ngModel",
		compile: function(tElem, tAttrs) {
			tElem.removeAttr('quantity'); 
			tElem.attr('mask', '*?9?9?9?9?9?9?9?9');
			tElem.attr('clean', 'false');
			tElem.attr('restrict', 'reject');
			tElem.attr('limit', 'true');
			tElem.attr('validate', 'true');
			return {
				pre: function preLink(scope, iElement, iAttrs, controller) { 

				},
				post: function postLink(scope, iElement, iAttrs, controller) { 
					$compile(iElement)(scope);
				}
			}
		}
	};
});
app.factory('lodash', function($window) { return $window._;});
app.factory("guid", function () {
    return {
        newuuid: function (tableName='') {
            // http://www.ietf.org/rfc/rfc4122.txt
            var s = [];
            var hexDigits = "0123456789abcdef";
            for (var i = 0; i < 36; i++) {
                s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
            }
            s[14] = "4";  // bits 12-15 of the time_hi_and_version field to 0010
            s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1);  // bits 6-7 of the clock_seq_hi_and_reserved to 01
            s[8] = s[13] = s[18] = s[23] = "-";
            return s.join("");
        },
        createNewNumeric: function (tableName='', leadingNumber=0) {
        	var d = new Date();
        	return d.getSeconds()+leadingNumber;
        }
    }
});

angular.module('angular-toArrayFilter', [])

.filter('toArray', function () {
  return function (obj, addKey) {
    if (!angular.isObject(obj)) return obj;
    if ( addKey === false ) {
      return Object.keys(obj).map(function(key) {
        return obj[key];
      });
    } else {
      return Object.keys(obj).map(function (key) {
        var value = obj[key];
        return angular.isObject(value) ?
          Object.defineProperty(value, '$key', { enumerable: false, value: key}) :
          { $key: key, $value: value };
      });
    }
  };
});

angular.module('angular-sumByKey', [])
    .filter('sumByKey', function() {
        return function(data, key) {
            if (typeof(data) === 'undefined' || typeof(key) === 'undefined') {
                return 0;
            }
 
            var sum = 0;
            if(Array.isArray(data)){
	            for (var i = data.length - 1; i >= 0; i--) {
	                sum += (parseFloat(data[i][key]) || 0);
	            }
            }else{
            	for (var keyTemp in data) {
			if (data.hasOwnProperty(keyTemp)) {
				sum += (parseFloat(data[keyTemp][key]) || 0);
			}
		}
            }
 
            return sum || 0;
        };
});

(function (angular) {
    'use strict';

    var bindHtmlCompile = angular.module('angular-bind-html-compile', []);

    bindHtmlCompile.directive('bindHtmlCompile', ['$compile', function ($compile) {
        return {
            restrict: 'A',
            priority: 100,
            replace: true,
            link: function (scope, element, attrs) {
                try{
                scope.$watch(function () {
                    return scope.$eval(attrs.bindHtmlCompile);
                }, function (value) {
                    // In case value is a TrustedValueHolderType, sometimes it
                    // needs to be explicitly called into a string in order to
                    // get the HTML string.
                    element.html(value && value.toString());
                    // If scope is provided use it, otherwise use parent scope
                    var compileScope = scope;
                    if (attrs.bindHtmlScope) {
                        compileScope = scope.$eval(attrs.bindHtmlScope);
                    }
                    
                    $compile(element.contents())(compileScope);
                });
                }
                catch(e){
                    $log.error(e);
                }
            }
        };
    }]);

    if (typeof module !== 'undefined' && module.exports) {
        module.exports = bindHtmlCompile.name;
    }
}(window.angular));

app.config(function ($mdKeyboardProvider) {
  
    // add layout for number fields
    $mdKeyboardProvider.addLayout('Numbers', {
      'name': 'Numbers', 'keys': [
            [['7', '7'], ['8', '8'], ['9', '9']],
            [['4', '4'], ['5', '5'], ['6', '6']],
            [['1', '1'], ['2', '2'], ['3', '3']],
            [['Bksp', 'Bksp'], ['0', '0'], ['Enter', 'Enter']]
      ], 'lang': ['en']
    });

    // default layout is german
    $mdKeyboardProvider.defaultLayout('Numbers');
});

//Check the online status of application
app.run(function($window, $rootScope) {
      $rootScope.appIsOnline = navigator.onLine;
      $window.addEventListener("offline", function () {
        $rootScope.$apply(function() {
          $rootScope.appIsOnline = false;
        });
      }, false);
      $window.addEventListener("online", function () {
        $rootScope.$apply(function() {
          $rootScope.appIsOnline = true;
        });
      }, false);
});

app.config(function($controllerProvider, $compileProvider, $provide, $filterProvider) {
    app.controller = function (name, controller) {
        $controllerProvider.register(name, controller);
    };
    app.compileProvider = $compileProvider; //for directive lazyload
    app.provide = $provide; //for service and factory lazyload
    app.filterProvider = $filterProvider; //for filter lazyload
    app.controllerProvider = $controllerProvider;
});

app.directive('script', function() {
    return {
      restrict: 'E',
      scope: false,
      link: function(scope, elem, attr, $controllerProvider) {
        if (attr.type === 'text/javascript-lazy') {
          var code = elem.text();
          var f = new Function(code);
          f();
        }
      }
    };
});

app.directive('lazycompile', function($interpolate, $compile) {
	return {
		restrict: 'E',
		replace: true,
		scope: false,
		link: function(scope, element, attr) {
			$compile(element.contents())(scope); //<---- recompilation 
		}
	}
});
app.directive("onElementResize", function($parse) {

  return {
    restrict: "A",
    link: function(scope, element, attrs) {
      
      var previousWidth = element[0].offsetWidth;
      var timer;
      
      onFrame();
      
      function onFrame() {
        var currentWidth = element[0].offsetWidth;
        
        if (!angular.equals(previousWidth, currentWidth)) {
          resiszeNotifier();
        }
        
        previousWidth = currentWidth;
        requestAnimationFrame(onFrame);
      }
      
      function resiszeNotifier() {
        window.dispatchEvent(new Event('resize'));
        /*var rs = $parse(attrs.onElementResize);
        rs(scope, {});
        */
      }
    }
  };
});

app.directive('updateOnValueChange', function() {
    return {
        restrict: 'A',
        require: 'ngModel',
        scope:{
            updateOnValueChange:'='
        },
        link: function(scope, element, attrs, vm) {
            scope.$watch("updateOnValueChange", function(newValue, oldValue){
                if(newValue){
                    element.controller('ngModel').$viewChangeListeners[0]();
                }
            });
        }
    }
});