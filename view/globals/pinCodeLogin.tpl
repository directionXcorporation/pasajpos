<md-dialog aria-label="Please login using pin code">
			<md-toolbar class="md-hue-2">
				<div class="md-toolbar-tools">
					<h2>Please login using pin code</h2>
					<span flex></span>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<form name="loginform">
					<md-input-container>
						<label>Pin Code</label>
						<input type="text" use-keyboard="Numbers" ng-model="$ctrl.pinCode" required>
					</md-input-container>
				</form>
			</md-dialog-content>
			<md-dialog-actions layout="row" style="font-size:2.3vw;">
				<md-button data-ng-disabled="$ctrl.FormDisabled || loginform.$invalid" class="md-raised md-primary" data-ng-click="$ctrl.clickToSave('pinCodeLogin', {pinCode: $ctrl.pinCode})">
					Save
				</md-button>
				<md-button class="md-raised" data-ng-click="$ctrl.clickToClose()">
					Cancel
				</md-button>
			</md-dialog-actions>
</md-dialog>