app.controller('parentobj.controllers.tillOperation',function($scope, $filter, $window, $http, $cacheFactory, $state, $mdDialog, i18nService, uiGridConstants, $templateCache, guid, NgTableParams, errorHandling, offlineTillOperation, loggedinUser, settings, localStorageService, receiptTemplatesService, Auth) {

	var tillId;
	var AllowedPaymentMethods = [];
	var allMarkdowns = [];
	var paymentMethodBreakdown = [];
	var retryTimes = 2;
	var tillUsers = [];
	$scope.operationType = "";
	$scope.validity = {countInvalid: 0};
	$scope.paymentMethodDetailTemplate = "empty.html";
	$scope.tillStatus = -2;
	
	function downloadOfflineData(){
		$scope.tillStatus = -1;
		settings.getSettingsTable().then(function(dbDetails){
			let settingsDb = dbDetails.settingsDb;
			let db = dbDetails.db;
			let tillDetailsSetting = settings.getSettings('tillDetails', settingsDb, $scope.parentobj.setlog);
			if(!tillDetailsSetting || !tillDetailsSetting.value){
				loggedinUser.getLoggedinUserTable().then(function(loggedinUserDbDetails){
					let loggedinUserDb = loggedinUserDbDetails.loggedinUserDb;
					let loggedInUserDetails = loggedinUser.getLoggedInUser(Auth.getUser(), loggedinUserDb);
					if(loggedInUserDetails && loggedInUserDetails.userId){
						var allowedTills = loggedInUserDetails.allowedTills;
						$scope.selectTill(allowedTills, settingsDb, db);
					}
				});
			}else{
				tillId = tillDetailsSetting.value.tillId;
				$scope.getTillStatus(tillId);
			}
		});
	}
	$scope.initializeScreen = function(){
		if(localStorageService.get("userId")){
			downloadOfflineData();
		}else{
			loggedinUser.showLogin($scope).then(function(response){
				if(response.userId){
					downloadOfflineData();
				}
			});
		}
	}
	$scope.initializeScreen();
	
	$scope.selectTill = function(allowedTills, settingsDb, db){
		$mdDialog.show({
			preserveScope: false,
			controller: selectTillDialogController,
			templateUrl: 'selectTill.html',
			parent: angular.element(document.body),
			clickOutsideToClose:true,
			multiple: true,
			locals: {
				allowedTills: allowedTills,
				settingsDb: settingsDb,
				db: db
			},
		})
		.then(function(tillDetailsResponse) {
			tillId = tillDetailsResponse.tillsDetail_tillId;
			$scope.getTillStatus(tillDetailsResponse.tillsDetail_tillId);
		}, function() {
			
		});
	}
	function selectTillDialogController($scope, $mdDialog, settings, allowedTills, settingsDb, db){
		$scope.allowedTills = allowedTills;
		$scope.clickToClose = function() {
			$mdDialog.cancel();
		};
		$scope.clickToSave = function(){
			settings.setSettings({id: "tillDetails", value: {tillId: $scope.selectedTill.tillsDetail_tillId, tillNumber: $scope.selectedTill.tillsDetail_tillNumber}}, settingsDb, db);
			$mdDialog.hide($scope.selectedTill);
		}
	}
	
	$scope.getTillStatus = function(tillId){
		let tillStatus;
		let tillOperationDetails = [];
		let lastTillPaymentShot = [];
		let tillPaymentMovementDetails = [];
		offlineTillOperation.getTillOperationsDb().then(function(tillOperationDbDetails){
			//sync with online data
			offlineTillOperation.syncTillOperation(tillId, tillOperationDbDetails.tillOperationDb, tillOperationDbDetails.db, $scope.parentobj.setlog).then(function(tillOperationDetails){
				//get latest till operation whenre close or open happened
				tillStatus = offlineTillOperation.getTillStatus(tillId, tillOperationDbDetails.tillOperationDb);
				tillOperationDetails = [];
				if(tillStatus[0] && tillStatus[0]['tillOperation_operationId']){
					tillOperationDetails = offlineTillOperation.getTillOperationDetails(tillStatus[0]['tillOperation_operationId'], tillOperationDbDetails.tillOperationDb);
					if(tillOperationDetails[0]){
						tillOperationDetails = tillOperationDetails[0];
					}
				}
				if(tillOperationDetails[0] && tillOperationDetails[0]['tillPaymentShotDetails']){
					lastTillPaymentShot = tillOperationDetails[0]['tillPaymentShotDetails'];
				}
				if(tillOperationDetails[0] && tillOperationDetails[0]['tillPaymentMovementDetails']){
					tillPaymentMovementDetails = tillOperationDetails[0]['tillPaymentMovementDetails'];
				}
				AllowedPaymentMethods = [];

				if(!tillStatus.length || tillStatus[0].tillOperation_operationType=="close"){
					//till is Closed right now
					$scope.operationType = "open";
					$scope.tillStatus = 0;
					settings.getAvailablePaymentMethodsDb().then(function(availablePaymentMethodsDbResponse){
						settings.syncAvailablePaymentMethods(tillId, availablePaymentMethodsDbResponse.availablePaymentMethodsDb, availablePaymentMethodsDbResponse.db, $scope.parentobj.setlog).then(function(syncAvailablePaymentMethodsResponse){
							if(syncAvailablePaymentMethodsResponse.status){
								AllowedPaymentMethods = availablePaymentMethodsDbResponse.availablePaymentMethodsDb.chain().simplesort("paymentMethodsDetail_currencyId").data();
								let OpeningPaymentMethods = [];
								for(var i=0, len=AllowedPaymentMethods.length; i < len; i++){
									if(AllowedPaymentMethods[i]['paymentMethodsDetail_includeInOpeningShot'] == 1){
										OpeningPaymentMethods.push(AllowedPaymentMethods[i]);
										let methodPaymentShot = $filter('filter')(lastTillPaymentShot, {tillPaymentShot_paymentMethodId: AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']})[0];
										let methodPaymentMovement = $filter('filter')(tillPaymentMovementDetails, {tillPaymentMovement_paymentMethodId: AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']})[0];
										
										if(!$scope.paymentShot){
											$scope.paymentShot = {};
										}
										if(!$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']]){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']] = {};
										}
										if(!$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedQty){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedQty = 0
										}
										if(methodPaymentShot && methodPaymentShot['tillPaymentShot_countedQty']){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedQty = (methodPaymentShot['tillPaymentShot_countedQty'] || 0);
										}
										
										if(!$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']]){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']] = {};
										}
										if(!$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedValue){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedValue = 0;
										}
										if(methodPaymentShot && methodPaymentShot['tillPaymentShot_countedValue']){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedValue = (methodPaymentShot['tillPaymentShot_countedValue'] || 0);
										}
										if(methodPaymentMovement && methodPaymentMovement['tillPaymentMovement_amount']){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedValue = $scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedValue + (methodPaymentMovement['tillPaymentMovement_amount'] || 0);
										}
										
									}
								}
								let initialParams = {
									count: 500 // initial page size
								};
								let initialSettings = {
									// page size buttons (right set of buttons in demo)
									counts: [],
									dataset: OpeningPaymentMethods
								};
								$scope.getMarkdowns(['discount','tillShotDiscrepancy']).then(function(markdowns){
									allMarkdowns = markdowns;
									$scope.discrepancyMarkdowns = $filter('filter')(allMarkdowns, {'markdownsDetail_markdownType' : 'tillShotDiscrepancy'});
									$scope.paymentMethodsTable = new NgTableParams(initialParams, initialSettings);
								});
								
								$scope.syncTillUsers(tillId);
							}
						});
					});
				}else if(tillStatus[0].tillOperation_operationType=="open"){
					//till is Open right now
					$scope.operationType = "close";
					$scope.tillStatus = 1;
					settings.getAvailablePaymentMethodsDb().then(function(availablePaymentMethodsDbResponse){
						settings.syncAvailablePaymentMethods(tillId, availablePaymentMethodsDbResponse.availablePaymentMethodsDb, availablePaymentMethodsDbResponse.db, $scope.parentobj.setlog).then(function(syncAvailablePaymentMethodsResponse){
							if(syncAvailablePaymentMethodsResponse.status){
								AllowedPaymentMethods = availablePaymentMethodsDbResponse.availablePaymentMethodsDb.chain().simplesort("paymentMethodsDetail_currencyId").data();
								let OpeningPaymentMethods = [];
								for(var i=0, len=AllowedPaymentMethods.length; i < len; i++){
									if(AllowedPaymentMethods[i]['paymentMethodsDetail_includeInOpeningShot'] == 1){
										OpeningPaymentMethods.push(AllowedPaymentMethods[i]);
										let methodPaymentShot = $filter('filter')(lastTillPaymentShot, {tillPaymentShot_paymentMethodId: AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']})[0];
										let methodPaymentMovement = $filter('filter')(tillPaymentMovementDetails, {tillPaymentMovement_paymentMethodId: AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']})[0];
										
										if(!$scope.paymentShot){
											$scope.paymentShot = {};
										}
										if(!$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']]){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']] = {};
										}
										if(!$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedQty){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedQty = 0
										}
										if(methodPaymentShot && methodPaymentShot['tillPaymentShot_countedQty']){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedQty = (methodPaymentShot['tillPaymentShot_countedQty'] || 0);
										}
										
										if(!$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']]){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']] = {};
										}
										if(!$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedValue){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedValue = 0;
										}
										if(methodPaymentShot && methodPaymentShot['tillPaymentShot_countedValue']){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedValue = (methodPaymentShot['tillPaymentShot_countedValue'] || 0);
										}
										if(methodPaymentMovement && methodPaymentMovement['tillPaymentMovement_amount']){
											$scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedValue = $scope.paymentShot[AllowedPaymentMethods[i]['paymentMethodsDetail_methodId']].tillPaymentShot_calculatedValue + (methodPaymentMovement['tillPaymentMovement_amount'] || 0);
										}
										
									}
								}
								let initialParams = {
									count: 500 // initial page size
								};
								let initialSettings = {
									// page size buttons (right set of buttons in demo)
									counts: [],
									dataset: OpeningPaymentMethods
								};
								$scope.getMarkdowns(['discount','tillShotDiscrepancy']).then(function(markdowns){
									allMarkdowns = markdowns;
									$scope.discrepancyMarkdowns = $filter('filter')(allMarkdowns, {'markdownsDetail_markdownType' : 'tillShotDiscrepancy'});
									$scope.paymentMethodsTable = new NgTableParams(initialParams, initialSettings);
								});
								
								$scope.syncTillUsers(tillId);
							}
						});
					});
				}else{
					$scope.tillStatus = 1;
				}
			});
		});
	}
	$scope.syncTillUsers = function(tillId){
		loggedinUser.getLoggedinUserTable().then(function(loggedinUserDbDetails){
			let loggedinUserDb = loggedinUserDbDetails.loggedinUserDb;
			let db = loggedinUserDbDetails.db;
			let loggedInUserData = loggedinUser.getLoggedInUser(Auth.getUser(), loggedinUserDb);
			let selectedTillDetails = $filter('filter')(loggedInUserData.allowedTills, {tillsDetail_tillId: tillId})[0];
			loggedinUser.syncTillUsers(selectedTillDetails, loggedinUserDb, db, $scope.parentobj.setlog).then(function(response){
				
			});
		});
	}
	$scope.syncReceiptTemplates = function(tillId){
		receiptTemplatesService.getTillReceiptTemplates('', tillId, $scope.parentobj.setlog).then(function(receiptTemplateResponse){
			
		});
	}
	$scope.getMarkdowns = function(markdownTypes=[]){
		return settings.getSettingsTable().then(function(response1){
			let parameters = ['markdownDetails', 'basicSettings'];
			return settings.syncBasicSettings(response1.settingsDb, response1.db, parameters).then(function(response2){
				if(response2.status){
					return settings.setSettings(response2.msg, response2.settingsDb, response2.db).then(function(response3){
						var markdowns = settings.getSettings('markdownDetails', response2.settingsDb, $scope.parentobj.setlog);
						let basicSettings = settings.getSettings('basicSettings', response2.settingsDb, $scope.parentobj.setlog);
						retryTimes = $filter('filter')(basicSettings.value, {'basicSettings_settingsCode' : 'retryTimes'})[0]['basicSettings_settingsValue'];
						return markdowns.value;
					});
				}
			});
		});
	}
	$scope.createBreakdowns = function(paymentMethod){
		if(paymentMethod['paymentMethodsDetail_priceRounding'] == 1){
			if(!paymentMethodBreakdown){
				paymentMethodBreakdown = [];
			}
			if(!paymentMethodBreakdown[paymentMethod.paymentMethodsDetail_methodId]){
				paymentMethodBreakdown[paymentMethod.paymentMethodsDetail_methodId] = {paymentMethodsDetail_methodId: paymentMethod.paymentMethodsDetail_methodId, breakdown: {}};
			}
			if(paymentMethod.cashTypesDetail[0].cashTypesDetail_cashTypeId && !paymentMethodBreakdown[paymentMethod.paymentMethodsDetail_methodId].breakdown[paymentMethod.cashTypesDetail[0].cashTypesDetail_cashTypeId]){
				for(var i=0, len=paymentMethod.cashTypesDetail.length; i < len; i++){
					if(!paymentMethodBreakdown[paymentMethod.paymentMethodsDetail_methodId].breakdown[paymentMethod.cashTypesDetail[i].cashTypesDetail_cashTypeId]){
					paymentMethodBreakdown[paymentMethod.paymentMethodsDetail_methodId].breakdown[paymentMethod.cashTypesDetail[i].cashTypesDetail_cashTypeId] = {};
					
	paymentMethodBreakdown[paymentMethod.paymentMethodsDetail_methodId].breakdown[paymentMethod.cashTypesDetail[i].cashTypesDetail_cashTypeId].cashTypesDetail_cashTypeId = paymentMethod.cashTypesDetail[i].cashTypesDetail_cashTypeId;
					}
				}
			}
			/*if(!$scope.paymentMethodBreakdown[paymentMethod.paymentMethodsDetail_methodId]){
				$scope.paymentMethodBreakdown[paymentMethod.paymentMethodsDetail_methodId] = paymentMethod.cashTypesDetail;
			}else{
				angular.extend($scope.paymentMethodBreakdown[paymentMethod.paymentMethodsDetail_methodId], paymentMethod.cashTypesDetail);
			}*/

			$scope.cashShot = paymentMethodBreakdown[paymentMethod.paymentMethodsDetail_methodId];
			$scope.paymentMethodBreakdown = paymentMethodBreakdown;
			let initialParams = {
				count: 500 // initial page size
			};
			let initialSettings = {
				// page size buttons (right set of buttons in demo)
				counts: [],
				dataset: paymentMethod.cashTypesDetail
			};
			$scope.cashDetailsTable = new NgTableParams(initialParams, initialSettings);
			$scope.paymentMethodDetailTemplate = "cashDetails.html";
		}
	}
	
	$scope.showPaymentMovements = function(){
		if(!$scope.validity.countInvalid){
			$mdDialog.show({
		   		preserveScope: false,
				controller: DialogController,
				templateUrl: 'paymentMovements.html',
				parent: angular.element(document.body),
				clickOutsideToClose: false,
				locals: {
					paymentMethods: AllowedPaymentMethods,
					setlog: $scope.parentobj.setlog,
					paymentShot: $scope.paymentShot,
					paymentMethodBreakdown: $scope.paymentMethodBreakdown,
					tillId: tillId,
					allMarkdowns: allMarkdowns,
					operationType: $scope.operationType
				},
				multiple: true,
				openFrom: "#showPaymentMovements",
				closeTo: "#showPaymentMovements"
			})
			.then(function() {
				if($scope.tillStatus == 0){
					$scope.tillStatus = 1;
				}else if($scope.tillStatus == 1){
					$scope.tillStatus = 0;
				}
			}, function() {
				
			});
		}
	}

	function DialogController(paymentMethods, setlog, paymentShot, paymentMethodBreakdown, tillId, allMarkdowns, operationType, $scope, $mdDialog, $mdToast, $http) {
		$scope.paymentShot = paymentShot;
		$scope.markdowns = $filter('filter')(allMarkdowns, {'markdownsDetail_markdownType' : 'tillPaymentMovement'});
		$scope.paymentMovements = [];
		
		$scope.formDisabled = 0;
		let includedPaymentMethods = $filter('filter')(paymentMethods, {paymentMethodsDetail_includeInMovement: 1});
		let initialParams = {
			count: 500 // initial page size
		};
		let initialSettings = {
			// page size buttons (right set of buttons in demo)
			counts: [],
			dataset: includedPaymentMethods
		};
		$scope.paymentMovementTable = new NgTableParams(initialParams, initialSettings);
			
		$scope.clickToClose  = function() {
			setlog('',0);
			$mdDialog.cancel();
		};
		$scope.clickToSave = function(retryTime=0){
			let operationId = guid.newuuid('operationId');
			let offlineUserId = Auth.getUser();
			let offlineInsertTime = moment.utc().format("YYYY-MM-DD hh:mm:ss");
			
			//MAKE tillPaymentShotDetails
			var tillPaymentShotDetails = [];
			var tillPaymentDiscrepancy = [];
			for (var key in paymentShot) {
				if (paymentShot.hasOwnProperty(key)) {
					if(!paymentShot[key]['tillPaymentShot_countedQty']){
						paymentShot[key]['tillPaymentShot_countedQty'] = 0;
					}
					if(!paymentShot[key]['tillPaymentShot_countedValue']){
						paymentShot[key]['tillPaymentShot_countedValue'] = 0;
					}
					if(!paymentShot[key]['tillPaymentShot_calculatedQty']){
						paymentShot[key]['tillPaymentShot_calculatedQty'] = 0;
					}
					if(!paymentShot[key]['tillPaymentShot_calculatedValue']){
						paymentShot[key]['tillPaymentShot_calculatedValue'] = 0;
					}
					if(paymentShot[key]['tillPaymentShot_calculatedValue'] != paymentShot[key]['tillPaymentShot_countedValue'] || paymentShot[key]['tillPaymentShot_countedQty'] != paymentShot[key]['tillPaymentShot_calculatedQty']){
						let tillPaymentDiscrepancyElement = {
							"tillPaymentDiscrepancy_id": '',
							"tillPaymentDiscrepancy_tillOperationId": operationId,
							"tillPaymentDiscrepancy_paymentMethodId": key,
							"tillPaymentDiscrepancy_insertBy_userId": '',
							"tillPaymentDiscrepancy_differenceInQty": (paymentShot[key]['tillPaymentShot_calculatedQty'] - paymentShot[key]['tillPaymentShot_countedQty']),
							"tillPaymentDiscrepancy_differenceInValue": (paymentShot[key]['tillPaymentShot_calculatedValue'] - paymentShot[key]['tillPaymentShot_countedValue']),
							"tillPaymentDiscrepancy_markdownId": paymentShot[key]['tillPaymentDiscrepancy_markdownId'],
							"tillPaymentDiscrepancy_insertIp": '',
							"tillPaymentDiscrepancy_onlineInsertTime": '',
							"tillPaymentDiscrepancy_offlineInsertTime": offlineInsertTime,
							"tillPaymentDiscrepancy_offlineUserId": offlineUserId
						};
						tillPaymentDiscrepancy.push(tillPaymentDiscrepancyElement);
					}
					let tillPaymentShotDetailsElement = {
						"tillPaymentShot_id": '',
						"tillPaymentShot_tillOperationId": operationId,
						"tillPaymentShot_paymentMethodId": key,
						"tillPaymentShot_insertBy_userId": '',
						"tillPaymentShot_countedQty": paymentShot[key]['tillPaymentShot_countedQty'],
						"tillPaymentShot_countedValue": paymentShot[key]['tillPaymentShot_countedValue'],
						"tillPaymentShot_insertIp": '',
						"tillPaymentShot_onlineInsertTime": '',
						"tillPaymentShot_offlineInsertTime": offlineInsertTime,
						"tillPaymentShot_offlineUserId": offlineUserId
					};
					tillPaymentShotDetails.push(tillPaymentShotDetailsElement);
				}
			}
			
			//MAKE tillPaymentMovementDetails
			var tillPaymentMovementDetails = [];
			for (var key in $scope.paymentMovements) {
				if ($scope.paymentMovements.hasOwnProperty(key)) {
					if(!$scope.paymentMovements[key]['tillPaymentMovement_amount']){
						$scope.paymentMovements[key]['tillPaymentMovement_amount'] = "0.00";
					}
					if($scope.paymentMovements[key]['tillPaymentMovement_amount'] && $scope.paymentMovements[key]['tillPaymentMovement_amount']!="0.00" && $scope.paymentMovements[key]['tillPaymentMovement_amount'] != 0){
						if(!$scope.paymentMovements[key]['tillPaymentMovement_note']){
							$scope.paymentMovements[key]['tillPaymentMovement_note'] = '';
						}
						if(!$scope.paymentMovements[key]['tillPaymentMovement_markdownId']){
							$scope.paymentMovements[key]['tillPaymentMovement_markdownId'] = '';
						}
						let tillPaymentMovementDetailsElement = {
							"tillPaymentMovement_id": '',
							"tillPaymentMovement_tillOperationId": operationId,
							"tillPaymentMovement_paymentMethodId": key,
							"tillPaymentMovement_insertBy_userId": '',
							"tillPaymentMovement_amount": $scope.paymentMovements[key]['tillPaymentMovement_amount'],
							"tillPaymentMovement_markdownId": $scope.paymentMovements[key]['tillPaymentMovement_markdownId'],
							"tillPaymentMovement_note": $scope.paymentMovements[key]['tillPaymentMovement_note'],
							"tillPaymentMovement_insertIp": '',
							"tillPaymentMovement_onlineInsertTime": '',
							"tillPaymentMovement_offlineInsertTime": offlineInsertTime,
							"tillPaymentMovement_offlineUserId": offlineUserId
						};
						tillPaymentMovementDetails.push(tillPaymentMovementDetailsElement);
					}
				}
			}
			
			//MAKE tillCashShotDetails
			var tillCashShotDetails = [];
			for (var key in paymentMethodBreakdown) {
				if (paymentMethodBreakdown.hasOwnProperty(key)) {
					for (var key2 in paymentMethodBreakdown[key].breakdown){
						if (paymentMethodBreakdown[key].breakdown.hasOwnProperty(key2)) {
							if(!paymentMethodBreakdown[key].breakdown[key2]['countedQty']){
								paymentMethodBreakdown[key].breakdown[key2]['countedQty'] = 0;
							}
							if(!paymentMethodBreakdown[key].breakdown[key2]['countedValue']){
								paymentMethodBreakdown[key].breakdown[key2]['countedValue'] = 0;
							}
							let tillCashShotDetailsElement = {
								"tillCashShot_id": '',
								"tillCashShot_tillOperationId": operationId,
								"tillCashShot_paymentMethodId": key,
								"tillCashShot_insertBy_userId": '',
								"tillCashShot_cashTypeId": key2,
								"tillCashShot_countedQty": paymentMethodBreakdown[key].breakdown[key2]['countedQty'],
								"tillCashShot_countedValue": paymentMethodBreakdown[key].breakdown[key2]['countedValue'],
								"tillCashShot_insertIp": '',
								"tillCashShot_onlineInsertTime": '',
								"tillCashShot_offlineInsertTime": offlineInsertTime,
								"tillCashShot_offlineUserId": offlineUserId
							};
							tillCashShotDetails.push(tillCashShotDetailsElement);
						}
					}
				}
			}
			
			tillOperationDbArray = offlineTillOperation.getTillOperationsDb();
			let tillOperationDb = tillOperationDbArray.tillOperationDb;
			let db = tillOperationDbArray.db;
			let operationToInsert = {
				"tillOperation_id": '',
				"tillOperation_onlineInsertTime": '',
				"tillOperation_insertBy_userId": '',
				"tillOperation_insertIp": '',
				"tillOperation_operationId": operationId,
				"tillOperation_tillId": tillId,
				"tillOperation_offlineInsertTime": offlineInsertTime,
				"tillOperation_offlineUserId": offlineUserId,
				"tillOperation_operationType": operationType,
				"tillPaymentShotDetails": tillPaymentShotDetails,
				"tillCashShotDetails": tillCashShotDetails,
				"tillPaymentDiscrepancyDetails": tillPaymentDiscrepancy,
				"tillPaymentMovementDetails": tillPaymentMovementDetails
			};

			offlineTillOperation.getTillOperationsDb().then(function(tillOperationDbDetails){
				offlineTillOperation.insertTillOperation(operationToInsert, tillOperationDbDetails.tillOperationDb, tillOperationDbDetails.db).then(function(response){
					$mdDialog.hide();
					if(!response.status){
						if(retryTime < retryTimes){
							retryTime = retryTime + 1;
						}else{
							setlog("error Saving details to database. You may experience problems in offline mode");
						}
					}else{
						settings.uploadDataToServer('tillOperation',operationToInsert);
					}
				});
			});
		}
	}
});

