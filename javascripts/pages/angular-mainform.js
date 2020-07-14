var dependancies = ['ui.router',"ngSanitize","ngMaterial","ngFitText","RecursionHelper",'ngInputModified','ngMessages', 'ngCookies', 'angular-toArrayFilter', 'angular-sumByKey',"ui.grid",'ngMask','ui.grid.resizeColumns', 'ui.grid.autoResize', 'ui.grid.moveColumns','ui.grid.saveState','ui.grid.pagination', 'ui.grid.pinning', 'ui.grid.grouping', 'ui.grid.exporter', 'ui.grid.selection', 'ui.grid.edit', 'ui.grid.cellNav', 'angular-gridster2', 'angular-bind-html-compile', 'lokijs', 'pouchdb', 'LocalStorageModule', 'material.components.keyboard','AngularPrint', 'angularMoment', 'ngAria', 'ngAnimate', 'pascalprecht.translate', 'ngBarcode', 'angular-intro', 'ngWebSocket'];
var offlineDb;
var offlineDb_Loading = false;
var app = angular.module("pasaj", []);
//Array.prototype.push.apply(app.requires, dependancies);
[].forEach.call(dependancies, function (d){
    app.requires.push(d);
});

var websocketURL = 'wss://demo.pasajpos.com/wss';

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
app.config(function (localStorageServiceProvider) {
    localStorageServiceProvider
      .setPrefix('pasajpos_')
      .setStorageType('localStorage');
});
app.config(function($stateProvider, $translateProvider) {
    //ROUTING
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
            pageCode: {squash: "", value: 'home'},
            mainObjectId: {squash: true, value: null, dynamic: false},
            mainData: {squash: true, value: null, dynamic: false},
            accessWithoutLogin: {squash: true, value: 0, dynamic: false}
        },
		views : {
			'main@' : {
				templateUrl : function (params) {
					if(params.pageCode && !params.modalPageId){
						var fullUrl = "dynaPage.html";
						return fullUrl;
					}
				       	//return a valid Url to the template, and angular will load it for you
				},
				controller: 'dynaPage',
				controllerAs: '$ctrl'
			}
		}
	}

	$stateProvider.state(mainState);
	$stateProvider.state(dynaPageState);
				
	//TRANSLATION		
	$translateProvider.useStaticFilesLoader({
        prefix: '/i18n/',// path to translations files
        suffix: '.json'// suffix, currently- extension of the translations
    });
              
    $translateProvider.useSanitizeValueStrategy('sanitize');
    $translateProvider.preferredLanguage('en_US');
    $translateProvider.useLocalStorage();
});

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
app.factory('PasajWS', function($websocket) {
    // Open a WebSocket connection
    var dataStream = $websocket(websocketURL);

    var collection = [];

    dataStream.onMessage(function(message) {
        collection.push(JSON.parse(message.data));
    });

    var methods = {
        collection: collection,
        get: function() {
            dataStream.send(JSON.stringify({ action: 'get' }));
        }
    };

    return methods;
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
	    try{
    	    logService.setlog(["requesting network data", options], "debug");
    	   // if(onlineStatus.isOnline()){
    	    return $http(options).then(function(response){
    	        logService.setlog(["response received", {options:options, response:response}], "debug");
    	        if(response && response.data && response.data.compressed){
    	            response.data = RJSON.unpack(response.data);
    	            logService.setlog(["unpacking response", {options:options, response:response}], "debug");
    	        }
    	        return response;
    	    }, function submitError(response) {
                return response;
        	});
	    }
	    catch(e){
	        logService.setlog(["Erro happened while requesting network data", {options: options}], "error");
	    }
	   /* }else{
	        return $q(function (resolve, reject) {
	            resolve();
	        })
	    }*/
	}
});
app.run(function ($state, $transitions, Auth, $log, $q, loginService, $location, logService, onlineStatus) {
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
		let method;
		if(onlineStatus.isOnline()){
			method = 'usernameLogin';
		}else{
			method = 'pinCodeLogin';
		}
		return $q(function (resolve, reject) {
			return Auth.CheckLoggedIn().then(function(){
				resolve();
			}, function(){
			    logService.setlog(["changing state to the following",{toState: toState}], "debug");
			    if(loginRequired && !toState.params().accessWithoutLogin){
				    return loginService.showLogin(method)
					.then(function (response) {
						if(response.userId){
						    Auth.setUser(response.userId);
							resolve();
						}else{
							reject();
						}
						if($scope){
							$scope.$emit("updateMenu",{});
						}
					})
					.catch(function (e) {
						reject();
						logService.setlog(["error updating menu",{error: e}], "debug");
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
                    .primaryPalette('blue')
                    .accentPalette('grey');
			    $mdThemingProvider.theme("error-toast");
			    $mdThemingProvider.theme("offlineTheme")
			      .primaryPalette('blue-grey')
			      .accentPalette('blue-grey');
				var customPrimary = {
					'50': '#ffffff',
					'100': '#ffffff',
					'200': '#ffffff',
					'300': '#ffffff',
					'400': '#ffffff',
					'500': '#f8f9fa',
					'600': '#e9ecef',
					'700': '#dadfe5',
					'800': '#cbd3da',
					'900': '#bcc6cf',
					'A100': '#ffffff',
					'A200': '#ffffff',
					'A400': '#ffffff',
					'A700': '#aeb9c5'
				};
				$mdThemingProvider
					.definePalette('customPrimary', 
									customPrimary);

				var customAccent = {
					'50': '#093d94',
					'100': '#0a47ac',
					'200': '#0b51c5',
					'300': '#0d5bdd',
					'400': '#1266f1',
					'500': '#2a75f3',
					'600': '#5a95f5',
					'700': '#72a4f7',
					'800': '#8ab4f8',
					'900': '#a2c3fa',
					'A100': '#5a95f5',
					'A200': '#4285f4',
					'A400': '#2a75f3',
					'A700': '#bad3fb'
				};
				$mdThemingProvider
					.definePalette('customAccent', 
									customAccent);

				var customWarn = {
					'50': '#ffb280',
					'100': '#ffa266',
					'200': '#ff934d',
					'300': '#ff8333',
					'400': '#ff741a',
					'500': '#ff6400',
					'600': '#e65a00',
					'700': '#cc5000',
					'800': '#b34600',
					'900': '#993c00',
					'A100': '#ffc199',
					'A200': '#ffd1b3',
					'A400': '#ffe0cc',
					'A700': '#803200'
				};
				$mdThemingProvider
					.definePalette('customWarn', 
									customWarn);

				var customBackground = {
					'50': '#ffffff',
					'100': '#ffffff',
					'200': '#ffffff',
					'300': '#ffffff',
					'400': '#ffffff',
					'500': '#f8f9fa',
					'600': '#e9ecef',
					'700': '#dadfe5',
					'800': '#cbd3da',
					'900': '#bcc6cf',
					'A100': '#ffffff',
					'A200': '#ffffff',
					'A400': '#ffffff',
					'A700': '#aeb9c5'
				};
				$mdThemingProvider
					.definePalette('customBackground', 
									customBackground);

			   /*$mdThemingProvider.theme('default')
				   .primaryPalette('customPrimary')
				   .accentPalette('customAccent')
				   .warnPalette('customWarn')
				   .backgroundPalette('customBackground');*/

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
			
app.controller('mainform', function($scope, $filter, $location, $window, httpService, $timeout, $mdSidenav, $log, $mdDialog, $mdToast, $templateCache, $transitions, $interval, $state, Auth, uiGridConstants, uiGridExporterConstants, uiGridExporterService, localDb, externalDevice, logService, menuService, loginService, lodash, onlineStatus, localStorageService, $translate, guid) {
			    let vm = this;
				externalDevice.sendData("%clearScreen%","LINE_DISPLAY").then(function(){
				externalDevice.sendData("Till Clsoed","LINE_DISPLAY");
				});
				$scope.dynamicTheme = "default";
		
				if(!$scope.parentobj){
					$scope.parentobj = {};
				}
				if(!$scope.parentobj.privatedata){
					$scope.parentobj.privatedata = {};
				}
                
                $scope.preferedLang = $translate.proposedLanguage() || 'en_US';
                logService.setlog(["current language is:"+$scope.preferedLang], "debug");
                $scope.changeLanguage = function(lang){
            	    $translate.use(lang);
            	}
                
				$scope.$watch("appIsOnline", function(newValue, oldValue){
				    logService.setlog(["Watch on appIsOnline activated", {newValue:newValue, oldValue:oldValue}], "debug");
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
				
				var showLogin = function(method = ''){
				    if(!method && onlineStatus.isOnline()){
				        method = "usernameLogin";
				    }else if(!method && !onlineStatus.isOnline()){
				        method = 'pinCodeLogin';
				    }
				    return loginService.logout().then(function(logoutresponse){
				        logService.setlog(["Current user loggedout",logoutresponse], "debug");
    				    return loginService.showLogin(method).then(function(response){
    				        $scope.parentobj.privatedata.allgroups = [];
    				        if(response.userId){
        				        return loginService.getLoggedInUserDetails(0).then(function(user){
        				            $scope.parentobj.privatedata.allgroups = user.usersGroup || [];
        				            $scope.$emit("updateMenu",{reload: 1});
        				        });
    				        }else{
    				            return $scope.$emit("updateMenu",{reload: 1});
    				        }
    				    });
				    });
				}
                $scope.$on("updateMenu", function(evt,data, callback){
				    menuService.getAllowedMenus($scope.parentobj.privatedata.allgroups || [], 'usersGroup_groupId').then(function(menu){
				            logService.setlog(["update menu triggered", {data:data}], "debug");
                            $scope.parentobj.privatedata.menu = menu;
                            if(data.reload){
                                $state.reload();
                            }
                            if(callback){
                                callback({menu: menu, showLogin:showLogin, menuClick: $scope.parentobj.menuClick});
                            }
                    });
				});
				
				$scope.$watch(Auth.isLoggedIn, function (value, oldValue) {
				    logService.setlog(["Watch on login state change activated",{oldVal: oldValue, newVal:value}]);
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
						let currentUrl = $location.absUrl();
						if(currentUrl && currentUrl.indexOf('/modal') !== -1){
							let rootState1 = currentUrl.split('/modal0/');
							if(rootState1 && rootState1[0]){
								let rootState2 = rootState1[0].split('/#!/');
								if(rootState2 && rootState2[1]){
									let rootPageCode = rootState2[1];
									$state.go("dynaPage", { "pageCode": rootPageCode});
									logService.setlog(["state is not in root. Switching to root", {currentUrl: currentUrl, rootPageCode: rootPageCode}], "debug");
								}
							}
						}
					}
					if(!$scope.gettingUserData){
						if(oldValue != value || !$scope.parentobj.privatedata.menu){
							$scope.gettingUserData = 1;
							loginService.getLoggedInUserDetails(0).then(function(user){
							    logService.setlog(["Fetching Menu Data ...", {user:user}], "info");
                                    $scope.parentobj.privatedata.allgroups = user.usersGroup || [];
                                    $scope.$emit("updateMenu",{});
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
        		
				$scope.parentobj.menuClick = function (node) {
				    try{	
                    	let pageCode = node.dynaPageMenu_pageId;
                    	let menuAction = node.dynaPageMenu_menuAction || '';
                    	let accessWithoutLogin = node.dynaPageDetails_accessWithoutLogin || 0;
                    				    
                    	logService.setlog(["menu clicked",{node: node, pageCode:pageCode, menuAction:menuAction, accessWithoutLogin: accessWithoutLogin}], "debug");
                    					
                    	if(menuAction){
                    		eval(menuAction);
                    	}else if(pageCode){
                    		$state.go("dynaPage", { "pageCode": pageCode, "accessWithoutLogin": accessWithoutLogin});
                    	}
                    }
                    catch(e){
                    	logService.setlog(["You cannot run the function from this page", {error:e, node: node}], "error");
                    }
				}
});
app.controller('LeftCtrl', function ($mdSidenav, logService) {
    let vm = this;
	vm.close = function () {
		$mdSidenav('mainsidemenuholder').close()
		.then(function () {
			logService.setlog("close LEFT is done", "debug");
		});
	};
});

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

app.constant('globals', {
	debug: 1,
	roundingMethod: {
		type: 'round',
		exp: -2 
	}
});


app.factory('localDb', ['Loki', '$q', 'globals', 'lodash', 'logService', '$interval', '$timeout', 'pouchDB', function(Loki, $q, globals, lodash, logService, $interval, $timeout, pouchDB) {
	var dbFunctions = {
		checkDatabaseInitialization: function(){
			logService.setlog("Checking for offlineDb_Loading");
			return $q(function(resolve, reject){
				if(offlineDb_Loading){
					reject();
				}else{
					resolve();
				}
			});
		},
		listCollections: function(indexdb){
			if(!offlineDb && indexdb){
				offlineDb = indexdb;
				logService.setlog("database listCollections creating new instance");
			}
			let collections = offlineDb.listCollections();
			return collections;
		},
		initilizeDb: function(engine='lokijs'){
			return $q(function (resolve, reject) {
				if(!offlineDb && !offlineDb_Loading){
					offlineDb_Loading = true;
					logService.setlog("database initilizeDb creating new instance during initialization");
					if(engine=='lokijs'){
						let idbAdapter = new LokiIndexedAdapter('pasajPOS');
						// use paging only if you expect a single collection to be over 50 megs or so
						let paAdapter = new Loki.LokiPartitioningAdapter(idbAdapter, { paging: false });
						offlineDb = new Loki('pasaj.db',{
							adapter : paAdapter,
							autoload: false,
							autosave: false,
							throttledSaves: true
						});
					
						offlineDb.loadDatabase({}, function(resultx1) {
							let collectionCount = this.listCollections(offlineDb).length;
							logService.setlog("database: number of collections: "+collectionCount);
							if(!collectionCount){
								this.createAllTables(["itemsDetail", "loggedinUser", "customers", "salesHeader", "functions", "functionGroup", "tillSettings", "tillOperation", "tableMaps", "dataToExport", "storeUsers", "paymentMethods", "tableView", "freeTable", "storesDetail", "groupsDetail", "tillsDetail", "promotionsDetail", "markdownsDetail", "dynaPages", "dynaPageMenu", "localTable", "brandsDetail", "taxDetail", "templatesDetail", "dynaPageFunctionAccessValidator"], offlineDb);
								this.saveDb(offlineDb).then(function(saveRes){
									offlineDb.loadDatabase({}, function(result) {
										offlineDb_Loading = false;
										resolve(offlineDb);
									}, function(err){
										logService.setlog(["Error happened in loading databasex1",{err: err, saveRes:saveRes, offlineDb:offlineDb}], "error");
										offlineDb_Loading = false;
										resolve({});
									});
								}, function(err){
									logService.setlog(["Error happened in loading databasex2",{err: err}], "error");
									offlineDb_Loading = false;
									resolve({});
								});
							}else{
								offlineDb.loadDatabase({}, function(result) {
									offlineDb_Loading = false;
									resolve(offlineDb);
								}, function(err){
									logService.setlog(["Error happened in loading databasex3",{err: err}], "error");
									offlineDb_Loading = false;
									resolve({});
								});
							}
						}.bind(this));
					}else if(engine=='pouchdb'){
						offlineDb = pouchDB('pasaj.db');
					}
				}else if(!offlineDb && offlineDb_Loading){
					let tag = Date.now();
					logService.setlog("database called before starting initialization. tag:"+tag);
					let n = 0;
					let timer = $interval(function () {
						this.checkDatabaseInitialization().then(function(){
							$interval.cancel(timer);
							this.initilizeDb().then(function(offlineDb){
								logService.setlog("database called before starting initialization released. tag:"+tag);
								resolve(offlineDb);
							}, function(e){
								logService.setlog(["database called before starting initialization error. tag:"+tag,{e:e}], "error");
							});
						}, function(){
							n = n + 1;
							if(n>20){
								$interval.cancel(timer);
								this.initilizeDb().then(function(offlineDb){
									logService.setlog("database called before starting initialization releasedx2. tag:"+tag);
									resolve(offlineDb);
								}, function(e){
									logService.setlog(["database called before starting initialization errorx2. tag:"+tag,{e:e}], "error");
								});
							}
						}.bind(this));
					}.bind(this), 500);
				}else if(offlineDb && !offlineDb_Loading){
					logService.setlog("database called and had been initialized before");
					resolve(offlineDb);
				}else if(offlineDb && offlineDb_Loading){
					let tag = Date.now();
					logService.setlog("database called before finishing initialization. tag:"+tag);
					let n = 0;
					let timer = $interval(function () {
						this.checkDatabaseInitialization().then(function(){
							logService.setlog("database called before finishing initialization released. tag:"+tag);
							$interval.cancel(timer);
							resolve(offlineDb);
						}, function(){
							n = n + 1;
							if(n>20){
								$interval.cancel(timer);
								logService.setlog("database called before finishing initialization error. tag:"+tag);
								throw "database could not be loaded";
								reject(offlineDb);
							}
						});
					}.bind(this), 500);
				}
			}.bind(this));
		},
		createAllTables: function(tableNames, indexdb){
			logService.setlog("database creating all tables");
			if(!offlineDb && indexdb){
				offlineDb = indexdb;
				logService.setlog("database createAllTables creating new instance");
			}
			for(let i=0; i<tableNames.length;i++){
				this.getTable(tableNames[i], offlineDb);
			}
		},
		createTables: function(tableName, indexdb){
			logService.setlog("database creating table "+tableName);
			if(!offlineDb && indexdb){
				offlineDb = indexdb;
				logService.setlog("database createTables creating new instance");
			}
			let table = {};
			switch(tableName){
				case 'tillId_deviceId':
					table = offlineDb.addCollection('tillId_deviceId', {
						unique: ['tillId_deviceId_tillId'],
						indices: [],
						autoupdate: true
					});
				break;
				case 'inventoryMovementDetail':
					table = offlineDb.addCollection('inventoryMovementDetail', {
						unique: [],
						indices: [],
						autoupdate: true
					});
				break;
				case 'inventoryDetail':
					table = offlineDb.addCollection('inventoryDetail', {
						unique: [],
						indices: ['inventoryShotHeader_inventoryShotId', 'inventoryShotHeader_storeId'],
						autoupdate: true
					});
				break;
				case 'itemsDetail':
					table = offlineDb.addCollection('itemsDetail', {
						unique: ['itemsDetail_itemCode'],
						indices: ['itemsDetail_itemBarcode'],
						autoupdate: true
					});
				break;
				case 'deviceDetails':
					table = offlineDb.addCollection('deviceDetails', {
						unique: ['deviceDetails_deviceId'],
						indices: [],
						autoupdate: true
					});
				break;
				case 'taxDetail':
				    table = offlineDb.addCollection('taxDetail', {
						unique: ['taxDetail_taxId'],
						indices: [],
						autoupdate: true
					});
				break;
				case 'templatesDetail':
				    table = offlineDb.addCollection('templatesDetail', {
						unique: ['templatesDetail_templateId'],
						indices: [],
						autoupdate: true
					});
				break;
				case 'dynaPageFunctionAccessValidator':
				    table = offlineDb.addCollection('dynaPageFunctionAccessValidator', {
						unique: ['dynaPageFunctionAccessValidator_validatorId'],
						indices: [],
						autoupdate: true
					});
				break;
				case 'brandsDetail':
				    table = offlineDb.addCollection('brandsDetail', {
						unique: ['brandsDetail_brandId'],
						indices: [],
						autoupdate: true
					});
				break;
				case 'loggedinUser':
					table = offlineDb.addCollection('loggedinUser', {
						unique: ['usersDetail_userId'],
						indices: ['pinCode'],
						autoupdate: true
					});
				break;
				case 'customers':
					table = offlineDb.addCollection('customers', {
						unique: ['usersDetail_userId'],
						indices: ['usersDetail_cellphone'],
						autoupdate: true
					});
				break;
				case 'usersDetail':
					table = offlineDb.addCollection('usersDetail', {
						unique: ['usersDetail_userId'],
						indices: [],
						autoupdate: true
					});
				break;
				case 'salesHeader':
					table = offlineDb.addCollection('salesHeader', {
						unique: ['salesHeader_offlineSalesHeaderId'],
						indices: ['salesHeader_offlineReceiptNumber'],
						autoupdate: true
					});
				break;
				case 'storesDetail':
				    table = offlineDb.addCollection('storesDetail', {
						unique: ['storesDetail_storeId'],
						indices: ['storesDetail_storeCode'],
						autoupdate: true
					});
				break;
				case 'functions':
					table = offlineDb.addCollection('functions', {
						unique: ['dynaPageFunction_functionId'],
						indices: [],
						autoupdate: true
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
						unique: ['promotionsDetail_promotionId'],
						autoupdate: true
					});
				break;
				case 'markdownsDetail':
				    table = offlineDb.addCollection('markdownsDetail', {
						unique: ['markdownsDetail_markdownId'],
						autoupdate: true
					});
				break;
				case 'paymentMethods':
					table = offlineDb.addCollection('paymentMethods', {
						unique: ['paymentMethods_methodId'],
						autoupdate: true
					});
				break;
				
				case 'tillOperation':
					table = offlineDb.addCollection('tillOperation', {
						unique: ['operationId'],
						indices: ['operationType'],
						autoupdate: true
					});
				break;
				case 'tableView':
					table = offlineDb.addCollection('tableView', {
						unique: ['tableViewDetail_viewId'],
						indices: ['tableViewDetail_tableCode'],
						autoupdate: true
					});
				break;
				case 'freeTable':
					table = offlineDb.addCollection('freeTable', {
						unique: ['dynaPageFreeTable_propertyCode'],
						indices: ['dynaPageFreeTable_categoryCode'],
						autoupdate: true
					});
				break;
				case 'tillsDetail':
				    table = offlineDb.addCollection('tillsDetail', {
						unique: ['tillsDetail_tillId'],
						indices: [],
						autoupdate: true 
					});
				break;
				case 'tableMaps':
					table = offlineDb.addCollection('tableMaps', {
						unique: ['dynaPageTableMap_localTable'],
						indices: [],
						autoupdate: true 
					});
				break;
				case 'dataToExport':
					table = offlineDb.addCollection('dataToExport', {
						unique: ['dataId'],
						indices: ['serverGroupName'],
						autoupdate: true 
					});
				break;
				case 'storeUsers':
					table = offlineDb.addCollection('storeUsers', {
						unique: ['usersIdstoresId_userId'],
						indices: ['usersIdstoresId_storeId'],
						autoupdate: true
					});
				break;
				case 'groupsDetail':
					table = offlineDb.addCollection('groupsDetail', {
						unique: ['groupsDetail_groupId'],
						indices: [],
						autoupdate: true 
					});
				break;
				case 'dynaPages':
				    table = offlineDb.addCollection('dynaPages', {
						unique: ['dynaPageDetails_pageId'],
						indices: [],
						autoupdate: true 
					});
				break;
				case 'dynaPageMenu':
			        table = offlineDb.addCollection('dynaPageMenu', {
						unique: ['dynaPageMenu_menuCode'],
						indices: ['dynaPageMenu_pageId'],
						autoupdate: true 
					});
				break;
				case 'functionGroup':
			        table = offlineDb.addCollection('functionGroup', {
						unique: ['dynaPageFunctionId_usersGroupId_id'],
						indices: ['dynaPageFunctionId_usersGroupId_functionId', 'dynaPageFunctionId_usersGroupId_groupId', 'dynaPageFunctionId_usersGroupId_actionCode'],
						autoupdate: true 
					});
				break;
				case 'localTable':
			        table = offlineDb.addCollection('localTable', {
						unique: ['name'],
						indices: [],
						autoupdate: true 
					});
				break;
			}
			return table;
		},
		getTable: function(tableName, indexdb){
			logService.setlog("database get table "+tableName);
			if(!offlineDb && indexdb){
				offlineDb = indexdb;
				logService.setlog("database getTable creating new instance");
			}
			let table = {};
			table = offlineDb.getCollection(tableName);
			if(!table && tableName && offlineDb){
				table = this.createTables(tableName, offlineDb);
			}
			return table;
		},
		clearTable: function(tableName, table=undefined, indexdb=undefined){
			logService.setlog("database clear table "+tableName);
			if(!offlineDb && indexdb){
				offlineDb = indexdb;
				logService.setlog("database clearTable creating new instance");
			}
			if(!table && tableName && offlineDb){
				let table = {};
				table = this.getTable(tableName, table, offlineDb);
			}
			table.chain().remove();
			table.removeDataOnly();
			return table;
		},
		updateOrInsert: function(tableName, items=[], table=undefined, indexdb=undefined){
			logService.setlog("database updateOrInsert table "+tableName);
		    let itemsToInsert = [];
    	    let itemsToUpdate = [];
		    try{
				if(!offlineDb && indexdb){
					offlineDb = indexdb;
					logService.setlog("database updateOrInsert creating new instance");
				}
		        let result = {};
    			if(items.length){
        			if(!table && tableName && offlineDb){
        				table = this.getTable(tableName, offlineDb);
        			}
        			for(let i=0; i<items.length;i++){
        			    let oldItem =  table.findOne(items[i].condition);
        			    if(oldItem){
        			        let newItem = lodash.merge(oldItem, items[i].value);
        			        itemsToUpdate.push(newItem);
        			    }else{
        			        itemsToInsert.push(items[i].value);
        			    }
        			}
        			let insertResult = table.insert(itemsToInsert);
        			let updateResult = table.update(itemsToUpdate);
        			return {result: {insertResult:insertResult, updateResult:updateResult}, status: 1};
    			}
		    }
		    catch(e){
			    logService.setlog(["error when insert or update in: "+tableName,{itemsToUpdate:itemsToUpdate, itemsToInsert: itemsToInsert, e:e}], "error");
			    return ({result: {}, status: false});
			}
		},
		insertItem: function(tableName, itemsToInsert=[], table=undefined, indexdb=undefined){
			logService.setlog("database insert item table "+tableName);
		    try{
				if(!offlineDb && indexdb){
					offlineDb = indexdb;
					logService.setlog("database insertItem creating new instance");
				}
                let result = {};
    			if(itemsToInsert.length){
        			if(!table && tableName && offlineDb){
        				table = this.getTable(tableName, offlineDb);
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
		updateItem: function(tableName, itemsToUpdate=[], table=undefined, indexdb=undefined){
			logService.setlog("database update item table "+tableName);
			try {
				if(!offlineDb && indexdb){
					offlineDb = indexdb;
					logService.setlog("database updateItem creating new instance");
				}
			    let result = {};
				if(itemsToUpdate.length){
					if(!table && tableName && offlineDb){
						let table = {};
						table = this.getTable(tableName, offlineDb);
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
		deleteItem: function(tableName, itemsToDelete=[], table=undefined, indexdb=undefined){
			logService.setlog("database delete item table "+tableName);
			try {
				if(!offlineDb && indexdb){
					offlineDb = indexdb;
					logService.setlog("database deleteitem creating new instance");
				}
			    let result = {};
				if(itemsToDelete.length){
					if(!table && tableName && offlineDb){
						let table = {};
						table = this.getTable(tableName, offlineDb);
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
		selectItem: function(tableName,key_value,limit=1, table=undefined, parameters=undefined, indexdb= undefined, debug=0){
			logService.setlog("database select item table "+tableName);
			if(!offlineDb && indexdb){
				offlineDb = indexdb;
				logService.setlog("database selectItem creating new instance");
			}
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
		advancedSelectItem: function(tableName, searchCondition={}, indexdb=undefined, parameters={}, table=undefined, whereFunction=undefined, debug=0){
			logService.setlog("database advanced select item table "+tableName);
		    let results;
			if(!offlineDb && indexdb){
				offlineDb = indexdb;
				logService.setlog("database advancedSelectItem creating new instance");
			}
			if(!table && tableName && offlineDb){
				table = {};
				table = this.getTable(tableName, offlineDb);
			}
			
			results = table.chain();
			if(lodash.size(searchCondition)){
			    results = results.find(searchCondition);
			}

            if(whereFunction){
                results = results.where(whereFunction);
            }
            
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
			
			logService.setlog(['advancedSelectItem', {tableName:tableName, table:table,searchCondition:searchCondition, parameters:parameters, results:results, whereFunction:whereFunction}], "debug");
			return results;
		},
		saveDb: function(indexdb, collectionName=""){
			if(!offlineDb){
				offlineDb = indexdb;
				logService.setlog("database save creating new instance");
			}
			logService.setlog(["database save called", {offlineDb: offlineDb}]);
			return $q(function (resolve, reject) {
				$timeout(function(){
				try {
					logService.setlog("Database save try");
					offlineDb.throttledSaveDrain(function (success) {
						logService.setlog(["database save started",{success:success}]);
						if(success){
							let msg = "";
							offlineDb.saveDatabase(function(msg){
								logService.setlog(["database save finished",{msg:msg}]);
								resolve({status: 1, msg: ""});
							}, function(msg){
								logService.setlog(["database save failed",{msg:msg}]);
								resolve({status: 1, msg: msg});
							});
							//offlineDb.saveDatabase();
							//logService.setlog(["database save success",{msg:msg}]);
							//resolve({status: 1, msg: ""});
						}else{
							logService.setlog("Database: An error prevented saving data", "error");
							resolve({status: 0, msg: "Data not Saved"});
						}
					}, { recursiveWaitLimit: true, recursiveWaitLimitDuration: 5000 });
				}
				catch(err) {
					logService.setlog(["database save catch failed",{err:err}], "error");
					resolve({status:0, msg: err.message});
				}
				},0);
			});
		}
	}
	return dbFunctions;
}]);

app.service('externalDevice', function(httpService, $q, localDb, globals){
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
app.service('gridsterService', function (lodash, logService) {
    this.initGridster = function(content={}){
		logService.setlog(["gridsterService.initGridster:: Initializing gridster",{content:content}], "debug");
        if(!content){
			content = {};
		}
		if(!content.options){
			content.options = {};
		}
		content.options.gridType = content.options.gridType || "fit";
		content.options.margin = content.options.margin || 3;
		content.options.compactType = content.options.compactType || "none";
		content.options.minCols = content.options.minCols || 5;
		content.options.maxCols = content.options.maxCols || 30;
		content.options.minRows = content.options.minRows || 5;
		content.options.maxRows = content.options.maxRows || 30;
		content.options.outerMargin = content.options.outerMargin || true;
		content.options.scrollSensitivity = content.options.scrollSensitivity || 10;
		content.options.pushResizeItems = content.options.pushResizeItems || false;
		content.options.disableWindowResize = content.options.disableWindowResize || false;
		content.options.margins = content.options.margins || [];
		content.options.customCss = content.options.customCss || "";
		
		if(typeof content.options.draggable === "undefined") {
			content.options.draggable = {
				enabled: false,
				delayStart: 0, 
				ignoreContentClass: "gridster-item-content"
			};
		}
		if(typeof content.options.resizable === "undefined") {
			content.options.resizable = {
				enabled: false, 
				delayStart: 0
			};
		}
		if(typeof content.options.disablePushOnDrag === "undefined") {
			content.options.disablePushOnDrag = true;
		}
		if(typeof content.options.disablePushOnResize === "undefined") {
			content.options.disablePushOnResize = true;
		}
		if(typeof content.options.pushItems === "undefined") {
			content.options.pushItems = false;
		}
		if(typeof content.options.swap === "undefined") {
			content.options.swap = false;
		}
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

app.service('errorHandling', function(httpService, $q, $log, localDb, globals){
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
    var currentMenuCodes = [];
    var currentMenu = [];
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
        logService.setlog(["Getting allowed Menus for groups", {userGroups: userGroups, groupProperty: groupProperty, currentMenu: currentMenu, currentMenuCodes:currentMenuCodes}], "debug");
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
                    'dynaPageMenu_pageId' : { '$containsAny' : allowedPageIds },
                    dynaPageMenu_status: 1
                };
                logService.setlog(["Searching for Allowed Menus in dynaPageMenu", {dynaPagesMenu_searchCondition: dynaPagesMenu_searchCondition, db: db}], "debug");
                let allowedMenus = localDb.advancedSelectItem('dynaPageMenu', dynaPagesMenu_searchCondition, db, {sortBy: 'dynaPageMenu_menuCode'});
                logService.setlog(["Allowed Menus for user", {allowedPageIds:allowedPageIds, allowedMenus:allowedMenus}], "debug");
                
                //Check of current allowed menus are the same as fetched allowed menus
                let menuCodes = allowedMenus.map(m=>m.dynaPageMenu_menuCode);
                if(menuCodes.length){
                    if(lodash.isEqual(currentMenuCodes, menuCodes)){
                        return currentMenu;
                    }else{
                        currentMenuCodes = menuCodes;
                    }
                }
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
            menu = lodash.sortBy(menu, 'dynaPageMenu_menuOrder');
            if(menu.length){
                currentMenu = menu;
            }
            return menu;
        }.bind(this));
    }
});

app.service('logService', function(lodash, $log){
    return {
        setlog: setlog,
        clearlog: clearlog
    };
    function setlog(msg='', type='info', pageIsLoading=0, showToUser=1) {
        //type: info, debug, error, warning
		if(type == "error"){
			$log.error(msg);
		}else{
			$log.debug(msg);
		}
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
			return localDb.initilizeDb().then(function(db){
				let tableMapsDb = localDb.getTable('tableMaps', db);
				if(localTableName.length && onlineStatus.isOnline()){
					return this.downloadTableMaps(localTableName, tableMapsDb, db);
				}else{
					let res = {status: 1, msg: '', localTableName: localTableName, tableMapsDb: tableMapsDb, db:db};
					return res;
				}
			}.bind(this));
	},
	this.getLastUpdateTime = function(db, localTableName, defaultDaysLimit=0, searchCondition={}){
	    let defaulttime;
        if(defaultDaysLimit > 0){
        	defaulttime = moment().subtract(parseInt(defaultDaysLimit), "days").utc().format("YYYY-MM-DD hh:mm:ss");
        }
        let lastUpdateTime = defaulttime || "1900-01-01 00:00:00";
        let epocheTime;
        
        if(!searchCondition){
        	searchCondition = {};
        }
        let parameters = {
        	"limit": 1,
        	"sortBy": "meta.created",
        	"sortIsDesc": true
        };
        
        let lastCreatedRecord = localDb.advancedSelectItem(localTableName, searchCondition, db, parameters);
        if(lastCreatedRecord && lastCreatedRecord.length){
        	var lastCreatedTime = lastCreatedRecord[0].meta.created;
        
        	let parameters = {
        		"limit": 1,
        		"sortBy": "meta.updated",
        		"sortIsDesc": true
        	};
        	let lastUpdatedRecord = localDb.advancedSelectItem(localTableName, searchCondition, db, parameters);
        	let lastUpdatedTime = lastUpdatedRecord[0].meta.updated;
        
        	epocheTime = Math.max(lastCreatedTime, lastUpdatedTime==lastUpdatedTime?-Infinity:lastUpdatedTime);
        }
        if(epocheTime){
        	lastUpdateTime = moment(epocheTime).utc().format('YYYY-MM-DD hh:mm:ss');
        }
        
        return lastUpdateTime;
	}
	this.downloadTableMaps = function(localTableName=[], tableMapsDb, db){
		var that = this;
		let onlineInsertTime = {};
		for(var i=0; i<localTableName.length; i++){
		    let condition = {
		        dynaPageTableMap_localTable: localTableName[i]
		    };
			onlineInsertTime[localTableName[i]] = this.getLastUpdateTime(db, 'tableMaps', 0, condition);
		}
			
		let properties = {
			"columnMap": 1
		};
		var tableMapDownloadParameters = {
			action: 'getTableMap',
			properties: properties,
			localTable: localTableName,
			onlineInsertTime_start: onlineInsertTime,
			debug: globals.debug
		};
		return httpService.request({
			method : "POST",
			url : "/dynaPage.php",
			data: tableMapDownloadParameters,
			headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
		}).then(function submitSuccess(response) {
		    logService.setlog(["Download table maps response", {response:response}], "debug");
			if(response.data.status){
				var tableMapsToInsert = [];
				var tableMapsToUpdate = [];
				var tableMapsToDelete = [];
				if(response.data.tableMapDetails){
					let tableMapDetails = response.data.tableMapDetails;
					tableMapDetails = that.readTableMap(tableMapDetails);
					for(var i=0; i<tableMapDetails.length; i++){
					    let localTableName_i = tableMapDetails[i]['dynaPageTableMap_localTable'];
					    let localTableNameDb = localDb.getTable(localTableName_i, db);
					    logService.setlog(["Clearing local table",{localTableName:localTableName_i, localTableNameDb:localTableNameDb}], "debug");
					    localTableNameDb.clear();
    	                logService.setlog("data of table "+localTableName_i+ " deleted, because its table map was updated", "debug");
						let oldRecord = tableMapsDb.findOne({dynaPageTableMap_localTable:localTableName_i});
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
	                logService.setlog(["importing tableMaps",{tableMapsToUpdate: tableMapsToUpdate, tableMapsToInsert: tableMapsToInsert, tableMapsToDelete: tableMapsToDelete, tableMapsDb:tableMapsDb}], "debug");
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
					return localDb.saveDb(db).then(function(saveRes){
						logService.clearlog();
					    return ({status: 1, msg: '', localTableName: localTableName, tableMapsDb: tableMapsDb, db:db, saveRes:saveRes});
					});
				}else{
				    return ({status: 1, msg: '', response: response, db:db, localTableName:localTableName, saveRes:1, tableMapsDb: {}});
				}
			}else{
				return ({status: 1, msg: '', response: response, db:db, saveRes:1, localTableName:localTableName, tableMapsDb: {}});
			}
		}, function submitError(response) {
			logService.setlog(response, "error",0);
			return ({status: 0, msg: response, localTableName: localTableName, tableMapsDb: {}, db:db, saveRes:0});
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
    				postParameters.debug = globals.debug;
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
        								logService.setlog(["preparing data for import for "+localTableName, {preparedRecords: preparedRecords}], "debug");
        								recordsToInsert = preparedRecords.recordsToInsert;
        								recordsToUpdate = preparedRecords.recordsToUpdate;
        								recordsToDelete = preparedRecords.recordsToDelete;
										logService.setlog(["prepared data for import for "+localTableName, {recordsToInsert: recordsToInsert,recordsToUpdate:recordsToUpdate,recordsToDelete:recordsToDelete}], "debug");
    							    }
    							}else if(needDataTransformation){
    							    logService.setlog(["needDataTransformation was set to "+needDataTransformation+", but columnMap is not defined for "+localTableName,{columnMap: columnMap, 'dataField name for data': dataField, 'raw data': data, needDataTransformation: needDataTransformation, 'tableMap to get dynaPageTableMap_columnMap as columnMap': tableMap, 'tableMapDb to look for tableMap by dynaPageTableMap_localTable=localTableName': tableMapDb}], "warning");
    							}
    							let databaseChanged = 0;
    							logService.setlog(["saving prepared data to database: "+databaseChanged, {recordsToInsert:recordsToInsert, recordsToUpdate:recordsToUpdate, recordsToDelete:recordsToDelete}], "debug");
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
    							logService.setlog("saving prepared data", "debug");
    							if(databaseChanged){
									logService.setlog("saving database: "+databaseChanged, "debug");
    							    localDb.saveDb(db).then(function(saveRes){
										logService.setlog(["prepared data saved", {saveRes: saveRes}], "debug");
    							        resolve({status: 1, msg: '', importDb: importDb, db: db, response: response.data, saveRes:saveRes, rows:[], columnMap: {}});
    							    }, function(error){
										resolve({status: 0, msg: error, importDb: importDb, db: db, response: response.data, saveRes:0, rows:[], columnMap: {}});
									});
    							}else{
									logService.setlog("database not changed","debug");
    							    resolve({status: 1, msg: '', importDb: importDb, db: db, response: response.data, rows: rows, columnMap: columnMap, saveRes:1});
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
        			let deleteStatus = 0;
        			if(deleteStatusObject){
        			    deleteStatus = deleteStatusObject.value || 0;
        			}
        			if(statusField && rows[i][statusField] === deleteStatus){
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
				if(statusField && newRecord[counter][statusField] === deleteStatus){
					deleteRecord = 1;
				}
			}
			
			if(deleteRecord){
				continue;
			}
			if(foundOldRowIndex >-1){
			    for(var columnName in oldRecord[foundOldRowIndex]){      // for every property in obj1 
			        if((columnName !== null) && (columnName in newRecord[counter]) && (typeof oldRecord[foundOldRowIndex][columnName] === "object" || Array.isArray(oldRecord[foundOldRowIndex][columnName]))){
			        	if(oldRecord[foundOldRowIndex][columnName] && typeof oldRecord[foundOldRowIndex][columnName] === "object" && oldRecord[foundOldRowIndex][columnName].constructor === Object){
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
    			for(let i=0; i<rows.length; i++) {
    				let result = this.formatImportDataRow(rows[i], mapId, tableMap);
    				if(result.parentServerColumns && result.parentServerColumns.length){
    				    let reverseResultColumns = result.parentServerColumns.reverse();
    				    let index = [];

    				    for(let j=0;j<reverseResultColumns.length;j++){
    				        let parentLocalColumn;
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
                    		    let index_log = angular.copy(index);
            				    logService.setlog(["finding index for "+mapId+" on joinValue "+resultColumnDetails.joinValue, { joinValue: resultColumnDetails.joinValue, mapId: angular.copy(mapId), resultColumnDetails: resultColumnDetails, result: angular.copy(result), formattedData: angular.copy(formattedData), index: index_log, parentCollectionsMap: parentCollectionsMap, parentLocalColumn: angular.copy(parentLocalColumn), parentMapId: angular.copy(parentMapId)}], "debug");
            				    try{
            				        //changed 7/9/2019 from formattedData to [] and rolledback because when index=[] then get returns third parameter but we want formattedData, then changed it to two ifs
            				        if(index.length){
            					        index_element = lodash.get(formattedData, index, []).findIndex(element=>element[resultColumnDetails.parentServerColumnName] == resultColumnDetails.joinValue);
            				        }else{
            				            index_element = formattedData.findIndex(element=>element[resultColumnDetails.parentServerColumnName] == resultColumnDetails.joinValue);
            				        }
            					    logService.setlog(["1.Found index for "+angular.copy(mapId)+" on joinValue "+resultColumnDetails.joinValue, {index_element: angular.copy(index_element)}], "debug");
            				    }
            				    catch(e){
            				        logService.setlog([e, "The above error was thrown with the following inputs:", {functoin: "formatDataForImport::findIndex", joinValue: resultColumnDetails.joinValue, mapId: angular.copy(mapId), resultColumnDetails:resultColumnDetails, result: result, formattedData: angular.copy(formattedData), index: angular.copy(index), data: lodash.get(formattedData, index, formattedData)}], "error");
            				    }
            					//debug:
            					//1- element[result.parentServerColumnName] should be element[result.parentLocalColumnName]
            					let no_index_element = 0;
            					if(index_element<0){
            					    no_index_element = 1;
            					    //Changed on 7/9/2019 do below in two steps
            					    let index_element_container = lodash.get(formattedData, index, undefined);
            					    if(index_element_container){
            					        index_element = index_element_container.length;
            					    }
            					    logService.setlog(["2.Found index for "+mapId+" on joinValue "+resultColumnDetails.joinValue, {index_element:angular.copy(index_element), no_index_element:angular.copy(no_index_element)}], "debug");
            					}//else{
            					//Changed on 7/9/2019 added if and removed else
            					if(typeof index_element !== 'undefined' && index_element > -1){
            					    index.push(index_element);
            					}
            					//}

            					if(parentLocalColumn){
            					    index.push(parentLocalColumn);
            					}
            					logService.setlog(["3.Found index for "+angular.copy(mapId)+" on joinValue "+resultColumnDetails.joinValue, {index_element:angular.copy(index_element), no_index_element:angular.copy(no_index_element),index:angular.copy(index), parentLocalColumn:angular.copy(parentLocalColumn)}], "debug");
            					if(no_index_element){
            					    let index_t = angular.copy(index);
            					    index_t.push(resultColumnDetails.parentServerColumnName);
            					    //Changed on 7/9/2019 order of two below line switched
            					    lodash.set(formattedData, index, {});
            					    lodash.set(formattedData, index_t, resultColumnDetails.joinValue);
            					    logService.setlog(["4.Found index for "+angular.copy(mapId)+" on joinValue "+resultColumnDetails.joinValue, {index_element:angular.copy(index_element),  no_index_element:angular.copy(no_index_element), index:angular.copy(index), index_t:angular.copy(index_t), formattedData:angular.copy(formattedData)}], "debug");
            					}
            					if(!lodash.get(formattedData, index, undefined)){
            					    lodash.set(formattedData, index, []);
            					}
    				        }
    				    }
    				    logService.setlog(["5.Found index for "+angular.copy(mapId), {index:angular.copy(index), formattedData:angular.copy(formattedData), result:angular.copy(result)}], "debug");
    				    if(index.length){
            				let formattedDataIndex = lodash.get(formattedData, index);
            				if(formattedDataIndex && Array.isArray(formattedDataIndex)){
            				    formattedDataIndex.push(result.formattedDataRow);
            				}
    				    }else{
    				        formattedData.push(result.formattedDataRow);
    				    }
    				}else if((!result.parentServerColumns || !result.parentServerColumns.length)){
    					formattedData.push(result.formattedDataRow);
    				}
    			}
    		}
    		logService.setlog(["6.Found index for "+angular.copy(mapId), {mainMap:angular.copy(mainMap), formattedData:angular.copy(formattedData), data:angular.copy(data), tableMap:angular.copy(tableMap), dataField: angular.copy(dataField)}], "debug");
    		if(lodash.has(mainMap,'collectionsMap')){
    			let collectionsMap = mainMap.collectionsMap;
    			for(var i=0; i<collectionsMap.length; i++){
    				this.formatDataForImport(data, tableMap, collectionsMap[i].mapId, dataField, formattedData);
    			}
    		}
	    }
	    catch(e){
	        //TODO for a multi-query command, if data is update dfor a sub-index but not for hte main index, and in the main index the onlineInsertTime is not joined with the sub-query, data will exist for sub-index but not for main index, and cannot be transformed. how to test: for dynaPageFunctionId_usersGroupId set time of one of records to future. solutions: 1- search in local database for the main data nad update the sub with the received data. 2- add join in the query with all subs in the main
	        $log.error(e);
	        $log.error("The above error was thrown with the following inputs, reason can be the main data is not sent from server, while the dependant data are sent:");
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
		//logService.setlog(["function: formatImportDataRow",{dataRow:dataRow, mapId:mapId, tableMap:tableMap, mainIdColumnDetails:mainIdColumnDetails}], "debug");
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
				let columnType = '';
    			let isRequired = 0;
    			if(columnsMap[t].hasOwnProperty('rules')){
    				let isRequiredObject = columnsMap[t].rules.find(item => item.name == "isRequired");
    				if(isRequiredObject && isRequiredObject.value){
    					isRequired = 1;
    				}
    				columnType = columnsMap[t].rules.find(item => item.name == "type");
					if(columnType && columnType.value){
    					columnType = columnType.value;
    				}
    			}
    
    			let localColumnName = columnsMap[t].localColumn;
    			let serverGroupName = columnsMap[t]['import'].serverGroupName;
    			let serverColumnName = columnsMap[t]['import'].serverColumn;
    			
    			let serverData = lodash.get(dataRow, serverColumnName, undefined);
    			
    			if(serverData !== undefined ){
					logService.setlog(["formatting column data",{columnType: columnType, serverData: serverData}], "debug");
					if(columnType){
						let tempServerData = serverData;
						try{
							if(columnType == "jsonObject"){
								logService.setlog(["parsing json data", {serverData: serverData}], "debug");
								tempServerData = JSON.parse(serverData);
							}else if(columnType == "int"){
								tempServerData = parseInt(serverData);
							}else if(columnType == "float"){
								tempServerData = parseFloat(serverData);
							}
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
							localDb.saveDb(db).then(function(saveRes){
							    tried = 0;
								that.exportToServer(db, allowedTries, limit, start, size-limit, tried);
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

				return localDb.initilizeDb().then(function(db){
					let dataToExportDb = localDb.getTable('dataToExport', db);
					return this.insertForExport(dataToExport, dataToExportDb, db);
				}.bind(this));

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
			localDb.saveDb(db).then(function(saveRes){
			    resolve({status: 1, db: db, dataToExportDb: dataToExportDb, saveRes:saveRes});
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
            				}else if(type == "int"){
            				    let temp = parseInt(localDataRowt);
            					if(temp){
            						localDataRowt = temp;
            					}
            				}else if(type == "float"){
            				    let temp = parseFloat(localDataRowt);
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
app.directive('stringToNumber', function() {
  return {
    require: 'ngModel',
    link: function(scope, element, attrs, ngModel) {
      ngModel.$parsers.push(function(value) {
        return '' + value;
      });
      ngModel.$formatters.push(function(value) {
        return parseFloat(value, 10);
      });
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