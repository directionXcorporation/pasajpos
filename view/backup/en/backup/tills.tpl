<div class="w3-container" data-ng-controller="parentobj.controllers.tills" ng-cloak>
	<md-toolbar class="md-table-toolbar md-default">
		<div class="md-toolbar-tools">
			<span>{{_Tills}}</span>
			<span flex></span>
			<md-button data-ng-click="parentobj.getTillDetails('','create');">
				<md-icon aria-label="Add New Till" class="material-icons">
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
					<md-select aria-label="{{_Change View}}" data-ng-model="privatedata.tillSelectedView" data-ng-change="changeTableView(privatedata.tillSelectedView)">
						<md-option data-ng-repeat="availableTillView in privatedata.availableTillViews" data-ng-value="availableTillView" data-ng-selected="$first">
							{{availableTillView.viewName}}
						</md-option>
					</md-select>
				</md-input-container>
				</div>
				<md-button id="deleteTillTableView" class="md-icon-button" aria-label="{{_Delete view}}" data-ng-if="isAllowedToSaveTillView" data-ng-show="privatedata.tillSelectedView.viewId" data-ng-click="parentobj.confirmDeleteSelectedView(privatedata.tillSelectedView.viewId, 'deleteTillTableView');">
					<md-icon class='material-icons'>delete</md-icon>
				</md-button>
				<md-button id="saveTillTableView" class="md-icon-button" aria-label="{{_Save view}}" data-ng-if="isAllowedToSaveTillView" data-ng-show="saveNewState && !privatedata.tillSelectedView.viewId" data-ng-click="parentobj.saveTableView('tills',tillsGridApi.saveState.save(),'saveTillTableView',privatedata.availableTillViews);">
					<md-icon class='material-icons'>save</md-icon>
				</md-button>
			</div>
		</md-toolbar>
		<div id="tillsTable" ui-grid="tillsGridOption" class="data-grid" {{ui-grid-selection}} ui-grid-resize-columns ui-grid-pinning ui-grid-move-columns ui-grid-exporter ui-grid-grouping ui-grid-pagination ui-grid-auto-resize ui-grid-save-state></div>
		<md-input-container>
			<md-switch aria-label="{{_Show Field Names}}" data-ng-model="tableProperties.showFieldNames" data-ng-true-value="1" data-ng-false-value="0">
				{{_Show Field Names}}
			</md-switch>
		</md-input-container>
	</div>
	<script type="text/ng-template" id="editTill.html">
		<md-dialog aria-label="{{_EDITING}} {{privatedata.record['tillsDetail_tillCode']}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_EDITING}} {{privatedata.record['tillsDetail_tillCode']}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose(privatedata.record)">
						<md-icon aria-label="Close Till Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-tabs md-dynamic-height md-border-bottom>
					<form name="tillDetails" data-ng-disabled="formDisabled">
						<md-tab label="{{_BASIC_DETAILS}}">
							<md-content class="md-padding">
								<fieldset>
								
									<div layout-gt-xs="row">
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_TILL CODE}}</label>
											<input type="text" data-ng-model="privatedata.record['tillsDetail_tillCode']" data-ng-change="privatedata.record['tillsDetail_tillCode']=privatedata.record['tillsDetail_tillCode'].toUpperCase();" />
										</md-input-container>
										<md-input-container class="md-icon-float md-block" flex-gt-xs>
											<label>{{_TILL_NUMBER}}</label>
											<md-select data-ng-model="privatedata.record['tillsDetail_tillNumber']">
												<md-option data-ng-value="1">1</md-option>
												<md-option data-ng-value="2">2</md-option>
												<md-option data-ng-value="3">3</md-option>
												<md-option data-ng-value="4">4</md-option>
												<md-option data-ng-value="5">5</md-option>
												<md-option data-ng-value="6">6</md-option>
											</md-select>
										</md-input-container>
									</div>
										<div layout="row">
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<md-switch ng-model="privatedata.record['tillsStatus_status']" aria-label="{{_TILL_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
													{{_TILL_STATUS}}
												</md-switch>
											</md-input-container>
										</div>
								</fieldset>
							</md-content>
						</md-tab>
						<md-tab label="{{_TILL_STORE}}">
							<md-content class="md-padding" data-ng-include="'/stores.php?setSelectable=single'">
							
							</md-content>
						</md-tab>
					</form>
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
	<script type="text/ng-template" id="tillChangeOK.html">
		<md-toast>
			{{_Till updated successfully}}
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
						<md-button ng-click="grid.appScope.parentobj.getTillDetails(row.entity);">
							<md-icon md-menu-align-target calss="material-icons" md-menu-align-target>edit</md-icon>
							{{_Edit}}
						</md-button>
					</md-menu-item>
				</md-menu-content>
			</md-menu>
		</div>
	</script>
</div>
