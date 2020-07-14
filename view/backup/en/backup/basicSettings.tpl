<div class="w3-container" data-ng-controller="parentobj.controllers.basicSettings" ng-cloak>
	<md-toolbar class="md-table-toolbar md-default">
		<div class="md-toolbar-tools">
			<span>{{_BASIC_SETTINGS}}</span>
			<span flex></span>
		</div>
	</md-toolbar>
	<div ui-i18n="{{_LANG}}">
		<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
		<md-tabs md-dynamic-height md-border-bottom>
			<md-tab label="{{_CASH_DETAILS}}" md-on-select="getDetails('cashDetails');">
				<md-content class="md-padding">
					<md-table-container>
						<table md-table>
							<thead md-head md-order="query.cashDetails.order">
								<tr md-row>
									<th md-column md-order-by="cashTypesDetail_cashName"><div>{{_CASH_NAME}}</div></th>
									<th md-column md-order-by="cashType_typeCode"><span>{{_CASH_TYPE}}</span></th>
									<th md-column md-order-by="cashTypesDetail_cashValue"><span>{{_CASH_VALUE}}</span></th>
									<th md-column md-order-by="cashTypesDetail_currencyId"><span>{{_CURRENCY}}</span></th>
									<th md-column>
										<md-button data-ng-click="openEditDetails('cashDetails.html', {cashTypesStatus_status:'1'}, 'create', 'addNewCashDetails');" id="edit_addNewCashDetails">
											<md-icon aria-label="Add New Cash" class="material-icons">
												add_box
								        		</md-icon>
							        		</md-button>
									</th>
								</tr>
							</thead>
							<tbody>
								
								<tr md-row md-body data-ng-repeat="cash in privatedata.cashDetails | filter: filter.search | orderBy: query.cashDetails.order | limitTo: query.cashDetails.limit : (query.cashDetails.page -1) * query.cashDetails.limit ">
									<td md-cell>{{cash.cashTypesDetail_cashName}}</td>
									<td md-cell>{{ (cashCategories | filter: {cashCategories_id: cash.cashTypesDetail_cashCategoriesId})[0].cashCategories_categoryCode }}</td>
									<td md-cell>{{cash.cashTypesDetail_cashValue}}</td>
									<td md-cell>{{cash.cashTypesDetail_currencyId}}</td>
									<td md-cell>
										<md-button class="md-raised" id="edit_{{cash.cashTypesDetail_cashTypeId}}" data-ng-click="openEditDetails('cashDetails.html', cash, 'edit', cash.cashTypesDetail_cashTypeId);">
											<md-icon aria-label="Edit Icon" md-font-icon="edit" style="color:grey" data-fittext>edit</md-icon>
										</md-button>
									</td>
								</tr>
							</tbody>
						</table>
					</md-table-container>
				
					<md-table-pagination md-label="{page: '{{_PAGE}}:', rowsPerPage: '{{_ROW PER PAGE}}:', of: '{{_OF}}'}" md-limit="query.cashDetails.limit" md-limit-options="[10, 20, 30, 50]" md-page="query.cashDetails.page" md-total="{{privatedata.cashDetails.length}}" md-page-select md-boundary-links></md-table-pagination>
				</md-content>
			</md-tab>
			<md-tab label="{{_TAX_DETAILS}}" md-on-select="getDetails('taxDetails');">
				<md-content class="md-padding">
					<md-table-container>
						<table md-table>
							<thead md-head md-order="query.taxDetails.order">
								<tr md-row>
									<th md-column md-order-by="taxDetail_taxName"><div>{{_TAX_NAME}}</div></th>
									<th md-column md-order-by="taxDetail_taxPercent"><span>{{_TAX_PERCENT}}</span></th>
									<th md-column>
										<md-button data-ng-click="openEditDetails('taxDetails.html', {taxStatus_status:'1'}, 'create', 'addNewTaxDetails');" id="edit_addNewTaxDetails">
											<md-icon aria-label="Add New Tax" class="material-icons">
												add_box
								        		</md-icon>
							        		</md-button>
									</th>
								</tr>
							</thead>
							<tbody>
								
								<tr md-row md-body data-ng-repeat="tax in privatedata.taxDetails | filter: filter.search | orderBy: query.taxDetails.order | limitTo: query.taxDetails.limit : (query.taxDetails.page -1) * query.taxDetails.limit ">
									<td md-cell>{{tax.taxDetail_taxName}}</td>
									<td md-cell>{{tax.taxDetail_taxPercent}}</td>
									<td md-cell>
										<md-button class="md-raised" id="edit_{{tax.taxDetail_taxId}}" data-ng-click="openEditDetails('taxDetails.html', tax, 'edit', tax.taxDetail_taxId);">
											<md-icon aria-label="Edit Icon" md-font-icon="edit" style="color:grey" data-fittext>edit</md-icon>
										</md-button>
									</td>
								</tr>
							</tbody>
						</table>
					</md-table-container>
				
					<md-table-pagination md-label="{page: '{{_PAGE}}:', rowsPerPage: '{{_ROW PER PAGE}}:', of: '{{_OF}}'}" md-limit="query.taxDetails.limit" md-limit-options="[10, 20, 30, 50]" md-page="query.taxDetails.page" md-total="{{privatedata.taxDetails.length}}" md-page-select md-boundary-links></md-table-pagination>
				</md-content>
			</md-tab>
			<md-tab label="{{_MARKDOWN_DETAILS}}" md-on-select="getDetails('markdownDetails');">
				<md-content class="md-padding">
					<md-table-container>
						<table md-table>
							<thead md-head md-order="query.markdownDetails.order">
								<tr md-row>
									<th md-column md-order-by="markdownsDetail_markdownType"><div>{{_TYPE}}</div></th>
									<th md-column md-order-by="markdownsDetail_markdownDescription"><span>{{_DESCRIPTION}}</span></th>
									<th md-column>
										<md-button data-ng-click="openEditDetails('markdownDetails.html', {markdownsStatus_status:'1'}, 'create', 'addNewMarkdownDetails');" id="edit_addNewMarkdownDetails">
											<md-icon aria-label="Add New Markdown" class="material-icons">
												add_box
								        		</md-icon>
							        		</md-button>
									</th>
								</tr>
							</thead>
							<tbody>
								
								<tr md-row md-body data-ng-repeat="markdown in privatedata.markdownDetails | filter: filter.search | orderBy: query.markdownDetails.order | limitTo: query.markdownDetails.limit : (query.markdownDetails.page -1) * query.markdownDetails.limit ">
									<td md-cell>{{ (markdownTypes | filter: {markdownsType_markdownType: markdown.markdownsDetail_markdownType})[0].markdownsType_markdownName }}</td>
									<td md-cell>{{markdown.markdownsDetail_markdownDescription}}</td>
									<td md-cell>
										<md-button class="md-raised" id="edit_{{markdown.markdownsDetail_markdownId}}" data-ng-click="openEditDetails('markdownDetails.html', markdown, 'edit', markdown.markdownsDetail_markdownId);">
											<md-icon aria-label="Edit Icon" md-font-icon="edit" style="color:grey" data-fittext>edit</md-icon>
										</md-button>
									</td>
								</tr>
							</tbody>
						</table>
					</md-table-container>
				
					<md-table-pagination md-label="{page: '{{_PAGE}}:', rowsPerPage: '{{_ROW PER PAGE}}:', of: '{{_OF}}'}" md-limit="query.markdownDetails.limit" md-limit-options="[10, 20, 30, 50]" md-page="query.markdownDetails.page" md-total="{{privatedata.markdownDetails.length}}" md-page-select md-boundary-links></md-table-pagination>
				</md-content>
			</md-tab>
			<md-tab label="{{_PAYMENTMETHOD_DETAILS}}" md-on-select="getDetails('paymentMethodDetails');">
				<md-content class="md-padding">
					<md-table-container>
						<table md-table>
							<thead md-head md-order="query.paymentMethodDetails.order">
								<tr md-row>
									<th md-column md-order-by="paymentMethodsDetail_methodCode"><div>{{_CODE}}</div></th>
									<th md-column md-order-by="paymentMethodsDetail_methodName"><span>{{_NAME}}</span></th>
									<th md-column><span>{{_DESCRIPTION}}</span></th>
									<th md-column md-order-by="paymentMethodsDetail_currencyId"><span>{{_CURRENCY}}</span></th>
									<th md-column md-order-by="paymentMethodsDetail_priceRounding"><span>{{_ROUND_PRICE}}</span></th>
									<th md-column md-order-by="paymentMethodsDetail_sendToExtId"><span>{{_SEND_TO_EXTERNAL}}</span></th>
									<th md-column md-order-by="paymentMethodsDetail_recieveFromExtId"><span>{{_RECEIVE_FROM_EXTERNAL}}</span></th>
									<th md-column>
										<md-button data-ng-click="openEditDetails('paymentMethodDetails.html', {paymentMethodsStatus_status:'1'}, 'create', 'addNewPaymentMethodDetails');" id="edit_addNewPaymentMethodDetails">
											<md-icon aria-label="Add New Payment Method" class="material-icons">
												add_box
								        		</md-icon>
							        		</md-button>
									</th>
								</tr>
							</thead>
							<tbody>
								
								<tr md-row md-body data-ng-repeat="paymentMethod in privatedata.paymentMethodDetails | filter: filter.search | orderBy: query.paymentMethodDetails.order | limitTo: query.paymentMethodDetails.limit : (query.paymentMethodDetails.page -1) * query.paymentMethodDetails.limit ">
									<td md-cell>{{paymentMethod.paymentMethodsDetail_methodCode}}</td>
									<td md-cell>{{paymentMethod.paymentMethodsDetail_methodName}}</td>
									<td md-cell>{{paymentMethod.paymentMethodsDetail_methodDescription}}</td>
									<td md-cell>{{paymentMethod.paymentMethodsDetail_currencyId}}</td>
									<td md-cell>
										<md-switch ng-model="paymentMethod.paymentMethodsDetail_priceRounding" aria-label="{{_ROUND_PRICE}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary" data-ng-disabled="true">
										</md-switch>
									</td>
									<td md-cell>{{ paymentMethod.paymentMethodsDetail_sendToExtId ? (privatedata.paymentExtDetails | filter: {paymentExtsDetail_extId: paymentMethod.paymentMethodsDetail_sendToExtId})[0].paymentExtsDetail_extName : ''}}</td>
									<td md-cell>{{ paymentMethod.paymentMethodsDetail_recieveFromExtId ? (privatedata.paymentExtDetails | filter: {paymentExtsDetail_extId: paymentMethod.paymentMethodsDetail_recieveFromExtId})[0].paymentExtsDetail_extName : ''}}</td>
									<td md-cell>
										<md-button class="md-raised" id="edit_{{paymentMethod.paymentMethodsDetail_methodId}}" data-ng-click="openEditDetails('paymentMethodDetails.html', paymentMethod, 'edit', paymentMethod.paymentMethodsDetail_methodId);">
											<md-icon aria-label="Edit Icon" md-font-icon="edit" style="color:grey" data-fittext>edit</md-icon>
										</md-button>
									</td>
								</tr>
							</tbody>
						</table>
					</md-table-container>
				
					<md-table-pagination md-label="{page: '{{_PAGE}}:', rowsPerPage: '{{_ROW PER PAGE}}:', of: '{{_OF}}'}" md-limit="query.paymentMethodDetails.limit" md-limit-options="[10, 20, 30, 50]" md-page="query.paymentMethodDetails.page" md-total="{{privatedata.paymentMethodDetails.length}}" md-page-select md-boundary-links></md-table-pagination>
				</md-content>
			</md-tab>
			<md-tab label="{{_OTHER_DETAILS}}" md-on-select="getDetails('basicSettings');">
				<md-content class="md-padding">
					<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
					<md-content class="md-padding">
						<fieldset>
							<form name="otherDetails" data-ng-disabled="formDisabled">
								<div layout-gt-xs="row">
									<md-input-container class="md-icon-float md-block" flex-gt-xs>
										<label>{{_RETRY_TIMES}}</label>
										<input type="text" data-ng-model="privatedata.basicSettings['retryTimes']" mask="99?9?" clean="false" restrict="true" limit="true" validate="true" />
									</md-input-container>
									<md-input-container class="md-icon-float md-block" flex-gt-xs>
										<label>{{_AUTO_SIGNOUT_SALES_INTERVAL}}</label>
										<input type="text" data-ng-model="privatedata.basicSettings['saleSignoutInterval']" mask="99?9?9?9?9?9?9?9?9?" clean="false" restrict="true" limit="true" validate="true" /> {{_SECONDS}}
									</md-input-container>
								</div>
							</form>
						</fieldset>
				</md-content>
				<md-button class="md-raised md-primary" data-ng-click="saveOtherDetails(privatedata.basicSettings)" >
					{{_SAVE}}
				</md-button>
			</md-tab>
		</md-tabs>
	</div>
	<script type="text/ng-template" id="basicSettingsChangeOK.html">
		<md-toast>
			{{_Basic Settings updated successfully}}
		</md-toast>
	</script>
	
	<!-- CASH DETAILS EDIT BOX -->
	<script type="text/ng-template" id="cashDetails.html">
		<md-dialog aria-label="{{_EDITING}} {{privatedata.record['cashTypesDetail_cashName']}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_EDITING}} {{privatedata.record['cashTypesDetail_cashName']}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose('cashDetails',privatedata.record)">
						<md-icon aria-label="Close Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-content class="md-padding">
					<fieldset>
						<form name="cashDetails" data-ng-disabled="formDisabled">
							<div layout-gt-xs="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_CASH_NAME}}</label>
									<input type="text" data-ng-model="privatedata.record['cashTypesDetail_cashName']" />
								</md-input-container>
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_CASH_VALUE}}</label>
									<input type="text" data-ng-model="privatedata.record['cashTypesDetail_cashValue']" price />
								</md-input-container>
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_CURRENCY}}</label>
									<input type="text" data-ng-model="privatedata.record['cashTypesDetail_currencyId']" data-ng-change="privatedata.record['cashTypesDetail_currencyId']=privatedata.record['cashTypesDetail_currencyId'].toUpperCase();" currency-id />
								</md-input-container>
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_CASHTYPE}}</label>
									 <md-select ng-model="privatedata.record['cashTypesDetail_cashCategoriesId']" aria-label="{{_CASHTYPE}}">
									 	<md-option data-ng-repeat="cashCategory in cashCategories" data-ng-value="cashCategory.cashCategories_id" data-ng-selected="cashCategory.cashCategories_id == privatedata.record['cashTypesDetail_cashCategoriesId']">
											{{cashCategory.cashCategories_categoryCode}}
										</md-option>
									 </md-select>
								</md-input-container>
							</div>
						</form>
						<form name="cashStatus" data-ng-disabled="formDisabled">
							<div layout="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<md-switch ng-model="privatedata.record['cashTypesStatus_status']" aria-label="{{_CASH_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
										{{_CASH_STATUS}}
									</md-switch>
								</md-input-container>
							</div>
						</form>
					</fieldset>
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button class="md-raised md-primary" data-ng-click="clickToSave('cashDetails', privatedata.record,'cashTypesStatus_status')" >
						{{_SAVE}}
					</md-button>
					<md-button class="md-raised" data-ng-click="clickToClose('cashDetails', privatedata.record)">
						{{_CANCEL}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
	
	<!--TAX EDIT BOX -->
	<script type="text/ng-template" id="taxDetails.html">
		<md-dialog aria-label="{{_EDITING}} {{privatedata.record['taxDetail_taxName']}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_EDITING}} {{privatedata.record['taxDetail_taxName']}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose('taxDetails',privatedata.record)">
						<md-icon aria-label="Close Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-content class="md-padding">
					<fieldset>
						<form name="taxDetails" data-ng-disabled="formDisabled">
							<div layout-gt-xs="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_TAX_NAME}}</label>
									<input type="text" data-ng-model="privatedata.record['taxDetail_taxName']" />
								</md-input-container>
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_TAX_PERCENT}}</label>
									<input type="text" data-ng-model="privatedata.record['taxDetail_taxPercent']" price />
								</md-input-container>
							</div>
						</form>
						<form name="taxStatus" data-ng-disabled="formDisabled">
							<div layout="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<md-switch ng-model="privatedata.record['taxStatus_status']" aria-label="{{_TAX_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
										{{_TAX_STATUS}}
									</md-switch>
								</md-input-container>
							</div>
						</form>
					</fieldset>
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button class="md-raised md-primary" data-ng-click="clickToSave('taxDetails', privatedata.record,'taxStatus_status')" >
						{{_SAVE}}
					</md-button>
					<md-button class="md-raised" data-ng-click="clickToClose('taxDetails', privatedata.record)">
						{{_CANCEL}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
	
	<!-- MARKDOWN EDIT BOX -->
	<script type="text/ng-template" id="markdownDetails.html">
		<md-dialog aria-label="{{_EDITING}} {{privatedata.record['markdownsDetail_markdownDescription']}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_EDITING}} {{privatedata.record['markdownsDetail_markdownDescription']}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose('markdownDetails',privatedata.record)">
						<md-icon aria-label="Close Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-content class="md-padding">
					<fieldset>
						<form name="markdownDetails" data-ng-disabled="formDisabled">
							<div layout-gt-xs="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_DESCRIPTION}}</label>
									<input type="text" data-ng-model="privatedata.record['markdownsDetail_markdownDescription']" />
								</md-input-container>
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_TYPE}}</label>
									 <md-select ng-model="privatedata.record['markdownsDetail_markdownType']" aria-label="{{_CASHTYPE}}">
									 	<md-option data-ng-repeat="markdownType in markdownTypes" data-ng-value="markdownType.markdownsType_markdownType" data-ng-selected="markdownType.markdownsType_markdownType == privatedata.record['markdownsDetail_markdownType']">
											{{markdownType.markdownsType_markdownName}}
										</md-option>
									 </md-select>
								</md-input-container>
							</div>
						</form>
						<form name="markdownStatus" data-ng-disabled="formDisabled">
							<div layout="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<md-switch ng-model="privatedata.record['markdownsStatus_status']" aria-label="{{_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
										{{_STATUS}}
									</md-switch>
								</md-input-container>
							</div>
						</form>
					</fieldset>
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button class="md-raised md-primary" data-ng-click="clickToSave('markdownDetails', privatedata.record,'markdownsStatus_status')" >
						{{_SAVE}}
					</md-button>
					<md-button class="md-raised" data-ng-click="clickToClose('markdownDetails', privatedata.record)">
						{{_CANCEL}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
	
	<!-- PAYMENT METHOD EDIT BOX -->
	<script type="text/ng-template" id="paymentMethodDetails.html">
		<md-dialog aria-label="{{_EDITING}} {{privatedata.record['paymentMethodsDetail_methodName']}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_EDITING}} {{privatedata.record['paymentMethodsDetail_methodName']}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose('paymentMethodDetails',privatedata.record)">
						<md-icon aria-label="Close Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-content class="md-padding">
					<fieldset>
						<form name="paymentMethodDetails" data-ng-disabled="formDisabled">
							<div layout-gt-xs="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_CODE}}</label>
									<input type="text" data-ng-model="privatedata.record['paymentMethodsDetail_methodCode']" />
								</md-input-container>
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_NAME}}</label>
									<input type="text" data-ng-model="privatedata.record['paymentMethodsDetail_methodName']"/>
								</md-input-container>
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_DESCRIPTION}}</label>
									<input type="text" data-ng-model="privatedata.record['paymentMethodsDetail_methodDescription']"/>
								</md-input-container>
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_CURRENCY}}</label>
									<input type="text" data-ng-model="privatedata.record['paymentMethodsDetail_currencyId']" data-ng-change="privatedata.record['paymentMethodsDetail_currencyId']=privatedata.record['paymentMethodsDetail_currencyId'].toUpperCase();" currency-id />
								</md-input-container>
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<md-switch ng-model="privatedata.record['paymentMethodsDetail_priceRounding']" aria-label="{{_ROUND_PRICE}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
										{{_ROUND_PRICE}}
									</md-switch>
								</md-input-container>
							</div>
							<div layout-gt-xs="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_SEND_TO_EXTERNAL}}</label>
									 <md-select ng-model="privatedata.record['paymentMethodsDetail_sendToExtId']" aria-label="{{_SEND_TO_EXTERNAL}}">
									 	<md-option data-ng-value=""></md-option>
									 	<md-option data-ng-repeat="paymentExtDetail in privatedata.paymentExtDetails" data-ng-value="paymentExtDetail.paymentExtsDetail_extId" data-ng-selected="paymentExtDetail.paymentExtsDetail_extId == privatedata.record['paymentMethodsDetail_sendToExtId']">
											{{paymentExtDetail.paymentExtsDetail_extName}}
										</md-option>
									 </md-select>
								</md-input-container>
								<md-button data-ng-if="!privatedata.record['paymentMethodsDetail_sendToExtId']" data-ng-click="openEditDetails('paymentExtDetails.html', {paymentExtsStatus_status:'1', paymentExtsDetail_parameters:[]}, 'create', 'addNewPaymentExtDetailsSend');" id="edit_addNewPaymentExtDetailsSend">
									<md-icon aria-label="Add New Cash" class="material-icons">
										add_box
								       	</md-icon>
							        </md-button>
							        <md-button data-ng-if="privatedata.record['paymentMethodsDetail_sendToExtId']" class="md-raised" id="edit_{{privatedata.record.paymentMethodsDetail_sendToExtId}}" data-ng-click="openEditDetails('paymentExtDetails.html', (privatedata.paymentExtDetails | filter: {paymentExtsDetail_extId: privatedata.record.paymentMethodsDetail_sendToExtId})[0], 'edit', privatedata.record['paymentMethodsDetail_sendToExtId']);">
									<md-icon aria-label="Edit Icon" md-font-icon="edit" style="color:grey" data-fittext>edit</md-icon>
								</md-button>
							</div>
							<div layout-gt-xs="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_RECEIVE_FROM_EXTERNAL}}</label>
									 <md-select ng-model="privatedata.record['paymentMethodsDetail_recieveFromExtId']" aria-label="{{_RECEIVE_FROM_EXTERNAL}}">
									 	<md-option data-ng-value=""></md-option>
									 	<md-option data-ng-repeat="paymentExtDetail in privatedata.paymentExtDetails" data-ng-value="paymentExtDetail.paymentExtsDetail_extId" data-ng-selected="paymentExtDetail.paymentExtsDetail_extId == privatedata.record['paymentMethodsDetail_recieveFromExtId']">
											{{paymentExtDetail.paymentExtsDetail_extName}}
										</md-option>
									 </md-select>
								</md-input-container>
								<md-button data-ng-if="!privatedata.record['paymentMethodsDetail_recieveFromExtId']" data-ng-click="openEditDetails('paymentExtDetails.html', {paymentExtsStatus_status:'1', paymentExtsDetail_parameters:[]}, 'create', 'addNewPaymentExtDetailsReceive');" id="edit_addNewPaymentExtDetailsReceive">
									<md-icon aria-label="Add New Cash" class="material-icons">
										add_box
								       	</md-icon>
							        </md-button>
							        <md-button data-ng-if="privatedata.record['paymentMethodsDetail_recieveFromExtId']" class="md-raised" id="edit_{{privatedata.record['paymentMethodsDetail_recieveFromExtId']}}" data-ng-click="openEditDetails('paymentExtDetails.html', (privatedata.paymentExtDetails | filter: {paymentExtsDetail_extId: privatedata.record.paymentMethodsDetail_recieveFromExtId})[0], 'edit', privatedata.record['paymentMethodsDetail_recieveFromExtId']);">
									<md-icon aria-label="Edit Icon" md-font-icon="edit" style="color:grey" data-fittext>edit</md-icon>
								</md-button>
							</div>
						</form>
						<form name="paymentMethodStatus" data-ng-disabled="formDisabled">
							<div layout="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<md-switch ng-model="privatedata.record['paymentMethodsStatus_status']" aria-label="{{_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
										{{_STATUS}}
									</md-switch>
								</md-input-container>
							</div>
						</form>
					</fieldset>
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button class="md-raised md-primary" data-ng-click="clickToSave('paymentMethodDetails', privatedata.record,'paymentMethodsStatus_status')" >
						{{_SAVE}}
					</md-button>
					<md-button class="md-raised" data-ng-click="clickToClose('paymentMethodDetails', privatedata.record)">
						{{_CANCEL}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
	<!-- PAYMENT METHOD EXTERNAL EDIT BOX -->
	<script type="text/ng-template" id="paymentExtDetails.html">
		<md-dialog aria-label="{{_EDITING}} {{privatedata.record['paymentMethodsDetail_methodName']}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_EDITING}} {{privatedata.record['paymentMethodsDetail_methodName']}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose('paymentExtDetails',privatedata.record)">
						<md-icon aria-label="Close Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-content class="md-padding">
					<fieldset>
						<form name="paymentExtDetails" data-ng-disabled="formDisabled">
							<div layout-gt-xs="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_NAME}}</label>
									<input type="text" data-ng-model="privatedata.record['paymentExtsDetail_extName']" />
								</md-input-container>
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_BASE_URL}}</label>
									<input type="text" data-ng-model="privatedata.record['paymentExtsDetail_baseUrl']"/>
								</md-input-container>
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<label>{{_METHOD}}</label>
									<md-select ng-model="privatedata.record['paymentExtsDetail_method']" aria-label="{{_METHOD}}">
									 	<md-option data-ng-value="'POST'" data-ng-selected="'POST' == privatedata.record['paymentExtsDetail_method']">
											POST
										</md-option>
										<md-option data-ng-value="'GET'" data-ng-selected="'GET' == privatedata.record['paymentExtsDetail_method']">
											GET
										</md-option>
									 </md-select>
								</md-input-container>
							</div>
							<div layout-gt-xs="column">
								<div class="md-icon-float md-block" flex-gt-xs data-ng-repeat="param in privatedata.record['paymentExtsDetail_parameters']" layout-gt-xs="column">
									<div layout-gt-xs="row">
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_PARAMETER_NAME}}</label>
											<input type="text" data-ng-model="param['parameterName']" />
										</md-input-container>
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_TYPE}}</label>
											<md-select ng-model="param['parameterType']" aria-label="{{_TYPE}}" required>
											 	<md-option data-ng-repeat="type in [{value:'dynamic', text:'{{_DYNAMIC}}'},{value: 'fixed', text: '{{_FIXED}}'}]" data-ng-value="type.value">
													{{type.text}}
												</md-option>
											 </md-select>
										</md-input-container>
										
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label data-ng-if="'dynamic' == param['parameterType']">{{_PARAMETER_MODEL}}</label>
											<label data-ng-if="'fixed' == param['parameterType']">{{_PARAMETER_VALUE}}</label>
											<input type="text" data-ng-model="param['parameterModel']" aria-label="{{_PARAMETER_MODEL}}" />
										</md-input-container>
										<md-input-container class="md-icon-float md-block" flex-gt-xs data-ng-if="privatedata.record['paymentExtsDetail_silent']!='1' || privatedata.record['paymentExtsDetail_silent']!=1">
											<label>{{_PARAMETER_MASK}}</label>
											<input type="text" data-ng-model="param['parameterMask']" />
										</md-input-container>
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<md-switch ng-model="param['isSuccessFactor']" aria-label="{{_IS_SUCCESS_FACTOR}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
												{{_IS_SUCCESS_FACTOR}}
											</md-switch>
										</md-input-container>
										<md-input-container class="md-icon-float md-block" flex-gt-xs data-ng-if="param['isSuccessFactor']=='1' || param['isSuccessFactor']==1">
											<label>{{_SUCCESS_VAUE}}</label>
											<input type="text" data-ng-model="param['successValue']" />
										</md-input-container>
									</div>
								</div>
								<md-button data-ng-click="addNewParameter(privatedata.record['paymentExtsDetail_parameters'])" id="addNewParameter">
									<md-icon aria-label="Add New Parameter" class="material-icons">
										add_box
								       	</md-icon>
								</md-button>
							</div>
							<div layout-gt-xs="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<md-switch ng-model="privatedata.record['paymentExtsDetail_silent']" aria-label="{{_SILENT}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
										{{_SILENT}}
									</md-switch>
								</md-input-container>
							</div>
						</form>
						<form name="paymentExtStatus" data-ng-disabled="formDisabled">
							<div layout="row">
								<md-input-container class="md-icon-float md-block" flex-gt-xs>
									<md-switch ng-model="privatedata.record['paymentExtsStatus_status']" aria-label="{{_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
										{{_STATUS}}
									</md-switch>
								</md-input-container>
							</div>
						</form>
					</fieldset>
				</md-content>
				<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button class="md-raised md-primary" data-ng-click="clickToSave('paymentExtDetails', privatedata.record,'paymentExtsStatus_status')" >
						{{_SAVE}}
					</md-button>
					<md-button class="md-raised" data-ng-click="clickToClose('paymentExtDetails', privatedata.record)">
						{{_CANCEL}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
</div>