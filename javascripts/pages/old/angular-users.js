app.controller('parentobj.controllers.users', function($scope, $filter, $window, $http, $cacheFactory, $state, $mdDialog) {

		//var privatedata = $cacheFactory('privatedata');

		//$scope.parentobj.privatedata.users = privatedata.get("users");
		//$scope.parentobj.allgroups = privatedata.get("allgroups");
		if(!$scope.parentobj.privatedata.users || !$scope.parentobj.privatedata.allgroups){
			//var servertime = moment.utc($scope.dataTimestamp).format("YYYY-MM-DD HH:mm:ss");
			//var localtime = moment().utc().format("YYYY-MM-DD HH:mm:ss");
			//servertime = new Date(servertime);
			//localtime = new Date(localtime);
			//if(localtime.getTime()-servertime.getTime()>5000){
				$scope.parentobj.privatedata.users = {};
				$scope.parentobj.privatedata.allgroups = {};
				$scope.parentobj.pageIsLoading = 1;
				$scope.parentobj.setlog("Fetching User Data From Server...",1);
				$http({
					cache: false,
					method : "GET",
					url : "/users.php?action=showallusers",
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					$scope.parentobj.pageIsLoading = 0;
					if(response.data.status){
						//privatedata.put("users", $scope.parentobj.privatedata.users);
						//privatedata.put("allgroups", $scope.parentobj.allgroups);
						$scope.parentobj.privatedata.users = response.data.USERS_JSON;
						$scope.parentobj.setlog('',0);
					}else{
						$scope.parentobj.setlog(response.data.msg,0);
					}
				}, function submitError(response) {
					$scope.parentobj.setlog(response,0);
					$scope.parentobj.pageIsLoading = 0;
				});
			//}
		}
	$scope.parentobj.OpenUserDetails = function(user,editAllowed=false,template='editUser.html'){
					$scope.changeUserPasswordType = "text";
					$scope.record = angular.copy(user);
					
					var preId;
					if(editAllowed){
						preId = "edit";
					}else{
						preId = "view";
					}
					var originalUserData = angular.copy(user);
					
					$mdDialog.show({
						scope: $scope,        // use parent scope in template
	          				preserveScope: true,
						controller: DialogController,
						templateUrl: template,
						parent: angular.element(document.body),
						targetEvent: user,
						clickOutsideToClose: false,
						locals: {
							record: $scope.record,
							userscope: user,
							originalUserData: originalUserData,
							treeView: $scope.privatedata.treeView,
							editAllowed: editAllowed
						},
						multiple: true,
						openFrom: '#'+preId+"_"+$scope.record.userId,
						closeTo: '#'+preId+"_"+$scope.record.userId
					})
					.then(function(user) {
						
					}, function() {
				
					});
				}
				
				$scope.parentobj.getUserDetails = function(user,editAllowed=false) {
					$scope.parentobj.pageIsLoading = 1;
					$scope.parentobj.setlog("Getting User Details",1);
					if(user.userId){
						var profileParameters = {
							action: "getDetails", 
							userId: user.userId
						};
						$http({
							method : "POST",
							url : "/users.php",
							data: profileParameters,
							headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
						}).then(function submitSuccess(response) {
							$scope.parentobj.pageIsLoading = 0;
							if(response.data.status){
								angular.extend(user,response.data.user);
								$scope.parentobj.setlog('',0);
								$scope.parentobj.OpenUserDetails(user,editAllowed);
							}else{
								$scope.parentobj.setlog(response.data.msg,0);
							}
						}, function submitError(response) {
							$scope.parentobj.setlog(response,0);
							$scope.parentobj.pageIsLoading = 0;
						});
						
						
					}else{
						$scope.parentobj.setlog("Error in finding requested user",0);
						$scope.parentobj.pageIsLoading = 0;
					}
				};
	function DialogController($scope, $mdDialog, record, originalUserData, userscope, treeView, editAllowed, $mdToast, $http, $templateCache) {
		$scope.record = record;
		$scope.editAllowed = editAllowed;
		
		$scope.hide = function() {
			$mdDialog.hide();
		};

		$scope.clickToClose  = function() {
			$scope.parentobj.setlog('',0);
			if(angular.equals($scope.record,originalUserData)){
				$mdDialog.cancel();
			}else{
				confirmDialogClose($mdDialog,$scope.clickToSave,$mdDialog.cancel);
			}
		};

		$scope.clickToSave = function() {
			if(angular.equals($scope.record,originalUserData)){
				showMainformToast('noChangesMade.html', $mdToast);
				$mdDialog.hide();
			}else{
				$scope.changeUserPasswordType = "password";
				$scope.editUser(record, $scope, $mdDialog, $http, $mdToast, $templateCache)
			}
		};
		$scope.editUser = function(record, $scope, $mdDialog, $http, $mdToast, $templateCache) {
			$scope.parentobj.setlog("Sending data to server",1);
			$scope.formDisabled = true;
			$scope.editAllowed = true;
			$scope.userLogin.password.$error = {};
			$scope.responseErrText = '';
			$scope.userLogin.$valid = true;
			var options = [];
			var changeArray = {};

			if($scope.userDetails.modified){
				changeArray.changeDetails = 1;
			}
			if($scope.userLogin.modified){
				if(record.password){
					record.password = sha512(record.password);
				}else{
					$scope.userLogin.password.$setValidity("passwordRequiredError", false);
					$scope.changeUserPasswordType = "text";
					
				}

				changeArray.changeLogin = 1;
			}
			if($scope.userGroups.modified){
				changeArray.changeGroups = 1;
			}
			if($scope.userPin.modified){
				changeArray.changePinCode = 1;
			}
			if($scope.userStatus.modified){
				changeArray.changeStatus = 1;
			}
			if($scope.userFunctions.modified){
				changeArray.changeFunctions = 1;
			}
			
			var profileParameters = {
				action: "saveDetails", 
				options: record,
				changeArray: changeArray
			};

			if($scope.userLogin.$valid){
				$http({
					method : "POST",
					url : "/users.php",
					data: profileParameters,
					headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8;'}
				}).then(function submitSuccess(response) {
					if(response.data.status){
						var i = 0;
						angular.forEach(changeArray, function(value, key) {
							if(response.data[key].status && value){
								i = i + 1;
								if(key==='changeDetails'){
									userscope.firstName = record.firstName;
									userscope.lastName = record.lastName;
									userscope.gender = record.gender;
								}else if(key === 'changeStatus'){
									userscope.userStatus = record.userStatus;
									userscope.passwordLoginStatus = record.passwordLoginStatus;
									userscope.pinLoginStatus = record.pinLoginStatus;
									userscope.fingerprintLoginStatus = record.fingerprintLoginStatus;
								}
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}else{
								$scope.responseErrText = $scope.responseErrText + "\n" + response.data[key].msg;
							}
						});

						var keys = Object.keys(changeArray);
						var len = keys.length;
						if(i === len){
							$mdDialog.hide();
							$mdToast.show({
								hideDelay   : 5000,
								position    : 'top right',
								controller  : 'ToastCtrl',
								templateUrl : 'userChangeOK.html'
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