app.service('offlineTillOperation', function($http, $q, localDb, localStorageService, globals, errorHandling){
	this.getTillOperationsDb = function(){
			if(!globals.db){
				return localDb.initilizeDb().then(function(db){
					let tillOperationDb = localDb.getTable('tillOperation', db);
					return ({db: db, tillOperationDb: tillOperationDb});
				}.bind(this));
			}else{
				let tillOperationDb = localDb.getTable('tillOperation', globals.db);
				return $q(function (resolve, reject) {
					resolve({db: globals.db, tillOperationDb: tillOperationDb});
				});
			}
	},
	this.getTillStatus = function(tillId, tillOperationDb){
		var tillStatusOperation = tillOperationDb.chain().find({ 
			'$and': [
				{tillOperation_tillId: tillId},
				{tillOperation_operationType: { '$in' : ['close', 'open'] }}
			]
		}).simplesort('tillOperation_offlineInsertTime', true).limit(1).data();
		return tillStatusOperation;
	}
	this.getTillOperationDetails = function(tillOperationId, tillOperationDb){
		let record = localDb.selectItem('tillOperation', {tillOperation_operationId: tillOperationId}, 1, tillOperationDb);
		return record;
	}
	//add tillId to records
	this.syncTillOperation = function(tillId, tillOperationDb, db, setlog){
			var requestTime = moment.utc().format("YYYY-MM-DD HH:mm:ss");
			
			let onlineInsertTime = "";
			if(localStorageService.get('lastSyncTillOperation_'+tillId)){
					onlineInsertTime = localStorageService.get('lastSyncTillOperation_'+tillId);
			}else{
				onlineInsertTime = "";
			}

			var tillOperationDownloadParameters = {
				action: 'getTillOperations',
				getCount: 0,
				tillsDetail_tillId: tillId,
				onlineInsertTime_start: onlineInsertTime
			};

			return $http({
				method : "POST",
				url : "/tillOperation.php",
				data: tillOperationDownloadParameters,
				headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
			}).then(function submitSuccess(response) {
				if(response.data.status){
					localStorageService.set('lastSyncTillOperation_'+tillId, requestTime)
					if(!response.data.partial){
						setlog("Re-saving whole till operation data",1);
						tillOperationDb.chain().remove();
						tillOperationDb.removeDataOnly();
						
						var tillOperation = tillOperationDb.insert(
							response.data.tillOperationData
						);
					}else{
						setlog("Updating new till Operation data",1);
						var i,arrLen = response.data.tillOperationData.length;
						var itemsToUpdate = [];
						var itemsToInsert = [];
						for (i = 0; i < arrLen; i++) {
							let oldRecord = localDb.selectItem('tillOperation', {tillOperation_operationId: response.data.tillOperationData[i]['tillOperation_operationId']}, 1, tillOperationDb);
							if(oldRecord){
								angular.extend(oldRecord,response.data.tillOperationData[i]);
								itemsToUpdate.push(oldRecord);
							}else{
								
								itemsToInsert.push(response.data.tillOperationData[i]);
							}
						}
	
						if(itemsToUpdate.length){
							tillOperationDb.update(itemsToUpdate);
						}
						if(itemsToInsert.length){
							tillOperationDb.insert(itemsToInsert);
						}
					}
					
					setlog("",0);
					return localDb.saveDb(db).then(function(result){
						if(!result.status){
							setlog("You may face issues opening and closing till if offline", 0);	
						}
						let returnResponse = ({status: 1, msg: '', tillOperationDb: tillOperationDb, tillOperationData: response.data.tillOperationData});
						return returnResponse;
					}, function submitError(response) {
						setlog(response, 0);
						return ({status: 0, msg: response, tillOperationDb: tillOperationDb, tillOperationData: tillOperationData});
					});
				}else{
					setlog(response.data.msg,0);
					return ({status: 0, msg: response.data.msg, tillOperationData: [], tillOperationDb: tillOperationDb});
				}
			}, function submitError(response) {
				setlog(response,0);
				return ({status: 0, msg: response, tillOperationData: [], tillOperationDb: tillOperationDb});
			});
	}
	this.insertTillOperation = function(operationToInsert, tillOperationDb, db){
		let operationToInsertArray = [];
		if(!operationToInsert.isArray){
			operationToInsertArray.push(operationToInsert);
		}else{
			operationToInsertArray = operationToInsert;
		}
		return localDb.insertItem('tillOperation', operationToInsertArray, tillOperationDb).then(function(response){
			//insert into online database too
			return localDb.saveDb(db).then(function(result){
				if(!result.status){
					return ({status: 0, response: result});
				}else{
					return ({status: 1, response: response});
				}
			}, function submitError(response) {
				return ({status: 0, response: response});
			});
		}, function (error) {
			return ({status: 0, response: error});  // <-- this is logged to the console
		}).catch(function (error) {
			return errorService.handleError({status: 0, response: error.message});
		});
	};
});