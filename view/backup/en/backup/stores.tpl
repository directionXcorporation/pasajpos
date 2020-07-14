<div class="w3-container" data-ng-controller="parentobj.controllers.stores" ng-cloak>
	<md-toolbar class="md-table-toolbar md-default">
		<div class="md-toolbar-tools">
			<span>{{_Stores}}</span>
			<span flex></span>
			<md-button data-ng-click="parentobj.getStoreDetails('','create');">
				<md-icon aria-label="Add New Store" class="material-icons">
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
					<md-select aria-label="{{_Change View}}" data-ng-model="privatedata.storeSelectedView" data-ng-change="changeTableView(privatedata.storeSelectedView)">
						<md-option data-ng-repeat="availableStoreView in privatedata.availableStoreViews" data-ng-value="availableStoreView" data-ng-selected="$first">
							{{availableStoreView.viewName}}
						</md-option>
					</md-select>
				</md-input-container>
				</div>
				<md-button id="deleteStoreTableView" class="md-icon-button" aria-label="{{_Delete view}}" data-ng-if="isAllowedToSaveStoreView" data-ng-show="privatedata.storeSelectedView.viewId" data-ng-click="parentobj.confirmDeleteSelectedView(privatedata.storeSelectedView.viewId, 'deleteStoreTableView');">
					<md-icon class='material-icons'>delete</md-icon>
				</md-button>
				<md-button id="saveStoreTableView" class="md-icon-button" aria-label="{{_Save view}}" data-ng-if="isAllowedToSaveStoreView" data-ng-show="saveNewState && !privatedata.storeSelectedView.viewId" data-ng-click="parentobj.saveTableView('stores',storesGridApi.saveState.save(),'saveStoreTableView',privatedata.availableStoreViews);">
					<md-icon class='material-icons'>save</md-icon>
				</md-button>
			</div>
		</md-toolbar>
		<div id="storesTable" ui-grid="storesGridOption" class="data-grid" {{ui-grid-selection}} ui-grid-resize-columns ui-grid-pinning ui-grid-move-columns ui-grid-exporter ui-grid-grouping ui-grid-pagination ui-grid-auto-resize ui-grid-save-state></div>
		<md-input-container>
			<md-switch aria-label="{{_Show Field Names}}" data-ng-model="tableProperties.showFieldNames" data-ng-true-value="1" data-ng-false-value="0">
				{{_Show Field Names}}
			</md-switch>
		</md-input-container>
	</div>
	<script type="text/ng-template" id="editStore.html">
		<md-dialog aria-label="{{_EDITING}} {{privatedata.record['storesDetail_storeCode']}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_EDITING}} {{privatedata.record['storesDetail_storeCode']}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose(privatedata.record)">
						<md-icon aria-label="Close Store Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-tabs md-dynamic-height md-border-bottom>
					<md-tab label="{{_BASIC_DETAILS}}">
						<md-content class="md-padding">
							<fieldset>
								<form name="storeDetails" data-ng-disabled="formDisabled">
									<div layout-gt-xs="column">
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_STORE CODE}}</label>
											<input type="text" data-ng-model="privatedata.record['storesDetail_storeCode']" data-ng-change="privatedata.record['storesDetail_storeCode']=privatedata.record['storesDetail_storeCode'].toUpperCase();" />
										</md-input-container>
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_STORE_NAME}}</label>
											<input type="text" md-maxlength="255" data-ng-model="privatedata.record['storesDetail_storeName']" />
										</md-input-container>
										<div layout="row">
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<md-switch ng-model="privatedata.record['storesStatus_status']" aria-label="{{_STORE_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
													{{_STORE_STATUS}}
												</md-switch>
											</md-input-container>
										</div>
									</div>
								</form>
							</fieldset>
						</md-content>
					</md-tab>
					<md-tab label="{{_STORE_CONTACT}}">
						<md-content class="md-padding">
							<fieldset>
								<form name="storeContact" data-ng-disabled="formDisabled">
									<div layout-gt-xs="column">
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_STORE_ADDRESSLINE1}}</label>
											<input type="text" data-ng-model="privatedata.record['storesContact_addressLine1']" />
										</md-input-container>
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_STORE_ADDRESSLINE2}}</label>
											<input type="text" data-ng-model="privatedata.record['storesContact_addressLine2']" />
										</md-input-container>
										<div layout-gt-xs="row">
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<label>{{_STORE_CITY}}</label>
												<input type="text" data-ng-model="privatedata.record['storesContact_city']" />
												 
											</md-input-container>
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<label>{{_STORE_STATE}}</label>
												<input type="text" data-ng-model="privatedata.record['storesContact_state']" />
											</md-input-container>
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<label>{{_STORE_COUNTRY}}</label>
												<input type="text" data-ng-model="privatedata.record['storesContact_country']" />
												<!--<md-autocomplete flex required
													md-input-name="storeCountry"
													md-input-minlength="2"
													md-input-maxlength="18"
													md-min-length="0"
													md-selected-item="selectedCountry"
													md-selected-item-change="selectedCountryChange(item)"
													md-search-text="searchText"
													md-search-text-change="searchTextChange(searchText)"
													md-items="item in countryList(searchText)"
													md-item-text="item.countryName"
													md-require-match
													md-floating-label="{{_STORE_COUNTRY}}">
													<md-item-template>
														<span md-highlight-text="searchText" md-highlight-flags="^i">{{item.countryName}}</span>
													</md-item-template>-->
											</md-input-container>
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<label>{{_STORE_POSTALCODE}}</label>
												<input type="text" data-ng-model="privatedata.record['storesContact_zipcode']" mask="@9@ 9@9" clean="false" restrict="reject" limit="true" validate="true" data-ng-change="privatedata.record['storesContact_zipcode']=privatedata.record['storesContact_zipcode'].toUpperCase();"/>
											</md-input-container>
										</div>
										<div layout-gt-xs="row">
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<label>{{_STORE_PHONE}}</label>
												<input type="text" name="phone" md-maxlength="16" data-ng-model="privatedata.record['storesContact_phone']" mask="(999) 999-9999" clean="false" restrict="reject" limit="true" validate="true" />
											</md-input-container>
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<label>{{_STORE_CELL}}</label>
												<input type="text" name="cell" md-maxlength="16" data-ng-model="privatedata.record['storesContact_cell']" mask="(999) 999-9999" clean="false" restrict="reject" limit="true" validate="true" />
											</md-input-container>
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<label>{{_STORE_EMAIL}}</label>
												<input type="text" md-maxlength="40" data-ng-model="privatedata.record['storesContact_email']" />
											</md-input-container>
										</div>
									</div>
								</form>
							</fieldset>
						</md-content>
					</md-tab>
					<md-tab label="{{_RECEIPTS}}">
						<md-content class="md-padding">
							<fieldset>
								<form name="storeReceipts" data-ng-disabled="formDisabled">
									<div layout-gt-xs="column" data-ng-repeat="receipt in privatedata.record.receipts">
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label data-ng-if="receipt.storesIdreceiptsId_receiptType=='sales'">{{_SALES_RECEIPT}}</label>
											<label data-ng-if="receipt.storesIdreceiptsId_receiptType=='X'">{{_X_RECEIPT}}</label>
											<label data-ng-if="receipt.storesIdreceiptsId_receiptType=='Z'">{{_Z_RECEIPT}}</label>
											<md-select data-ng-model="receipt.storesIdreceiptsId_receiptId" aria-label="{{_RECEIPTS}}">
												<md-option data-ng-value=""></md-option>
												<md-option data-ng-repeat="availableReceipt in privatedata.availableReceipts" data-ng-value="availableReceipt.receiptsDetail_receiptId">
													{{availableReceipt.receiptsDetail_receiptName}}
												</md-option>
											</md-select>
										</md-input-container>
									</div>
								</form>
							</fieldset>
						</md-content>
					</md-tab>
					<md-tab label="{{_STORE_BRANDS}}">
						<md-content class="md-padding">
							<fieldset>
								<form name="storeBrand" data-ng-disabled="formDisabled">
									<md-table-container>
										<table data-ng-model="privatedata.record['brands']" md-table md-row-select multiple>
											<thead md-head md-order="brandsQuery.order">
												<tr md-row>
													<th md-column md-order-by="brandId" resize="vertical">
												  		<div>{{_ID}}</div>
								  					</th>
													<th md-column md-order-by="brandCode"><div>{{_BRAND CODE}}</div></th>
													<th md-column md-order-by="brandName"><span>{{_BRAND_NAME}}</span></th>
													<th md-column md-order-by="onlineInsertTime">{{_Update Time}}</th>
												</tr>
											</thead>
											<tbody md-body>
												<tr md-row data-ng-repeat="StoreBrand in parentobj.privatedata.brandsWithDetails | filter: filter.search | orderBy: brandsQuery.order | limitTo: brandsQuery.limit : (brandsQuery.page - 1) * brandsQuery.limit" md-select="StoreBrand" md-select-id="brandId" md-auto-select md-on-select="storeBrandSelected" md-on-deselect="storeBrandUnselected">
													<td md-cell>
														{{StoreBrand.brandId}}
													</td>
													<td md-cell>{{StoreBrand.brandCode}}<br/>
														<md-chips readonly="true" data-ng-model="StoreBrand.parents" data-ng-if="StoreBrand.parents.length && StoreBrand.parents">
															<md-chip-template>
																{{ (parentobj.privatedata.brandsWithDetails|filter:{brandId:$chip.brandId}:true)[0]['brandCode'] }}
															</md-chip-template>
														</md-chips></td>
													<td md-cell>{{StoreBrand.brandName}}</td>
													<td md-cell>{{StoreBrand.onlineInsertTime}}</td>
												</tr>
											</tbody>
										</table>
									</md-table-container>
									<md-table-pagination md-label="{page: '{{_PAGE}}:', rowsPerPage: '{{_ROW PER PAGE}}:', of: '{{_OF}}'}" md-limit="brandsQuery.limit" md-limit-options="[10, 20, 30]" md-page="brandsQuery.page" md-total="{{parentobj.privatedata.brandsWithDetails.length}}" md-page-select md-boundary-links></md-table-pagination>
								</form>
							</fieldset>
						</md-content>
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
	<script type="text/ng-template" id="storeChangeOK.html">
		<md-toast>
			{{_Store updated successfully}}
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
						<md-button ng-click="grid.appScope.parentobj.getStoreDetails(row.entity);">
							<md-icon md-menu-align-target calss="material-icons" md-menu-align-target>edit</md-icon>
							{{_Edit}}
						</md-button>
					</md-menu-item>
				</md-menu-content>
			</md-menu>
		</div>
	</script>
</div>
