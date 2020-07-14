<div class="w3-container" data-ng-controller="parentobj.controllers.sale" ng-cloak>
	<script type="text/ng-template" id="salesScreen.html">
		<md-dialog class="fullscreen-dialog" aria-label="{{_SALES_SCREEN}}">
			<style>
				{{salesScreen.options.customCss}}
			</style>
			<md-toolbar class="md-hue-2">
				<div class="md-toolbar-tools">
					<h2>{{_TILL_MESSAGE}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose()">
						<md-icon aria-label="Close Sales Screen">close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<div ui-i18n="{{_LANG}}">
					<gridster options="salesScreen.options" class="customGridster">
						<gridster-item item="item" data-ng-repeat="item in (salesScreen.screens | filter:{screenId:selectedScreen.screenId})[0].items">
							<div bind-html-compile="item.element" style="width:100%;height:100%;"></div>
						</gridster-item>
					</gridster>
				</div>
			</md-dialog-content>
		</md-dialog>
	</script>
	<script type="text/ng-template" id="itemDetailsNotFound.html" >
		<md-toast>
			<div layout="column">
				<div class="column">
					{{_Item Not Found, Please select from the list}}!
				</div>
			</div>
		</md-toast>
	</script>
	<script type="text/ng-template" id="ItemSelect.html">
		<md-dialog aria-label="{{_SELECT ITEM}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_SELECT ITEM}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose()">
						<md-icon aria-label="Close Till Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-content class="md-padding" data-ng-include="'/items.php?setSelectable=single'">
							
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button class="md-raised" data-ng-click="clickToClose()">
						{{_CANCEL}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
	<script type="text/ng-template" id="checkoutError.html">
		<md-dialog aria-label="{{_PAYMENT_NOT_COMPLETE}}">
			<md-toolbar class="md-hue-2">
				<div class="md-toolbar-tools">
					<h2>{{_PAYMENT_NOT_COMPLETE}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose()">
						<md-icon aria-label="Close {{_PAYMENT_NOT_COMPLETE}}">close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<div ui-i18n="{{_LANG}}">
					{{PAYMENT_IS_NOT_COMPLETE}}: {{paidVsTotal}}
				</div>
				<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button class="md-raised" data-ng-click="clickToClose()">
						{{_CANCEL}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
	<script type="text/ng-template" id="paymentInput.html" >
		<md-dialog aria-label="{{_WAITTING_FOR_INPUT}}">
			<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
			<md-toolbar class="md-hue-2">
				<div class="md-toolbar-tools">
					<h2>{{_WAITTING_FOR_INPUT}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose()">
						<md-icon aria-label="Close {{_WAITTING_FOR_INPUT}}">close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<div ui-i18n="{{_LANG}}">
					<md-input-container class="md-block" data-ng-repeat="field in fields">
						<label>{{field.parameterName}}</label>
						<input type="text" data-ng-model="paymentRecievedParameters[field.parameterModel]" mask="{{field.parameterMask}}" clean="true" restrict="reject" limit="true" validate="true" />
					</md-input-container>
				</div>
				<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button class="md-raised md-primary" data-ng-click="clickToSave()">
						{{_SAVE}}
					</md-button>
					<md-button class="md-raised" data-ng-click="clickToClose()">
						{{_CANCEL}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
	<script type="text/ng-template" id="saleSaved.html">
		<md-toast>
			{{_SALE_COMPLETED}}
		</md-toast>
	</script>
	<script type="text/ng-template" id="printReceipt.html">
		<style>
			{{data.options.customCss}}
		</style>
		<md-dialog aria-label="{{_Print Receipt}}">
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<gridster options="data.options" class="customGridster" style="width: {{data.options.paperWidth}}mm; margin-left:auto; margin-right:auto;" print-section>
					<gridster-item item="item" data-ng-repeat="item in (data.screens | filter:{screenId: selectedScreen.screenId})[0].items" ng-class="($eval(item.condition) != true && item.condition != true) ? 'no-print': ''">
						<div bind-html-compile="item.element" style="width:100%;height:100%;"></div>
					</gridster-item>
				</gridster>
			</md-dialog-content>
		</md-dialog>
	</script>
</div>