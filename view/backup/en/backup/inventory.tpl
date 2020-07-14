<div class="w3-container" data-ng-controller="parentobj.controllers.items" ng-cloak>
	<md-toolbar class="md-table-toolbar md-default">
		<div class="md-toolbar-tools">
			<span>{{_Items}}</span>
			<span flex></span>
			<md-button data-ng-click="parentobj.getItemDetails('','create');">
				<md-icon aria-label="Add New Item" class="material-icons">
					add_box
	        		</md-icon>
        		</md-button>
		</div>
	</md-toolbar>
	<div ui-i18n="{{_LANG}}">
		<md-toolbar class="md-hue-2">
			<div class="md-toolbar-tools">
				<div flex>
				<md-input-container>
					<md-select aria-label="{{_Change View}}" data-ng-model="privatedata.itemSelectedView" data-ng-change="changeTableView(privatedata.itemSelectedView)">
						<md-option data-ng-repeat="availableItemView in privatedata.availableItemViews" data-ng-value="availableItemView" data-ng-selected="$first">
							{{availableItemView.viewName}}
						</md-option>
					</md-select>
				</md-input-container>
				</div>
				<md-button id="deleteItemTableView" class="md-icon-button" aria-label="{{_Delete view}}" data-ng-if="isAllowedToSaveItemView" data-ng-show="privatedata.itemSelectedView.viewId" data-ng-click="parentobj.confirmDeleteSelectedView(privatedata.itemSelectedView.viewId, 'deleteItemTableView');">
					<md-icon class='material-icons'>delete</md-icon>
				</md-button>
				<md-button id="saveItemTableView" class="md-icon-button" aria-label="{{_Save view}}" data-ng-if="isAllowedToSaveItemView" data-ng-show="saveNewState && !privatedata.itemSelectedView.viewId" data-ng-click="parentobj.saveTableView('items',itemsGridApi.saveState.save(),'saveItemTableView',privatedata.availableItemViews);">
					<md-icon class='material-icons'>save</md-icon>
				</md-button>
			</div>
		</md-toolbar>
		<div id="itemsTable" ui-grid="itemsGridOption" class="data-grid" {{ui-grid-selection}} ui-grid-resize-columns ui-grid-pinning ui-grid-move-columns ui-grid-exporter ui-grid-grouping ui-grid-pagination ui-grid-auto-resize ui-grid-save-state></div>
		<md-input-container>
			<md-switch aria-label="{{_Show Field Names}}" data-ng-model="tableProperties.showFieldNames" data-ng-true-value="1" data-ng-false-value="0">
				{{_Show Field Names}}
			</md-switch>
		</md-input-container>
	</div>
	<script type="text/ng-template" id="editItem.html">
		<md-dialog aria-label="{{_EDITING}} {{privatedata.record['itemsDetail_itemCode']}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_EDITING}} {{privatedata.record['itemsDetail_itemCode']}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose(privatedata.record)">
						<md-icon aria-label="Close Item Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-tabs md-dynamic-height md-border-bottom>
					<form name="itemDetails" data-ng-disabled="formDisabled">
						<md-tab label="{{_BASIC_DETAILS}}">
							<md-content class="md-padding">
								<fieldset>
								
									<div layout-gt-xs="row">
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_ITEM_CODE}}</label>
											<input type="text" data-ng-model="privatedata.record['itemsDetail_itemCode']" data-ng-change="privatedata.record['itemsDetail_itemCode']=privatedata.record['itemsDetail_itemCode'].toUpperCase();" />
										</md-input-container>
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_ITEM_NAME}}</label>
											<input type="text" data-ng-model="privatedata.record['itemsDetail_itemName']" />
										</md-input-container>
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_ITEM_BARCODE}}</label>
											<input type="text" data-ng-model="privatedata.record['itemsDetail_itemBarcode']" />
										</md-input-container>
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_ITEM_EXTERNALID}}</label>
											<input type="text" data-ng-model="privatedata.record['itemsDetail_itemExternalId']" />
										</md-input-container>
									</div>
									<div layout-gt-xs="row">
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_ITEM_DESCRIPTION}}</label>
											<textarea md-maxlength="255" data-ng-model="privatedata.record['itemsDetail_itemDescription']"></textarea>
										</md-input-container>
									</div>
									<div layout="row">
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<md-switch ng-model="privatedata.record['itemsStatus_status']" aria-label="{{_ITEM_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
												{{_ITEM_STATUS}}
											</md-switch>
										</md-input-container>
									</div>
								</fieldset>
							</md-content>
						</md-tab>
						<md-tab label="{{_ITEM_BRANDS}}">
							<md-content class="md-padding" data-ng-include="'/brands.php?setSelectable=multiple'">
							
							</md-content>
						</md-tab>
					</form>
					<md-tab label="{{_ITEM_PRICES}}">
						<md-content class="md-padding">
							<form name="itemPrices" data-ng-disabled="formDisabled">
								<div layout-gt-xs="row" data-ng-repeat="itemPrice in privatedata.record.itemPrices">
									<md-input-container class="md-icon-float md-block" flex-gt-xs>
										<label>{{_PRICE_CURRENCY}}</label>
										<input type="text" data-ng-model="itemPrice['itemsPrice_currencyId']" data-ng-change="itemPrice['itemsPrice_currencyId']=itemPrice['itemsPrice_currencyId'].toUpperCase();" mask="@@@" clean="false" restrict="reject" limit="true" validate="true" />
									</md-input-container>
									<md-input-container class="md-icon-float md-block" flex-gt-xs>
										<label>{{_ITEM_PRICE}}</label>
										<input type="text" data-ng-model="itemPrice['itemsPrice_price']" mask="9?9?9?9?9?9?9?9.99" clean="false" restrict="reject" limit="true" validate="true" />
									</md-input-container>
									<md-datepicker data-ng-model="itemPrice['itemsPrice_validFrom']" md-placeholder="{{_VALID_FROM}}" md-open-on-focus acceptallzeroasdate="true" required="required"></md-datepicker>
									<md-checkbox
										ng-model="itemPrice['itemsPrice_status']"
										aria-label="{{_DISABLED}}"
										ng-true-value="'0'"
										ng-false-value="'1'"
									class="md-warn" flex>
										{{_DISABLED}}
									</md-checkbox>
								</div>
								<md-button class="md-icon-button md-primary" aria-label="{{_ADD_PRICE}}" data-ng-click="addCurrency(privatedata.record.itemPrices)">
									<md-icon class="material-icons">monetization_on</md-icon>
								</md-button>
							</form>
						</md-content>
					</md-tab>
					<md-tab label="{{_ITEM_INVENTORY}}">
						
					</md-tab>
				</md-tabs>
				<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button class="md-raised md-primary" data-ng-click="clickToSave(privatedata.record)" >
						{{_SAVE}}
					</md-button>
					<md-button class="md-raised" data-ng-click="clickToClose(privatedata.record)">
						{{_CANCEL}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
	<script type="text/ng-template" id="itemChangeOK.html">
		<md-toast>
			{{_Item updated successfully}}
		</md-toast>
	</script>
	<script type="text/ng-template" id="gridRowMenu.html">
		<div layout-align="start center" layout="column">	
			<md-menu md-position-mode="target-right target"> 
				<md-button aria-label="Open interactions menu" class="md-icon-button" data-ng-click="$mdMenu.open(ev)">
					<md-icon md-menu-origin class="material-icons">menu</md-icon> 
				</md-button>
				<md-menu-content width="2">
					<md-menu-item>
						<md-button ng-click="grid.appScope.parentobj.getItemDetails(row.entity);">
							<md-icon md-menu-align-target calss="material-icons" md-menu-align-target>edit</md-icon>
							{{_Edit}}
						</md-button>
					</md-menu-item>
				</md-menu-content>
			</md-menu>
		</div>
	</script>
</div>
