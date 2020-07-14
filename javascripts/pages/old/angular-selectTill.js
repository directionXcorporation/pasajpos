app.controller('parentobj.controllers.changeWorkstation',function($scope, $filter, $window, $http, $cacheFactory, $state, $mdDialog, i18nService, uiGridConstants, $templateCache, guid, NgTableParams, errorHandling, loggedinUser, settings, Auth) {
	$scope.pageStatus = 0;
	$scope.allowedTills = [];
	$scope.tillId = "";
	settings.getSettingsTable().then(function(dbDetails){
		let settingsDb = dbDetails.settingsDb;
		let db = dbDetails.db;
		let tillIdSetting = settings.getSettings('tillId', settingsDb, $scope.parentobj.setlog);
		loggedinUser.getLoggedinUserTable().then(function(loggedinUserDbDetails){
			let loggedinUserDb = loggedinUserDbDetails.loggedinUserDb;
			let loggedInUserDetails = loggedinUser.getLoggedInUser(Auth.getUser(), loggedinUserDb, $scope.parentobj.setlog);
			if(loggedInUserDetails && loggedInUserDetails.userId){
				$scope.allowedTills = loggedInUserDetails.allowedTills;
				$scope.selectTill(allowedTills, settingsDb, db);
			}
		});
		if(tillIdSetting && tillIdSetting.value){
			$scope.tillId = tillIdSetting.value;
		}
		$scope.openTill(tillId);
	});
	$scope.clickToSave = function(){
		settings.setSettings({id: "tillId", value: $scope.selectedTill}, settingsDb, db);
		$mdDialog.hide($scope.selectedTill);
	}
});