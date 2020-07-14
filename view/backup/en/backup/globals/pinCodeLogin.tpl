<md-dialog aria-label="Please login using pin code">
			<md-toolbar class="md-hue-2">
				<div class="md-toolbar-tools">
					<h2>Please login using pin code</h2>
					<span flex></span>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<form name="salesLogin">
					<md-input-container>
						<label>Pin Code</label>
						<input type="text" use-keyboard="Numbers" ng-model="pinCode">
					</md-input-container>
				</form>
			</md-dialog-content>
			<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
				<md-button class="md-raised md-primary" data-ng-click="clickToSave(pinCode)">
					Save
				</md-button>
				<md-button class="md-raised" data-ng-click="clickToClose()">
					Cancel
				</md-button>
			</md-dialog-actions>
</md-dialog>