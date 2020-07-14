<script type="text/ng-template" id="usernameLogin.tpl">
    <md-dialog aria-label="Login">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>
					    <md-icon aria-label="Login" md-font-icon="security" style="font-size:2vw;">security</md-icon>
					    <span flex translate>Login</span>
					</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="$ctrl.clickToClose()">
						<md-icon aria-label="Close dialog" md-font-icon="close" style="font-size:2vw;">close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content style="max-width:800px;max-height:810px;">
				<md-progress-linear md-mode="query" data-ng-disabled="!$ctrl.FormDisabled"></md-progress-linear>
				<md-content class="md-padding">
						<div id="loginformholder">
								<div layout="column">
									<div class="msgholder" translate>{{$ctrl.responseErrText}}</div>
									<form method="POST" name="loginform">
										<div layout="column">
											<md-input-container class="md-icon-float md-block">
												<label translate>Username</label>
												<md-icon aria-label="Username" md-font-icon="perm_identity" style="display: inline; font-size:2vw;">perm_identity</md-icon>
												<input type="text" name="username" data-ng-model="$ctrl.privatedata.username" autocomplete="username" required data-ng-disabled="$ctrl.FormDisabled" flex="grow" />
											</md-input-container>
											<md-input-container class="md-icon-float md-block">
												<label translate>Password</label>
												<md-icon aria-label="Password" md-font-icon="vpn_key" style="display: inline; font-size:2vw;">vpn_key</md-icon>
												<input type="password" name="password" required data-ng-model="$ctrl.privatedata.password" autocomplete="current-password" data-ng-disabled="$ctrl.FormDisabled" flex="grow">
											</md-input-container>
										</div>
									</form>
									<div>
										<div data-ng-click="$ctrl.recoverPassword($ctrl.privatedata.username);" style="color: blue; cursor: pointer;" translate>Forgot password?</div>
									</div>
								</div>
						</div>
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;">
					<md-button data-ng-click="$ctrl.clickToSave('usernameLogin', {username: $ctrl.privatedata.username,password: $ctrl.privatedata.password})" data-ng-disabled="$ctrl.FormDisabled || loginform.$invalid" class="md-raised md-primary" translate>
						Login
					</md-button>
					<span flux></span>
					<md-button data-ng-click="$ctrl.clickToClose()" class="md-raised" translate>
						Cancel
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
    </md-dialog>
</script>
<script type="text/ng-template" id="pinCodeLogin.tpl">
    <md-dialog aria-label="Please login using pin code">
			<md-toolbar class="md-hue-2">
				<div class="md-toolbar-tools">
					<h2 translate>Please login using pin code</h2>
					<span flex></span>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
			    <div class="msgholder" translate>{{$ctrl.responseErrText}}</div>
				<form name="loginform">
				    <md-input-container>
                        <label translate>Select User</label>
                        <md-select ng-model="$ctrl.userId">
                            <md-option><em translate>None</em></md-option>
                            <md-option ng-repeat="user in $ctrl.storeUsers" ng-value="user.usersIdstoresId_userId">
                                {{user.usersDetail_firstName}} {{user.usersDetail_lastName}}
                            </md-option>
                        </md-select>
                    </md-input-container>
					<md-input-container>
						<label translate>Pin Code</label>
						<input type="number" ng-model="$ctrl.pinCode" required>
					</md-input-container>
				</form>
			</md-dialog-content>
			<md-dialog-actions layout="row" style="font-size:2.3vw;">
				<md-button data-ng-disabled="$ctrl.FormDisabled || loginform.$invalid" class="md-raised md-primary" data-ng-click="$ctrl.clickToSave('pinCodeLogin', {pinCode: $ctrl.pinCode, userId: $ctrl.userId})" translate>
					Save
				</md-button>
				<md-button class="md-raised" data-ng-click="$ctrl.clickToClose()" translate>
					Cancel
				</md-button>
			</md-dialog-actions>
    </md-dialog>
</script>
<script type="text/ng-template" id="recoverPassword.tpl">
    <md-dialog aria-label="Login">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>
					    <md-icon aria-label="Login" md-font-icon="security" style="font-size:2vw;">security</md-icon>
					    <span flex translate>Recover Password</span>
					</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="$ctrl.clickToClose()">
						<md-icon aria-label="Close dialog" md-font-icon="close" style="font-size:2vw;">close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content style="max-width:800px;max-height:810px;">
				<md-progress-linear md-mode="query" data-ng-disabled="!$ctrl.FormDisabled"></md-progress-linear>
				<md-content class="md-padding">
						<div id="loginformholder">
								<div layout="column">
									<div class="msgholder" translate>{{$ctrl.responseErrText}}</div>
									<form method="POST" name="recoverPasswordform">
										<div layout="column">
											<md-input-container class="md-icon-float md-block">
												<label translate>Username</label>
												<md-icon aria-label="Username" md-font-icon="perm_identity" style="display: inline; font-size:2vw;">perm_identity</md-icon>
												<input type="text" name="username" data-ng-model="$ctrl.privatedata.username" autocomplete="username" required data-ng-disabled="$ctrl.FormDisabled" flex="grow" />
											</md-input-container>
											<md-input-container class="md-icon-float md-block">
												<label translate>One-time Token</label>
												<md-icon aria-label="Password" md-font-icon="vpn_key" style="display: inline; font-size:2vw;">vpn_key</md-icon>
												<input type="text" name="text" required data-ng-model="$ctrl.privatedata.token" data-ng-disabled="$ctrl.FormDisabled" flex="grow">
											</md-input-container>
											<md-input-container class="md-icon-float md-block">
												<label translate>New Password</label>
												<md-icon aria-label="Password" md-font-icon="vpn_key" style="display: inline; font-size:2vw;">vpn_key</md-icon>
												<input type="text" name="text" required data-ng-model="$ctrl.privatedata.password" autocomplete="new-password" data-ng-disabled="$ctrl.FormDisabled" flex="grow">
											</md-input-container>
											<md-input-container class="md-icon-float md-block">
												<label translate>New Pincode</label>
												<md-icon aria-label="Pin Code" md-font-icon="keyboard" style="display: inline; font-size:2vw;">keyboard</md-icon>
												<input type="text" name="text" required data-ng-model="$ctrl.privatedata.pinCode" data-ng-disabled="$ctrl.FormDisabled" flex="grow">
											</md-input-container>
										</div>
									</form>
									<div>
										<div data-ng-click="$ctrl.recoverPassword($ctrl.privatedata.username, 0);" style="color: blue; cursor: pointer;" translate>Did not receive token?</div>
									</div>
								</div>
						</div>
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;">
					<md-button data-ng-click="$ctrl.clickToSave('changePassword', {username: $ctrl.privatedata.username, password: $ctrl.privatedata.password, pinCode: $ctrl.privatedata.pinCode, token: $ctrl.privatedata.token})" data-ng-disabled="$ctrl.FormDisabled || loginform.$invalid" class="md-raised md-primary" translate>
						Save
					</md-button>
					<span flux></span>
					<md-button data-ng-click="$ctrl.clickToClose()" class="md-raised" translate>
						Cancel
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
    </md-dialog>
</script>