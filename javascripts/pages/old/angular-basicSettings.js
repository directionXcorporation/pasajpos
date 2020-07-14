 app.controller('parentobj.controllers.basicSettings',function($scope, $filter, $window, $http, $cacheFactory, $state, $mdDialog, $mdToast, i18nService, uiGridConstants, $templateCache, $log) {
	$scope.privatedata = {};
	$scope.query = {};
	
	$scope.privatedata.cashDetails = [];
	var initData;
	
	$scope.getDetails = function(gridName){
		if(!$scope.privatedata[gridName] || !$scope.privatedata[gridName].length){
			$scope.query[gridName] = {
				order: '',
				limit: 10,
				page: 1
			};
			$scope.parentobj.setlog('Getting Requested Data...', 1);
			initData = {
				action: 'getDetails',
				parameters: [gridName]
			}
			$http({
				method : "POST",
				data: initData,
				url : "/basicSettings.php",
				headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
			}).then(function submitSuccess(response) {
				$scope.parentobj.setlog('', 0);
				if(response.data.status){
					if(gridName == "cashDetails"){
						if(response.data.data['cashDetails'].cashCategories && (!$scope.cashCategories || !$scope.cashCategories.length)){
							$scope.cashCategories = response.data.data['cashDetails'].cashCategories;
						}
						$scope.privatedata.cashDetails = response.data.data['cashDetails'].record;
					}
					if(gridName == "taxDetails"){
						$scope.privatedata.taxDetails = response.data.data['taxDetails'].record;
					}
					if(gridName == "markdownDetails"){
						if(response.data.data['markdownDetails'].markdownTypes && (!$scope.markdownTypes || !$scope.markdownTypes.length)){
							$scope.markdownTypes = response.data.data['markdownDetails'].markdownTypes;
						}
						$scope.privatedata.markdownDetails = response.data.data['markdownDetails'].record;
					}
					if(gridName == "paymentMethodDetails"){
						if(!$scope.privatedata.paymentExtDetails){
							$scope.privatedata.paymentExtDetails = [];
						}
						if(response.data.data['paymentMethodDetails'].paymentExtDetails && (!$scope.privatedata.paymentExtDetails || !$scope.privatedata.paymentExtDetails.length)){
							$scope.privatedata.paymentExtDetails = response.data.data['paymentMethodDetails'].paymentExtDetails;
						}
						$scope.privatedata.paymentMethodDetails = response.data.data['paymentMethodDetails'].record;
					}
					if(gridName == "basicSettings"){
						$scope.privatedata.basicSettings = {};
						for (var i = 0, len = response.data.data['basicSettings'].record.length; i < len; i++) {
							$scope.privatedata.basicSettings[response.data.data['basicSettings'].record[i]['basicSettings_settingsCode']] = response.data.data['basicSettings'].record[i]['basicSettings_settingsValue'];
						}
					}
				}else{
					$scope.parentobj.setlog(response.data.msg,0);
				}
			}, function submitError(response) {
				$scope.parentobj.setlog(response,0);
				$scope.parentobj.pageIsLoading = 0;
			});
		}
	};
	
	$scope.openEditDetails = function(templateName, record, action='edit', elementId){
		var originalData = angular.copy(record);
		$mdDialog.show({
	   		preserveScope: false,
			controller: DialogController,
			templateUrl: templateName,
			parent: angular.element(document.body),
			targetEvent: record,
			clickOutsideToClose: false,
			locals: {
				originalData: originalData,
				record: record,
				action: action,
				params: {cashCategories: $scope.cashCategories, markdownTypes: $scope.markdownTypes},
				setlog: $scope.parentobj.setlog,
				privatedata: $scope.privatedata,
				openEditDetails: $scope.openEditDetails
			},
			multiple: true,
			openFrom: "#edit_"+elementId,
			closeTo: "#edit_"+elementId
		})
		.then(function() {
			
		}, function() {
			
		});
	};
	
	$scope.saveOtherDetails = function(basicSettings){
		$scope.formDisabled = true;
		let data = {
			action: "saveDetails",
			detailName: "basicSettings",
			changeArray: {changeDetails:1},
			record: basicSettings
		}
		console.log(data);
		if($scope.otherDetails.modified){
			if($scope.otherDetails.$valid){
				$http({
					method : "POST",
					url : "/basicSettings.php",
					data: data,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					console.log(response);
					if(response.data.status){
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
		}else{
			$scope.formDisabled = false;
			showMainformToast('noChangesMade.html', $mdToast);
		}
	}
	
	function DialogController(record, action, setlog, params, privatedata, openEditDetails, $scope, $mdDialog, originalData, $mdToast, $http) {
		var recordscope;
		$scope.privatedata = {};
		$scope.privatedata.record = record;
		$scope.privatedata.paymentExtDetails = privatedata.paymentExtDetails;
		$scope.openEditDetails = openEditDetails;
		if(params.cashCategories){
			$scope.cashCategories = params.cashCategories;
		}
		if(params.markdownTypes){
			$scope.markdownTypes = params.markdownTypes;
		}
		$scope.addNewParameter = function(paramArray){
			if(!Array.isArray(paramArray)){
				paramArray = [];
			}
			let newElement = {
				parameterName: '',
				parameterModel: '',
				parameterType: "dynamic",
				parameterMask: '',
				isSuccessFactor: 0,
				successValue: ''
			};
			paramArray.push(newElement);
		}
		$scope.clickToClose  = function(detailName, editedRecord, checkChangs=1) {
			recordscope = angular.copy($scope.privatedata.record);
			setlog('',0);
			if(angular.equals(editedRecord,originalData) || checkChangs==0){
				$mdDialog.cancel();
			}else{
				confirmDialogClose($mdDialog, 'clickToSave(detailName, editedRecord)', 'clickToClose(detailName, editedRecord, 0)', $scope);
			}
		};
		$scope.clickToSave = function(detailName, editedRecord, statusProperty){
			if(editedRecord[statusProperty] === '0' || editedRecord[statusProperty] === 0){
				var confirm = $mdDialog.confirm()
					.title('Confirm Delete')
					.textContent('You are disabling this record and it will not be shown in the list. Are you sure?')
					.ariaLabel('confirm delete')
					.targetEvent()
					.multiple(true)
					.ok('Yes, disable it!')
					.cancel('No! Do NOT disable!');

				$mdDialog.show(confirm).then(function() {
					$scope.saveData(detailName, editedRecord);
				}, function() {
					showMainformToast('noChangesMade.html', $mdToast);
					$mdDialog.cancel();
				});
			}else if(angular.equals(editedRecord,originalData)){
				showMainformToast('noChangesMade.html', $mdToast);
				$mdDialog.cancel();
			}else{
				$scope.saveData(detailName, editedRecord);
			}
		}
		$scope.saveData = function(detailName, editedRecord){
			$scope.formDisabled = true;
			var changeArray = {};
			if(detailName=="cashDetails"){
				if($scope.cashStatus.modified || action ==="create"){
					changeArray.changeStatus = 1;
				}else{
					delete editedRecord['cashStatus_status'];
				}
				if($scope.cashDetails.modified){
					changeArray.changeDetails = 1;
				}else{
					delete editedRecord['cashTypesDetail_cashName'];
					delete editedRecord['cashTypesDetail_cashValue'];
					delete editedRecord['cashTypesDetail_currencyId'];
					delete editedRecord['cashTypesDetail_cashCategoriesId'];
				}
			}
			if(detailName=="taxDetails"){
				if($scope.taxStatus.modified || action ==="create"){
					changeArray.changeStatus = 1;
				}else{
					delete editedRecord['taxStatus_status'];
				}
				if($scope.taxDetails.modified){
					changeArray.changeDetails = 1;
				}else{
					delete editedRecord['taxDetail_taxName'];
					delete editedRecord['taxDetail_taxPercent'];
					delete editedRecord['taxDetail_taxId'];
				}
			}
			
			if(detailName=="markdownDetails"){
				if($scope.markdownStatus.modified || action ==="create"){
					changeArray.changeStatus = 1;
				}else{
					delete editedRecord['markdownsStatus_status'];
				}
				if($scope.markdownDetails.modified){
					changeArray.changeDetails = 1;
				}else{
					delete editedRecord['markdownsDetail_markdownDescription'];
					delete editedRecord['markdownsDetail_markdownType'];
				}
			}
			if(detailName == "paymentExtDetails"){
				if($scope.paymentExtStatus.modified || action ==="create"){
					changeArray.changeStatus = 1;
				}else{
					delete editedRecord['paymentExtsStatus_status'];
				}
				if($scope.paymentExtDetails.modified){
					changeArray.changeDetails = 1;
				}else{
					delete editedRecord['paymentExtsDetail_extName'];
					delete editedRecord['paymentExtsDetail_baseUrl'];
					delete editedRecord['paymentExtsDetail_method'];
					delete editedRecord['paymentExtsDetail_parameters'];
					delete editedRecord['paymentExtsDetail_silent'];
				}
			}
			if(detailName == "paymentMethodDetails"){
				if($scope.paymentMethodStatus.modified || action ==="create"){
					changeArray.changeStatus = 1;
				}else{
					delete editedRecord['paymentMethodsStatus_status'];
				}
				if($scope.paymentMethodDetails.modified){
					changeArray.changeDetails = 1;
				}else{
					delete editedRecord['paymentMethodsDetail_methodCode'];
					delete editedRecord['paymentMethodsDetail_methodName'];
					delete editedRecord['paymentMethodsDetail_methodDescription'];
					delete editedRecord['paymentMethodsDetail_currencyId'];
					delete editedRecord['paymentMethodsDetail_priceRounding'];
					delete editedRecord['paymentMethodsDetail_sendToExtId'];
					delete editedRecord['paymentMethodsDetail_recieveFromExtId'];
				}
			}
			var data = {
				action: "saveDetails",
				detailName: detailName,
				record: editedRecord,
				changeArray: changeArray
			}

			if($scope[detailName].$valid){
				$http({
					method : "POST",
					url : "/basicSettings.php",
					data: data,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					console.log(response);
					if(response.data.status){
						var success = 0;
						if(detailName=="cashDetails"){
							var newRecord = {};
							if(action ==="create"){
								if(response.data[detailName]['addCashType'].status){
									newRecord['cashTypesDetail_cashTypeId'] = response.data[detailName]['addCashType'].cashTypesDetail_cashTypeId;
									success = 1;
								}else{
									$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['addCashType'].msg;
								}
							}
							if(response.data[detailName]['changeDetails'].status){
								angular.extend(newRecord, editedRecord);
								success = 1;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['changeDetails'].msg;
							}
							if(response.data[detailName]['changeStatus'].status){
								newRecord['cashTypesStatus_status'] = response.data[detailName]['changeStatus'].cashTypesStatus_status;
								success = 1;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['changeStatus'].msg;
							}
							if(success == 1){
								if(action ==="create"){
									privatedata.cashDetails.push(newRecord);
								}else{
									angular.extend(record, newRecord);
								}
							}
						}
						if(detailName=="taxDetails"){
							var newRecord = {};
							if(action ==="create"){
								if(response.data[detailName]['addTax'].status){
									newRecord['taxDetail_taxId'] = response.data[detailName]['addTax'].taxDetail_taxId;
								}else{
									$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['addTax'].msg;
								}
							}
							if(response.data[detailName]['changeDetails'].status){
								angular.extend(newRecord, editedRecord);
								success = 1;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['changeDetails'].msg;
							}
							if(response.data[detailName]['changeStatus'].status){
								newRecord['taxDetail_status'] = response.data[detailName]['changeStatus'].cashTypesDetail_status;
								success = 1;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['changeStatus'].msg;
							}
							if(success == 1){
								if(action ==="create"){
									privatedata.taxDetails.push(newRecord);
								}else{
									angular.extend(record, newRecord);
								}
							}
						}
						if(detailName=="markdownDetails"){
							var newRecord = {};
							if(action ==="create"){
								if(response.data[detailName]['addMarkdown'].status){
									newRecord['markdownsDetail_markdownId'] = response.data[detailName]['addMarkdown'].markdownsDetail_markdownId;
									success = 1;
								}else{
									$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['addMarkdown'].msg;
								}
							}
							if(response.data[detailName]['changeDetails'].status){
								angular.extend(newRecord, editedRecord);
								success = 1;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['changeDetails'].msg;
							}
							if(response.data[detailName]['changeStatus'].status){
								newRecord['markdownsStatus_status'] = response.data[detailName]['changeStatus'].markdownsStatus_status;
								success = 1;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['changeStatus'].msg;
							}
							if(success == 1){
								if(action ==="create"){
									privatedata.markdownDetails.push(newRecord);
								}else{
									angular.extend(record, newRecord);
								}
							}
						}
						if(detailName=="paymentExtDetails"){
							var newRecord = {};
							if(action ==="create"){
								if(response.data[detailName]['addPaymentExt'].status){
									newRecord['paymentExtsDetail_extId'] = response.data[detailName]['addPaymentExt'].paymentExtsDetail_extId;
									success = 1;
								}else{
									$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['addPaymentExt'].msg;
								}
							}
							if(response.data[detailName]['changeDetails'].status){
								angular.extend(newRecord, editedRecord);
								success = 1;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['changeDetails'].msg;
							}
							if(response.data[detailName]['changeStatus'].status){
								newRecord['paymentExtsStatus_status'] = response.data[detailName]['changeStatus'].paymentExtsStatus_status;
								success = 1;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['changeStatus'].msg;
							}
							if(success == 1){
								if(action ==="create"){
									privatedata.paymentExtDetails.push(newRecord);
								}else{
									angular.extend(record, newRecord);
								}
							}
						}
						if(detailName=="paymentMethodDetails"){
							var newRecord = {};
							if(action ==="create"){
								if(response.data[detailName]['addPaymentMethod'].status){
									newRecord['paymentMethodsDetail_methodId'] = response.data[detailName]['addPaymentMethod'].paymentMethodsDetail_methodId;
									success = 1;
								}else{
									$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['addPaymentMethod'].msg;
								}
							}
							if(response.data[detailName]['changeDetails'].status){
								angular.extend(newRecord, editedRecord);
								success = 1;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['changeDetails'].msg;
							}
							if(response.data[detailName]['changeStatus'].status){
								newRecord['paymentMethodsStatus_status'] = response.data[detailName]['changeStatus'].paymentMethodsStatus_status;
								success = 1;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[detailName]['changeStatus'].msg;
							}
							if(success == 1){
								if(action ==="create"){
									privatedata.paymentMethodDetails.push(newRecord);
								}else{
									angular.extend(record, newRecord);
								}
							}
						}
						if(success == 1){
							setlog('',0);
							$mdDialog.hide();
						}
					}else{
						setlog(response.data.msg,0);
						$scope.formDisabled = false;
					}
				}, function submitError(response) {
					setlog(response,0);
					$scope.formDisabled = false;
				});
			}else{
				$scope.formDisabled = false;
				setlog("Errors found that prevented sending form",0);
			}
		}
	}
});

app.service('settings', function($http, $q, $filter, $timeout, localDb, localStorageService, globals, errorHandling, Auth){
	var userId = Auth.getUser();
	this.uploadDataToServer = function(dataName, data){
		if(dataName == 'tillOperation'){
			let uploadData = {
				"action" : "syncCloseOpen",
				"data" : data
			}
			$http({
				method : "POST",
				url : "/tillOperation.php",
				data: uploadData,
				headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
			}).then(function submitSuccess(response) {
				if(response.data.status){
					let error = 0;
					let onlineInsertTime = moment().format('YYYY-MM-DD HH:mm:ss');
					if(response.data.tillOperationResult.status && response.data.tillOperationResult.result && response.data.tillOperationResult.result.tillOperation_insertBy_userId){
						data.tillOperation_insertBy_userId = response.data.tillOperationResult.result.tillOperation_insertBy_userId;
						data.tillOperation_onlineInsertTime =  onlineInsertTime;
						if(response.data.tillPaymentShotResult.result.tillPaymentShot_insertBy_userId){
							for(var i = 0, len = data.tillPaymentShotDetails.length; i < len; i++) {
								data.tillPaymentShotDetails[i].tillPaymentShot_insertBy_userId = response.data.tillPaymentShotResult.result.tillPaymentShot_insertBy_userId;
								data.tillPaymentShotDetails[i].tillPaymentShot_onlineInsertTime = onlineInsertTime;
							}
						}else{
							error = 1;
						}
						if(response.data.tillCashShotResult.result.tillCashShot_insertBy_userId){
							for(var i = 0, len = data.tillCashShotDetails.length; i < len; i++) {
								data.tillCashShotDetails[i].tillCashShot_insertBy_userId = response.data.tillCashShotResult.result.tillCashShot_insertBy_userId;
								data.tillCashShotDetails[i].tillCashShot_onlineInsertTime = onlineInsertTime;
							}
						}else{
							error = 1;
						}
						if(response.data.tillPaymentDiscrepancyResult.result){
							if(response.data.tillPaymentDiscrepancyResult.result.tillPaymentDiscrepancy_insertBy_userId){
								for(var i = 0, len = data.tillPaymentDiscrepancyDetails.length; i < len; i++) {
									data.tillPaymentDiscrepancyDetails[i].tillPaymentDiscrepancy_insertBy_userId = response.data.tillPaymentDiscrepancyResult.result.tillPaymentDiscrepancy_insertBy_userId;
									data.tillPaymentDiscrepancyDetails[i].tillPaymentDiscrepancy_onlineInsertTime = onlineInsertTime;
								}
							}else{
								error = 1;
							}
						
							if(response.data.tillPaymentMovementResult.result.tillPaymentMovement_insertBy_userId){
								for(var i = 0, len = data.tillPaymentMovementDetails.length; i < len; i++) {
									data.tillPaymentMovementDetails[i].tillPaymentMovement_insertBy_userId = response.data.tillPaymentMovementResult.result.tillPaymentMovement_insertBy_userId;
									data.tillPaymentMovementDetails[i].tillPaymentMovement_onlineInsertTime = onlineInsertTime;
								}
							}else{
								error = 1;
							}
						}
					}else{
						error = 1;
					}
					if(error == 1){
						this.retryUploadDataToServer(dataName, data);
					}
				}else{
					this.retryUploadDataToServer(dataName, data);
				}
			}, function submitError(response) {
				this.retryUploadDataToServer(dataName, data);
			});
		}
	}
	this.retryUploadDataToServer = function(dataName, data){
		$timeout(function(){uploadDataToServer(dataName, data);}, 5000);
	}
	this.getSettingsTable = function(){
		if(!globals.db){
			return localDb.initilizeDb().then(function(db){
				let settingsDb = localDb.getTable('settings', db);
				return {settingsDb: settingsDb, db: db};
			});
		}else{
			return $q(function (resolve, reject) {
				let settingsDb = localDb.getTable('settings', globals.db);
				resolve({settingsDb: settingsDb, db: globals.db});
			});
		}
	}
	this.getSettings = function(settingId, settingsDb, setlog){
		var foundSettings = localDb.selectItem('settings', {id: settingId}, 1, settingsDb);
		return foundSettings;
	}
	this.setSettings = function(settingToInsert, settingsDb, db){
		//settingToInsert = {id: settingId, value: settingValue};
		let settingToInsertArray = [];
		let settingToUpdateArray = [];
		if(Array.isArray(settingToInsert)){
			for(var i=0, len=settingToInsert.length; i < len; i++){
				var oldRecord = localDb.selectItem('settings', {id: settingToInsert[i].id}, 1, settingsDb);
				
				if(oldRecord){
					angular.extend(oldRecord, settingToInsert[i]);
					settingToUpdateArray.push(oldRecord);
				}else{
					settingToInsertArray.push(settingToInsert[i]);
				}
				if(settingToInsert[i] && settingToInsert[i].id == "basicSettings" && settingToInsert[i].value){
					let saleSignoutInterval = $filter('filter')(settingToInsert[i].value, {'basicSettings_settingsCode' : "saleSignoutInterval"})[0];
					localStorageService.set("saleSignoutInterval", saleSignoutInterval.basicSettings_settingsValue);
				}
			}
		}else{
			var oldRecord = localDb.selectItem('settings', {id: settingToInsert.id}, 1, settingsDb);
			
			if(oldRecord){
				angular.extend(oldRecord, settingToInsert);
				settingToUpdateArray.push(oldRecord);
			}else{
				settingToInsertArray.push(settingToInsert);
			}
		}
		
		if(settingToInsertArray.length){
				return localDb.insertItem('settings', settingToInsertArray, settingsDb).then(function(response){
					return localDb.saveDb(db);
				}, function (error) {
					return ({status: 0, response: error});  // <-- this is logged to the console
				}).catch(function (error) {
					return errorService.handleError({status: 0, response: error.message});
				});
		}else if(settingToUpdateArray.length){
				return localDb.updateItem('settings', settingToUpdateArray, settingsDb).then(function(response){
					return localDb.saveDb(db);
				}, function (error) {
					return ({status: 0, response: error});  // <-- this is logged to the console
				}).catch(function (error) {
					return errorService.handleError({status: 0, response: error.message});
				});	
		}
	}
	
	this.getAvailablePaymentMethodsDb = function(){
			if(!globals.db){
				return localDb.initilizeDb().then(function(db){
					let availablePaymentMethodsDb = localDb.getTable('availablePaymentMethods', db);
					return ({db: db, availablePaymentMethodsDb: availablePaymentMethodsDb});
				}.bind(this));
			}else{
				let availablePaymentMethodsDb = localDb.getTable('availablePaymentMethods', globals.db);
				return $q(function (resolve, reject) {
					resolve({db: globals.db, availablePaymentMethodsDb: availablePaymentMethodsDb});
				});
			}
	},
	this.getAvailablePaymentMethods = function(tillId, availablePaymentMethodsDb){
		var availablePaymentMethods = availablePaymentMethodsDb.find({ 
			tillIdpaymentMethodId_tillId: tillId
		});
		return availablePaymentMethods;
	}
	this.syncBasicSettings = function(settingsDb, db, syncParameters=['markdownDetails','basicSettings']){
		var parameters = {
			action: 'getDetails',
			parameters: syncParameters
		};
		return $http({
			method : "POST",
			url : "/basicSettings.php",
			data: parameters,
			headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
		}).then(function submitSuccess(response) {
			if(response.data.status){
				let output = [];
				for(var i=0, len=syncParameters.length; i < len; i++){
					output.push({id: syncParameters[i], value: response.data.data[syncParameters[i]].record});
				}
				return ({status: 1, msg: output, settingsDb: settingsDb, db: db});
			}else{
				return ({status: 0, msg: response.data.msg, settingsDb: settingsDb});
			}
		}, function submitError(response) {
			return ({status: 0, msg: response, settingsDb: settingsDb});
		});
	}
	this.syncAvailablePaymentMethods = function(tillId, availablePaymentMethodsDb, db, setlog){
			var availablePaymentMethodsParameters = {
				action: 'getAvailablePaymentMethods',
				tillsDetail_tillId: tillId
			};
			return $http({
				method : "POST",
				url : "/basicSettings.php",
				data: availablePaymentMethodsParameters,
				headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
			}).then(function submitSuccess(response) {
				if(response.data.status){
					setlog("Re-saving whole till operation data",1);
					availablePaymentMethodsDb.chain().remove();
					availablePaymentMethodsDb.removeDataOnly();
					
					let availablePaymentMethods = response.data.data.availablePaymentMethods;
					let paymentExtDetails = response.data.data.paymentExtDetails;
					let cashTypesDetail = response.data.data.cashTypesDetail;
					for(var i=0, len=availablePaymentMethods.length; i < len; i++){
						availablePaymentMethods[i]['recieveFromExt'] = [];
						availablePaymentMethods[i]['sendToExt'] = [];
						if(availablePaymentMethods[i]['paymentMethodsDetail_recieveFromExtId']){
							availablePaymentMethods[i]['recieveFromExt'] = $filter('filter')(paymentExtDetails, {'paymentExtsDetail_extId' : availablePaymentMethods[i]['paymentMethodsDetail_recieveFromExtId']});
						}
						if(availablePaymentMethods[i]['paymentMethodsDetail_sendToExtId']){
							availablePaymentMethods[i]['sendToExt'] = $filter('filter')(paymentExtDetails, {'paymentExtsDetail_extId' : availablePaymentMethods[i]['paymentMethodsDetail_sendToExtId']});
						}
						if(availablePaymentMethods[i]['paymentMethodsDetail_priceRounding'] == 1){
							availablePaymentMethods[i]['cashTypesDetail'] = $filter('filter')(cashTypesDetail, {'cashTypesDetail_currencyId' : availablePaymentMethods[i]['paymentMethodsDetail_currencyId']});
						}
					}
					var tillOperation = availablePaymentMethodsDb.insert(
						availablePaymentMethods
					);
					
					setlog("",0);
					return localDb.saveDb(db).then(function(result){
						if(!result.status){
							setlog("You may face issues processing payments if offline", 0);	
						}
						let returnResponse = ({status: 1, msg: '', availablePaymentMethodsDb: availablePaymentMethodsDb});
						return returnResponse;
					}, function submitError(response) {
						setlog(response, 0);
						return ({status: 0, msg: response, availablePaymentMethodsDb: availablePaymentMethodsDb});
					});
				}else{
					setlog(response.data.msg,0);
					return ({status: 0, msg: response.data.msg, availablePaymentMethodsDb: availablePaymentMethodsDb});
				}
			}, function submitError(response) {
				setlog(response,0);
				return ({status: 0, msg: response, availablePaymentMethodsDb: availablePaymentMethodsDb});
			});
	}
});