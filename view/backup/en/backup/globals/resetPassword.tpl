<md-dialog aria-label="resetCredentials">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2><md-icon aria-label="resetCredentials" md-font-icon="security" style="font-size:2vw;">security</md-icon><span flex>Reset Password</span></h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="$ctrl.clickToClose()">
						<md-icon aria-label="Close dialog" md-font-icon="close" style="font-size:2vw;">close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content style="max-width:800px;max-height:810px;">
				<md-progress-linear md-mode="query" data-ng-disabled="!$ctrl.FormDisabled"></md-progress-linear>
				<md-content class="md-padding">
						<div id="resetCredentialsFormholder">
							<div layout="column">
								<div class="msgholder">{{$ctrl.responseErrText}}</div>
								<form method="POST" name="resetCredentialsForm">
									<div layout="column">
										<md-input-container class="md-icon-float md-block">
											<label>Email</label>
											<md-icon aria-label="Email" md-font-icon="alternate_email" style="display: inline; font-size:2vw;">alternate_email</md-icon>
											<input type="text" name="email" data-ng-model="$ctrl.privatedata.email" required data-ng-disabled="$ctrl.FormDisabled" flex="grow" />
										</md-input-container>
									</div>
								</form>
							</div>
						</div>
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;">
					<md-button data-ng-click="$ctrl.resetCredentials('credentialsReset', {email: $ctrl.privatedata.email})" data-ng-disabled="$ctrl.FormDisabled || loginform.$invalid" class="md-raised md-primary">
						Send Link
					</md-button>
					<span flux></span>
					<md-button data-ng-click="$ctrl.clickToClose()" class="md-raised">
						Cancel
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
</md-dialog>