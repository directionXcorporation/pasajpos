<script type="text/ng-template" id="selectAccessTime.tpl">
    <md-dialog aria-label="Please choose escalation time">
			<md-toolbar class="md-hue-2">
				<div class="md-toolbar-tools">
					<h2>Escalation Time</h2>
					<span flex></span>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
			    <div class="msgholder">{{$ctrl.responseErrText}}</div>
				<form name="loginform">
				    <div>
				        Escalation Successful. Please choose escalation time only for this function and this user
				    </div>
					<md-input-container>
						<label>Time in seconds</label>
						<input type="number" step='5' ng-model="$ctrl.escalationTime" required>
					</md-input-container>
				</form>
			</md-dialog-content>
			<md-dialog-actions layout="row" style="font-size:2.3vw;">
				<md-button class="md-raised md-primary" data-ng-click="$ctrl.clickToSave({escalationTime: $ctrl.escalationTime})">
					Save
				</md-button>
				<md-button class="md-raised" data-ng-click="$ctrl.clickToClose()">
					Cancel
				</md-button>
			</md-dialog-actions>
    </md-dialog>
</script>