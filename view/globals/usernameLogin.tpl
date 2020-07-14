<md-dialog aria-label="Login">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2><md-icon aria-label="Login" md-font-icon="security" style="font-size:2vw;">security</md-icon><span flex>Login</span></h2>
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
									<div class="msgholder">{{$ctrl.responseErrText}}</div>
									<form method="POST" name="loginform">
										<div layout="column">
											<md-input-container class="md-icon-float md-block">
												<label>Username</label>
												<md-icon aria-label="Username" md-font-icon="perm_identity" style="display: inline; font-size:2vw;">perm_identity</md-icon>
												<input type="text" name="username" data-ng-model="$ctrl.privatedata.username" required data-ng-disabled="$ctrl.FormDisabled" flex="grow" />
											</md-input-container>
											<md-input-container class="md-icon-float md-block">
												<label>Password</label>
												<md-icon aria-label="Password" md-font-icon="vpn_key" style="display: inline; font-size:2vw;">vpn_key</md-icon>
												<input type="password" name="password" required data-ng-model="$ctrl.privatedata.password" data-ng-disabled="$ctrl.FormDisabled" flex="grow">
											</md-input-container>
										</div>
									</form>
									<div>
										<div data-ng-click="$ctrl.showLogin('resetPassword');" style="color: blue; cursor: pointer;">Forgot password?</div>
									</div>
								</div>
						</div>
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;">
					<md-button data-ng-click="$ctrl.clickToSave('usernameLogin', {username: $ctrl.privatedata.username,password: $ctrl.privatedata.password})" data-ng-disabled="$ctrl.FormDisabled || loginform.$invalid" class="md-raised md-primary">
						Login
					</md-button>
					<span flux></span>
					<md-button data-ng-click="$ctrl.clickToClose()" class="md-raised">
						Cancel
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
</md-dialog>