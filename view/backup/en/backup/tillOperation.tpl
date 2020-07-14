<div class="w3-container" data-ng-controller="parentobj.controllers.tillOperation" ng-cloak>
	<div ui-i18n="{{_LANG}}">
		<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
		<md-content class="md-padding" ng-switch="tillStatus">
			<div ng-switch-when="-2">
				<md-button class="md-raised md-primary" data-ng-click="initializeScreen();" >
					{{_CONFIRM_IDENTITY}}
				</md-button>
			</div>
			<div ng-switch-when="-1">
				{{_PLEASE WAIT WHILE LOADING TILL STATUS}}
			</div>
			<div ng-switch-when="0">
				<md-toolbar class="md-table-toolbar md-default">
					<div class="md-toolbar-tools">
						{{_OPEN_TILL}}
					</div>
				</md-toolbar>
				<form name="paymentMethodTotals" data-ng-disabled="formDisabled">
					<table ng-table="paymentMethodsTable" class="table" show-filter="false">
						<tr ng-repeat="method in $data" data-ng-click="createBreakdowns(method)">
							<td title="'{{_PAYMENT_Method}}'">
								{{method.paymentMethodsDetail_methodName}}
							</td>
							<td title="'{{_CURRENCY}}'">
								{{method.paymentMethodsDetail_currencyId}}
							</td>
							<td title="'{{_STATUS}}'">
								<span data-ng-if="!paymentMethodTotals['totalValueCounted_'+$index].$touched" data-ng-init="validity.countInvalid=1"></span>
								<span data-ng-if="paymentMethodTotals['totalValueCounted_'+$index].$touched && paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_calculatedValue != paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_countedValue && !paymentShot[method.paymentMethodsDetail_methodId].tillPaymentDiscrepancy_markdownId" class="material-icons" style="color: red;" data-ng-init="validity.countInvalid=1">error</span>
								<span data-ng-if="paymentMethodTotals['totalValueCounted_'+$index].$touched && paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_calculatedValue != paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_countedValue && paymentShot[method.paymentMethodsDetail_methodId].tillPaymentDiscrepancy_markdownId" class="material-icons" style="color: orange;" data-ng-init="validity.countInvalid=0">check_circle</span>
								<span data-ng-if="paymentMethodTotals['totalValueCounted_'+$index].$touched && paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_calculatedValue == paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_countedValue" class="material-icons" style="color: green;" data-ng-init="validity.countInvalid=0">check_circle</span>
							</td>
							<td title="'{{_TOTAL_VALUE_CALCULATED}}'">
								{{paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_calculatedValue}}
							</td>
							<td title="'{{_TOTAL_QUANTITY_CALCULATED}}'">
								{{paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_calculatedQty}}
							</td>
							<td title="'{{_TOTAL_QUANTITY_COUNTED}}'">
								{{paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_countedQty}}
							</td>
							<td title="'{{_TOTAL_VALUE_COUNTED}}'">
								<md-input-container md-no-float>
									<input name="totalValueCounted_{{$index}}" type="text" placeholder="{{_TOTAL_VALUE_COUNTED}}" data-ng-model="paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_countedValue" value="{{paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_countedValue = (paymentMethodBreakdown[method.paymentMethodsDetail_methodId].breakdown | sumByKey:'countedValue')}}" />
								</md-input-container>
							</td>
							<td title="'{{_DISCREPANCY_MARKDOWN}}'" data-ng-show="paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_countedValue && paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_calculatedValue != paymentShot[method.paymentMethodsDetail_methodId].tillPaymentShot_countedValue">
								<md-input-container class="md-icon-float md-block" flex-gt-xs  md-no-float>
									 <md-select data-ng-model="paymentShot[method.paymentMethodsDetail_methodId].tillPaymentDiscrepancy_markdownId" aria-label="{{_DISCREPANCY_MARKDOWN}}">
									 	<md-option data-ng-repeat="markdown in discrepancyMarkdowns" data-ng-value="markdown.markdownsDetail_markdownId">
											{{markdown.markdownsDetail_markdownDescription}}
										</md-option>
									 </md-select>
								</md-input-container>
							</td>
						</tr>
					</table>
				</form>
				<form name="paymentMethodBreakdowns">
					<ng-include src="paymentMethodDetailTemplate"></ng-include>
				</form>
				<md-button class="md-raised md-primary" data-ng-click="showPaymentMovements()" data-ng-hide="validity.countInvalid" id="showPaymentMovements" >
					{{_NEXT}}
				</md-button>
			</div>
		</md-content>
	</div>
	<script type="text/ng-template" id="empty.html">
	</script>
	<script type="text/ng-template" id="cashDetails.html">
		<table ng-table="cashDetailsTable" class="table" show-filter="false">
			<tr ng-repeat="cashType in $data">
				<td title="'{{_CASH_NAME}}'">
					{{cashType.cashTypesDetail_cashName}}
				</td>
				<td title="'{{_CURRENCY}}'">
					{{cashType.cashTypesDetail_currencyId}}
				</td>
				<td title="'{{_CASH_VALUE}}'">
					{{cashType.cashTypesDetail_cashValue}}
				</td>
				<td title="'{{_TOTAL_QUANTITY_COUNTED}}'">
					<md-input-container md-no-float>
						<input name="totalCashQtyCounted" type="text" placeholder="{{_TOTAL_QUANTITY_COUNTED}}" data-ng-model="cashShot.breakdown[cashType.cashTypesDetail_cashTypeId].countedQty" data-ng-change="cashShot.breakdown[cashType.cashTypesDetail_cashTypeId].countedValue = cashShot.breakdown[cashType.cashTypesDetail_cashTypeId].countedQty * cashType.cashTypesDetail_cashValue" />
					</md-input-container>
				</td>
				<td title="'{{_TOTAL_VALUE_COUNTED}}'">
					<md-input-container md-no-float>
						<input name="totalCashValueCounted" type="text" placeholder="{{_TOTAL_VALUE_COUNTED}}" data-ng-model="cashShot.breakdown[cashType.cashTypesDetail_cashTypeId].countedValue" />
					</md-input-container>
				</td>
			</tr>
		</table>
	</script>
	<script type="text/ng-template" id="nonCashDetails.html">
		<table ng-table="vm.paymentReceipts" class="table" show-filter="false">
			<tr ng-repeat="receipt in $data">
				<td title="'{{_RECEIPT_NUMBER}}'">
					{{receipt.salesHeader.salesHeader_receiptNumber}}
				</td>
				<td title="'{{_CURRENCY}}'">
					{{receipt.salesPayment.salesPayment_currencyId}}
				</td>
				<td title="'{{_TOTAL_RECEIPT_AMOUNT}}'">
					{{receipt.salesHeader.salesHeader_totalAfterTax}}
				</td>
				<td title="'{{_PAID_AMOUNT_USING_THIS_METHOD}}'">
					{{receipt.salesPayment.salesPayment_paidAmount}}
				</td>
			</tr>
		</table>
	</script>
	<script type="text/ng-template" id="paymentMovements.html">
		<md-dialog aria-label="{{_TILL_CASH_MOVEMENTS}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_TILL_CASH_MOVEMENTS}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose()">
						<md-icon aria-label="Close Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-content class="md-padding">
					<form name="tillCahsMovement">
						<table ng-table="paymentMovementTable" class="table" show-filter="false">
							<tr ng-repeat="paymentMethods in $data">
								<td title="'{{_PAYMENT_Method}}'">
									{{paymentMethods.paymentMethodsDetail_methodName}}
								</td>
								<td title="'{{_CURRENCY}}'">
									{{paymentMethods.paymentMethodsDetail_currencyId}}
								</td>
								<td title="'{{_TOTAL_VALUE_COUNTED}}'">
									{{paymentShot[paymentMethods.paymentMethodsDetail_methodId].tillPaymentShot_countedValue}}
								</td>
								<td title="'{{_AMOUNT_TO_MOVE}}'">
									<md-input-container md-no-float>
										<input name="amountToMove" type="text" placeholder="{{_AMOUNT_TO_MOVE}}" data-ng-model="paymentMovements[paymentMethods.paymentMethodsDetail_methodId].tillPaymentMovement_amount" min="{{-paymentShot[paymentMethods.paymentMethodsDetail_methodId].tillPaymentShot_countedValue-0.001}}" price />
									</md-input-container>
								</td>
								<td title="'{{_MOVEMENT_MARKDOWN}}'" data-ng-show="paymentMovements[paymentMethods.paymentMethodsDetail_methodId].tillPaymentMovement_amount">
									<md-input-container class="md-icon-float md-block" flex-gt-xs  md-no-float>
										 <md-select data-ng-model="paymentMovements[paymentMethods.paymentMethodsDetail_methodId].tillPaymentMovement_markdownId" aria-label="{{_MOVEMENT_MARKDOWN}}">
										 	<md-option data-ng-value=""></md-option>
										 	<md-option data-ng-repeat="markdown in markdowns" data-ng-value="markdown.markdownsDetail_markdownId">
												{{markdown.markdownsDetail_markdownDescription}}
											</md-option>
										 </md-select>
									</md-input-container>
									<md-input-container>
										<label>{{_NOTE}}</label>
										<textarea type="text" ng-model="paymentMovements[paymentMethods.paymentMethodsDetail_methodId].tillPaymentMovement_note"></textarea>
									</md-input-container>
								</td>
							</tr>
						</table>
					</form>
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button class="md-raised md-primary" data-ng-click="clickToSave()" data-ng-show="tillCahsMovement.$valid" >
						{{_SAVE}}
					</md-button>
					<md-button class="md-raised" data-ng-click="clickToClose()">
						{{_CANCEL}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
	<script type="text/ng-template" id="selectTill.html">
		<md-dialog aria-label="{{_SELECT TILL}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_SELECT TILL}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose()">
						<md-icon aria-label="Close" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-content class="md-padding">
					<fieldset>
						<form name="selectTill" data-ng-disabled="formDisabled">
							<div layout-gt-xs="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_ALLOWED_TILLS}}</label>
									<md-select ng-model="selectedTill" aria-label="{{_ALLOWED_TILLS}}" placeholder="{{_ALLOWED_TILLS}}">
										<md-option data-ng-value=""></md-option>
									 	<md-option data-ng-repeat="till in allowedTills" data-ng-value="till">
											{{till.storesDetail_storeName}} ({{till.storesDetail_storeCode}}):
 {{till.tillsDetail_tillCode}} ({{till.tillsDetail_tillNumber}})
										</md-option>
									 </md-select>
								</md-input-container>
							</div>
						</form>
					</fieldset>
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button class="md-raised md-primary" data-ng-click="clickToSave()" >
						{{_SAVE}}
					</md-button>
					<md-button class="md-raised" data-ng-click="clickToClose()">
						{{_CANCEL}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
</div>