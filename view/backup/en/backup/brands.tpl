<div class="w3-container" data-ng-controller="parentobj.controllers.brands" ng-cloak>
	<md-toolbar class="md-table-toolbar md-default">
		<div class="md-toolbar-tools">
			<span>{{_Brands}}</span>
			<span flex></span>
			<md-button data-ng-click="parentobj.getBrandDetails('','create');">
				<md-icon aria-label="Add New Brand" class="material-icons">
					add_box
	        		</md-icon>
        		</md-button>
		</div>
	</md-toolbar>
	
	<md-table-container>
		<table md-table data-ng-model="parentobj.privatedata.initialSelectedBrands" {{_BRAND_SELECT}}>
			<thead md-head md-order="query.order" md-on-reorder="getBrands">
				<tr md-row>
					<th md-column md-order-by="brandId" resize="vertical">
				  		<div>{{_ID}}</div>
  					</th>
					<th md-column md-order-by="brandCode"><div>{{_BRAND CODE}}</div></th>
					<th md-column md-order-by="brandName"><span>{{_BRAND_NAME}}</span></th>
					<th md-column md-order-by="onlineInsertTime">{{_Update Time}}</th>
					<th md-column>{{_Actions}}</th>
				</tr>
			</thead>
			<tbody>
				
				<tr md-row md-body data-ng-repeat="brand in parentobj.privatedata.brandsWithDetails | filter: filter.search | orderBy: query.order | limitTo: query.limit : (query.page -1) * query.limit " data-ng-init="brand.parents = makeparent(brand.parent_brandId.split(',').length ? brand.parent_brandId.split(',') : []);" md-select="brand" md-select-id="brandId" md-auto-select md-on-select="brandSelected" md-on-deselect="brandUnselected">
					<td md-cell>{{brand.brandId}}</td>
					<td md-cell>{{brand.brandCode}}<br/>
						<md-chips readonly="true" data-ng-model="brand.parents" data-ng-if="brand.parents.length">
							<md-chip-template>
								<span style="font-size:9px;">{{ (parentobj.privatedata.brandsWithDetails|filter:{brandId:$chip.brandId}:true)[0]['brandCode'] }}</span>
							</md-chip-template>
						</md-chips></td>
					<td md-cell>{{brand.brandName}}</td>
					<td md-cell>{{brand.onlineInsertTime}}</td>
					<td md-cell>
						<md-button class="md-raised" id="edit_{{brand.brandId}}" data-ng-click="parentobj.getBrandDetails(brand);">
							<md-icon aria-label="Brand Edit Icon" md-font-icon="edit" style="color:grey" data-fittext>edit</md-icon>
						</md-button>
					</td>
				</tr>
			</tbody>
		</table>
	</md-table-container>

	<md-table-pagination md-label="{page: '{{_PAGE}}:', rowsPerPage: '{{_ROW PER PAGE}}:', of: '{{_OF}}'}" md-limit="query.limit" md-limit-options="[10, 20, 30]" md-page="query.page" md-total="{{parentobj.privatedata.brandsWithDetails.length}}" md-on-paginate="getBrands" md-page-select md-boundary-links></md-table-pagination>
	
	<script type="text/ng-template" id="editBrand.html">
		<md-dialog aria-label="{{_EDITING}} {{privatedata.record.brandName}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_EDITING}} {{privatedata.record.brandName}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose(privatedata.record)">
						<md-icon aria-label="Close Brand Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-tabs md-dynamic-height md-border-bottom>
					<md-tab label="{{_BASIC_DETAILS}}">
						<md-content class="md-padding">
							<fieldset>
								<form name="brandDetails" data-ng-disabled="formDisabled">
									<div layout-gt-xs="column">
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_BRAND CODE}}</label>
											<input data-ng-model="privatedata.record.brandCode" data-ng-change="privatedata.record.brandCode=privatedata.record.brandCode.toUpperCase();" />
										</md-input-container>
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_BRAND_NAME}}</label>
											<textarea md-maxlength="255" data-ng-model="privatedata.record.brandName"></textarea>
										</md-input-container>
										<div layout="row">
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<md-switch ng-model="privatedata.record.status" aria-label="{{_BRAND_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
													{{_BRAND_STATUS}}
												</md-switch>
											</md-input-container>
										</div>
									</div>
								</form>
							</fieldset>
						</md-content>
					</md-tab>
					<md-tab label="{{_BRAND_PARENTS}}">
						<md-content class="md-padding">
							<fieldset>
								<form name="brandParents" data-ng-disabled="formDisabled">
									<md-table-container>
										<table data-ng-model="privatedata.record.parents" md-table md-row-select multiple>
											<thead md-head md-order="parentsQuery.order">
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
												<tr md-row data-ng-repeat="parentBrand in parentobj.privatedata.brandsWithDetails | filter: filter.search | orderBy: parentsQuery.order | limitTo: parentsQuery.limit : (parentsQuery.page - 1) * parentsQuery.limit" md-select="parentBrand" md-select-id="brandId" md-auto-select data-ng-disabled="!((parentBrand.brandId != privatedata.record.brandId))" md-on-select="parentBrandSelected" md-on-deselect="parentBrandUnselected">
													<td md-cell>
														{{parentBrand.brandId}}
														<md-tooltip md-direction="right" data-ng-if="(parentBrand.brandId == privatedata.record.brandId)">{{_You cannot select self as parent}}</md-tooltip>
														<md-tooltip md-direction="right" data-ng-if="((privatedata.record.children|filter:{groupId:parentGroup.groupId}:true).length > 0)">{{_You cannot select child brand as parent}}</md-tooltip>
													</td>
													<td md-cell>{{parentBrand.brandCode}}<br/>
														<md-chips readonly="true" data-ng-model="parentBrand.parents" data-ng-if="parentBrand.parents.length && parentBrand.parents">
															<md-chip-template>
																{{ (parentobj.privatedata.brandsWithDetails|filter:{brandId:$chip.brandId}:true)[0]['brandCode'] }}
															</md-chip-template>
														</md-chips></td>
													<td md-cell>{{parentBrand.brandName}}</td>
													<td md-cell>{{parentGroup.onlineInsertTime}}</td>
												</tr>
											</tbody>
										</table>
									</md-table-container>
									<md-table-pagination md-label="{page: '{{_PAGE}}:', rowsPerPage: '{{_ROW PER PAGE}}:', of: '{{_OF}}'}" md-limit="parentsQuery.limit" md-limit-options="[10, 20, 30]" md-page="parentsQuery.page" md-total="{{parentobj.privatedata.brandsWithDetails.length}}" md-page-select md-boundary-links></md-table-pagination>
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
	<script type="text/ng-template" id="brandChangeOK.html">
		<md-toast>
			{{_Brand updated successfully}}
		</md-toast>
	</script>
</div>
