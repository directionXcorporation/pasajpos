<div class="w3-container" data-ng-controller="parentobj.controllers.groups" ng-cloak>
	<md-toolbar class="md-table-toolbar md-default">
		<div class="md-toolbar-tools">
			<span>{{_Groups}}</span>
			<span flex></span>
			<md-button data-ng-click="parentobj.getGroupDetails('','create');">
				<md-icon aria-label="Add New Group" class="material-icons">
					add_box
	        		</md-icon>
        		</md-button>
		</div>
	</md-toolbar>
	
	<md-table-container>
		<table md-table>
			<thead md-head md-order="query.order" md-on-reorder="getGroups">
				<tr md-row>
					<th md-column md-order-by="groupId" resize="vertical">
				  		<div>{{_ID}}</div>
  					</th>
					<th md-column md-order-by="groupName"><div>{{_GROUP_NAME}}</div></th>
					<th md-column>{{_GROUP_DESCRIPTION}}</th>
					<th md-column md-order-by="startDate"><span>{{_VALID_FROM}}</span></th>
					<th md-column md-order-by="endDate"><span>{{_VALID_TO}}</span></th>
					<th md-column md-order-by="onlineInsertTime">{{_Update Time}}</th>
					<th md-column>{{_Actions}}</th>
				</tr>
			</thead>
			<tbody>
				
				<tr md-row md-body data-ng-repeat="group in privatedata.allgroupsWithDetails | filter: filter.search | orderBy: query.order | limitTo: query.limit : (query.page -1) * query.limit ">
					<td md-cell>{{group.groupId}}</td>
					<td md-cell>{{group.groupName}}<br/>
						<md-chips readonly="true" data-ng-model="group.parents" data-ng-if="group.parents.length && group.parents[0]['groupName']">
							<md-chip-template>
								<span style="font-size:9px;">{{ $chip.groupName }}</span>
							</md-chip-template>
						</md-chips></td>
					<td md-cell>{{group.groupDescription}}</td>
					<td md-cell>{{group.startDate}}</td>
					<td md-cell>{{group.endDate}}</td>
					<td md-cell>{{group.onlineInsertTime}}</td>
					<td md-cell>
						<md-button class="md-raised" id="edit_{{group.groupId}}" data-ng-click="parentobj.getGroupDetails(group);">
							<md-icon aria-label="User Edit Icon" md-font-icon="edit" style="color:grey" data-fittext>edit</md-icon>
						</md-button>
					</td>
				</tr>
			</tbody>
		</table>
	</md-table-container>

	<md-table-pagination md-label="{page: '{{_PAGE}}:', rowsPerPage: '{{_ROW PER PAGE}}:', of: '{{_OF}}'}" md-limit="query.limit" md-limit-options="[10, 20, 30]" md-page="query.page" md-total="{{privatedata.groupsCount}}" md-on-paginate="getGroups" md-page-select md-boundary-links></md-table-pagination>
	
	<script type="text/ng-template" id="editGroup.html">
		<md-dialog aria-label="{{_EDITING}} {{privatedata.record.groupName}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_EDITING}} {{privatedata.record.groupName}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose(privatedata.record)">
						<md-icon aria-label="Clse User Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-tabs md-dynamic-height md-border-bottom>
					<md-tab label="{{_BASIC_DETAILS}}">
						<md-content class="md-padding">
							<fieldset>
								<form name="groupDetails" data-ng-disabled="formDisabled">
									<div layout-gt-xs="column">
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_GROUP_NAME}}</label>
											<input data-ng-model="privatedata.record.groupName" />
										</md-input-container>
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_GROUP_DESCRIPTION}}</label>
											<textarea md-maxlength="255" data-ng-model="privatedata.record.groupDescription"></textarea>
										</md-input-container>
										<div layout="row">
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<md-switch ng-model="privatedata.record.status" aria-label="{{_GROUP_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
													{{_GROUP_STATUS}}
												</md-switch>
											</md-input-container>
											<md-datepicker data-ng-if="privatedata.record.status" data-ng-model="privatedata.record.startDate" md-placeholder="{{_VALID_FROM}}" md-open-on-focus acceptallzeroasdate="true"></md-datepicker>
											<md-datepicker data-ng-if="privatedata.record.status" data-ng-model="privatedata.record.endDate" md-placeholder="{{_VALID_TO}}" md-open-on-focus acceptallzeroasdate="true"></md-datepicker>
										</div>
									</div>
								</form>
							</fieldset>
						</md-content>
					</md-tab>
					<md-tab label="{{_GROUP_PARENTS}}">
						<md-content class="md-padding">
							<fieldset>
								<form name="groupParents" data-ng-disabled="formDisabled">
									<md-table-container>
										<table data-ng-model="privatedata.record.parents" md-table md-row-select multiple>
											<thead md-head md-order="groupParentsQuery.order">
												<tr md-row>
													<th md-column md-order-by="groupId" resize="vertical">
												  		<div>{{_ID}}</div>
								  					</th>
													<th md-column md-order-by="groupName"><div>{{_GROUP_NAME}}</div></th>
													<th md-column>{{_GROUP_DESCRIPTION}}</th>
													<th md-column md-order-by="startDate"><span>{{_VALID_FROM}}</span></th>
													<th md-column md-order-by="endDate"><span>{{_VALID_TO}}</span></th>
													<th md-column md-order-by="onlineInsertTime">{{_Update Time}}</th>
												</tr>
											</thead>
											<tbody md-body>
												<tr md-row data-ng-repeat="parentGroup in privatedata.allgroupsWithDetails | filter: filter.search | orderBy: groupParentsQuery.order | limitTo: groupParentsQuery.limit : (groupParentsQuery.page - 1) * groupParentsQuery.limit" md-select="parentGroup" md-select-id="groupId" md-auto-select data-ng-disabled="!((parentGroup.groupId != privatedata.record.groupId) && ((privatedata.record.children|filter:{groupId:parentGroup.groupId}:true).length <= 0))" md-on-select="parentGroupSelected" md-on-deselect="parentGroupUnselected">
													<td md-cell>
														{{parentGroup.groupId}}
														<md-tooltip md-direction="right" data-ng-if="(parentGroup.groupId == privatedata.record.groupId)">{{_You cannot select self as parent}}</md-tooltip>
														<md-tooltip md-direction="right" data-ng-if="((privatedata.record.children|filter:{groupId:parentGroup.groupId}:true).length > 0)">{{_You cannot select child group as parent}}</md-tooltip>
													</td>
													<td md-cell>{{parentGroup.groupName}}<br/>
														<md-chips readonly="true" data-ng-model="parentGroup.parents" data-ng-if="parentGroup.parents.length && parentGroup.parents[0]['groupName']">
															<md-chip-template>
																{{ $chip.groupName }}
															</md-chip-template>
														</md-chips></td>
													<td md-cell>{{parentGroup.groupDescription}}</td>
													<td md-cell>{{parentGroup.startDate}}</td>
													<td md-cell>{{parentGroup.endDate}}</td>
													<td md-cell>{{parentGroup.onlineInsertTime}}</td>
												</tr>
											</tbody>
										</table>
									</md-table-container>
									<md-table-pagination md-label="{page: '{{_PAGE}}:', rowsPerPage: '{{_ROW PER PAGE}}:', of: '{{_OF}}'}" md-limit="groupParentsQuery.limit" md-limit-options="[10, 20, 30]" md-page="groupParentsQuery.page" md-total="{{parentobj.privatedata.allgroups.length}}" md-page-select md-boundary-links></md-table-pagination>
								</form>
							</fieldset>
						</md-content>
					</md-tab>
					<md-tab label="{{_GROUP_FUNCTION}}">
						<md-content class="md-padding">
							<fieldset>
								<form name="groupFunctions" data-ng-disabled="formDisabled">
									<md-table-container style="width:100%;">
										<table data-ng-model="privatedata.record.functions" md-table md-row-select multiple style="width:100%;">
											<thead md-head md-order="functionsQuery.order">
												<tr md-row>
													<th md-column md-order-by="functionCode" resize="vertical">
												  		<div>{{_ID}}</div>
								  					</th>
													<th md-column md-order-by="functionName"><div>{{_FUNCTION_NAME}}</div></th>
													<th md-column>{{_FUNCTION_DESCRIPTION}}</th>
													<th md-column md-order-by="dependsOnCode"><span>{{_DEPENDS_ON}}</span></th>
													<th md-column md-order-by="categoryName"><span>{{_CATEGORY_NAME}}</span></th>
												</tr>
											</thead>
											<tbody md-body>
												<tr md-row data-ng-repeat="function in parentobj.privatedata.allfunctions | filter: filter.search | orderBy: functionsQuery.order | limitTo: functionsQuery.limit : (functionsQuery.page - 1) * functionsQuery.limit" md-select="function" md-select-id="functionCode" md-auto-select md-on-select="functionSelected" md-on-deselect="functionUnselected" data-ng-disabled="((privatedata.record.functions|filter:{dependsOnCode:function.functionCode}:true).length > 0)">
													<td md-cell>
														{{function.functionCode}}
														<md-tooltip md-direction="right" data-ng-if="((privatedata.record.functions|filter:{dependsOnCode:function.functionCode}:true).length > 0)">
															{{_You cannot uncheck since one or more dependant function is/are checked}}:
															<span data-ng-repeat="dependantItems in (privatedata.record.functions|filter:{dependsOnCode:function.functionCode}:true)">
																'{{dependantItems.functionName}}'
															</span>
														</md-tooltip>
													</td>
													<td md-cell>{{function.functionName}}</td>
													<td md-cell>{{function.functionDescription}}</td>
													<td md-cell>{{function.dependsOnCode}}</td>
													<td md-cell>{{function.categoryName}}</td>
												</tr>
											</tbody>
										</table>
									</md-table-container>
									<md-table-pagination md-label="{page: '{{_PAGE}}:', rowsPerPage: '{{_ROW PER PAGE}}:', of: '{{_OF}}'}" md-limit="functionsQuery.limit" md-limit-options="[10, 20, 30]" md-page="functionsQuery.page" md-total="{{parentobj.privatedata.allfunctions.length}}" md-page-select md-boundary-links></md-table-pagination>
								</form>
							</fieldset>
						</md-content>
					</md-tab>
					<md-tab label="{{_STORES}}">
						<md-content class="md-padding" data-ng-include="'/stores.php?setSelectable=multiple'">
							
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
	<script type="text/ng-template" id="groupChangeOK.html">
		<md-toast>
			{{_Group updated successfully}}
		</md-toast>
	</script>
</div>
