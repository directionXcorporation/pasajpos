<div class="w3-container" data-ng-controller="parentobj.controllers.salesScreen" ng-cloak>
	<md-toolbar class="md-table-toolbar md-default">
		<div class="md-toolbar-tools">
			<span>{{_Sales_Screen_Templates}}</span>
			<span flex></span>
			<md-button data-ng-click="parentobj.getSalesScreenDetails('','create');">
				<md-icon aria-label="Add New Sales Screen" class="material-icons">
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
					<md-select aria-label="{{_Change View}}" data-ng-model="privatedata.salesScreenSelectedView" data-ng-change="changeTableView(privatedata.salesScreenSelectedView)">
						<md-option data-ng-repeat="availableSalesScreenView in privatedata.availableSalesScreenViews" data-ng-value="availableSalescreenView" data-ng-selected="$first">
							{{availableSalesScreenView.viewName}}
						</md-option>
					</md-select>
				</md-input-container>
				</div>
				<md-button id="deleteSalesScreenTableView" class="md-icon-button" aria-label="{{_Delete view}}" data-ng-if="isAllowedToSaveSalesScreenView" data-ng-show="privatedata.salesScreenSelectedView.viewId" data-ng-click="parentobj.confirmDeleteSelectedView(privatedata.salesScreenSelectedView.viewId, 'deleteSalesScreenTableView');">
					<md-icon class='material-icons'>delete</md-icon>
				</md-button>
				<md-button id="saveSalesScreenTableView" class="md-icon-button" aria-label="{{_Save view}}" data-ng-if="isAllowedToSaveSalesScreenView" data-ng-show="saveNewState && !privatedata.salesScreenSelectedView.viewId" data-ng-click="parentobj.saveTableView('salesScreen',salesScreenGridApi.saveState.save(),'savesalesScreenTableView',privatedata.availableSalesScreenViews);">
					<md-icon class='material-icons'>save</md-icon>
				</md-button>
			</div>
		</md-toolbar>
		<div id="salesScreenTable" ui-grid="salesScreenGridOption" class="data-grid" {{ui-grid-selection}} ui-grid-resize-columns ui-grid-pinning ui-grid-move-columns ui-grid-exporter ui-grid-grouping ui-grid-pagination ui-grid-auto-resize ui-grid-save-state></div>
		<md-input-container>
			<md-switch aria-label="{{_Show Field Names}}" data-ng-model="tableProperties.showFieldNames" data-ng-true-value="1" data-ng-false-value="0">
				{{_Show Field Names}}
			</md-switch>
		</md-input-container>
	</div>
	<script type="text/ng-template" id="editSalesScreen.html">
		<md-dialog aria-label="{{_EDITING}} {{privatedata.record['salesScreenDetail_salesScreenName']}}" flex="noshrink">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_EDITING}} {{privatedata.record['salesScreenDetail_salesScreenName']}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose(privatedata.record)">
						<md-icon aria-label="Close Sales Screen Edit" md-font-icon="close" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<md-tabs md-dynamic-height md-border-bottom>
						<md-tab label="{{_BASIC_DETAILS}}">
							<md-content class="md-padding">
								<fieldset>
									<form name="salesScreenDetails" data-ng-disabled="formDisabled">
										<div layout-gt-xs="row">
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<label>{{_SALESSCREEN_NAME}}</label>
												<input type="text" data-ng-model="privatedata.record['salesScreenDetail_salesScreenName']" />
											</md-input-container>
										</div>
										<div layout-gt-xs="row">
											<md-input-container>
												<textarea md-no-resize md-no-autogrow json-text style="overflow:auto;width:400px;height:300px;" readonly="readonly">{{privatedata.record['salesScreenDetail_salesScreenData']}}
												</textarea>
											</md-input-container>
											<md-button id="openVisualEditor" class="md-fab" aria-label="{{_Visual Editor}}"  data-ng-click="parentobj.openSalesScreenVisualEditor(privatedata.record['salesScreenDetail_salesScreenData']);">
												<md-icon class="material-icons">visibility</md-icon>
											</md-button>
										</div>
									</form>
									<form name="salesScreenStatus" data-ng-disabled="formDisabled">
										<div layout="row">
											<md-input-container class="md-icon-float md-block" flex-gt-xs>
												<md-switch ng-model="privatedata.record['salesScreenStatus_status']" aria-label="{{_SALESSCREEN_STATUS}}" ng-true-value="'1'" ng-false-value="'0'" class="md-primary">
													{{_SALESSCREEN_STATUS}}
												</md-switch>
											</md-input-container>
										</div>
									</form>	
								</fieldset>	
							</md-content>
						</md-tab>
						<md-tab label="{{_SALESSCREEN_STORES}}">
							<form name="salesScreenStores" data-ng-disabled="formDisabled">
								<md-content class="md-padding" data-ng-include="'/stores.php?setSelectable=multiple'">
								
								</md-content>
							</form>
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
	<script type="text/ng-template" id="salesScreenChangeOK.html">
		<md-toast>
			{{_SalesScreen updated successfully}}
		</md-toast>
	</script>
	<script type="text/ng-template" id="gridRowMenu.html">
		<div layout-align="start center" layout="column">	
			<md-menu md-position-mode="target-right target"> 
				<md-button aria-label="Open interactions menu" class="md-icon-button" data-ng-click="$mdMenu.open(ev)">
					<md-icon md-menu-origin class="material-icons">menu</md-icon> 
				</md-button>
				<md-menu-content width="2">
					<md-menu-item id="edit_{{row.entity['salesScreenDetail_salesScreenId']}}">
						<md-button ng-click="grid.appScope.parentobj.getSalesScreenDetails(row.entity);">
							<md-icon md-menu-align-target calss="material-icons" md-menu-align-target>edit</md-icon>
							{{_Edit}}
						</md-button>
					</md-menu-item>
				</md-menu-content>
			</md-menu>
		</div>
	</script>
	<script type="text/ng-template" id="salesScreenVisualEditor.html">
		<style>
			{{data.options.customCss}}
		</style>
		<md-dialog class="fullscreen-dialog" aria-label="{{_Visual Editor}}">
			<md-toolbar class="md-hue-2">
				<div class="md-toolbar-tools">
					<h2>{{_Visual Editor}}</h2>
					<md-button class="md-icon-button" data-ng-click="openGridsterOptions(data.options)" id="openGridsterOptions">
						<md-icon aria-label="Open Options">build</md-icon>
					</md-button>
					<md-button class="md-icon-button" data-ng-click="openElementSelection()" id="openElementSelection">
						<md-icon aria-label="Add New Element">widgets</md-icon>
					</md-button>
					<md-button class="md-icon-button" data-ng-click="clickToSave(data)">
						<md-icon aria-label="Save Sales Screen Data">save</md-icon>
					</md-button>
					<span flex></span>
					
					<md-button class="md-icon-button" data-ng-click="addNewScreen()" id="addNewScreen">
						<md-icon aria-label="Add New Screen">add</md-icon>
					</md-button>
					<md-select ng-model="selectedScreen" placeholder="{{_CURRENT_SCREEN}}" class="md-no-underline">
						<md-option data-ng-repeat="screen in data.screens" data-ng-value="screen">{{screen.screenName}}</md-option>
					</md-select>
					<md-button class="md-icon-button" data-ng-click="clickToClose(data)">
						<md-icon aria-label="Close Sales Screen {{_Visual Editor}}">close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content flex="noshrink">
				<md-progress-linear md-mode="query" data-ng-disabled="!formDisabled"></md-progress-linear>
				<gridster options="data.options" class="customGridster">
					<gridster-item item="item" data-ng-repeat="item in (data.screens | filter:{screenId:selectedScreen.screenId})[0].items">
						<div bind-html-compile="item.element" style="width:100%;height:100%;"></div>
					</gridster-item>
				</gridster>
			</md-dialog-content>
		</md-dialog>
	</script>
	<script type="text/ng-template" id="addNewScreen.html">
		<md-dialog aria-label="{{_NEW SCREEN DETAILS}}">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_NEW SCREEN DETAILS}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose()">
						<md-icon aria-label="Close {{_NEW SCREEN DETAILS}}" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content>
				<md-input-container class="md-block">
					<label>{{_NAME OF SCREEN}}</label>
					<input type="text" data-ng-model="screenName" />
				</md-input-container>
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
	<script type="text/ng-template" id="GridsterOptions.html">
		<md-dialog aria-label="{{_Sales Screen Options}}">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_Sales Screen Options}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose(options)">
						<md-icon aria-label="Close {{_Sales Screen Options}}" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content>
				<md-input-container class="md-block">
					<label>{{_MIN NUMBER OF COLUMNS}}</label>
					<input type="text" data-ng-model="options.minCols" />
				</md-input-container>
				<md-input-container class="md-block">
					<label>{{_MARGIN}}</label>
					<input type="text" data-ng-model="options.margin" />
				</md-input-container>
				<md-input-container class="md-block">
					<label>{{_MIN NUMBER OF ROWS}}</label>
					<input type="text" data-ng-model="options.minRows" />
				</md-input-container>
				<md-switch class="md-primary" md-no-ink aria-label="{{_ITEM swapping}}" ng-model="options.swap" ng-true-value="true" ng-false-value="false">
					{{_ITEM swapping}}
				</md-switch>
				<md-switch class="md-primary" md-no-ink aria-label="{{_PUSH ITEMS}}" ng-model="options.pushItems" ng-true-value="true" ng-false-value="false">
					{{_PUSH ITEMS}}
				</md-switch>
				<md-input-container class="md-block">
					<label>{{_CUSTOM_CSS}}</label>
					<textarea ng-model="options.customCss" rows="5" md-select-on-focus></textarea>
				</md-input-container>
				<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button class="md-raised md-primary" data-ng-click="clickToSave(options)" >
						{{_SAVE}}
					</md-button>
					<md-button class="md-raised" data-ng-click="clickToClose(options)">
						{{_CANCEL}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
	
	<script type="text/ng-template" id="GridsterElements.html">
		<md-dialog aria-label="{{_Sales Screen Elements}}">
			<md-toolbar>
				<div class="md-toolbar-tools">
					<h2>{{_Sales Screen Elements}}</h2>
					<span flex></span>
					<md-button class="md-icon-button" data-ng-click="clickToClose()">
						<md-icon aria-label="Close {{_Sales Screen Elements}}" data-fittext>close</md-icon>
					</md-button>
				</div>
			</md-toolbar>
			<md-dialog-content>
				<md-button class="md-raised" data-ng-click='makeModule(module.type, module.parameters, addModule)' data-ng-repeat="module in availableModules">
					{{module.name}}
					<md-tooltip md-direction="top">{{module.description}}</md-tooltip>
				</md-button>
				<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
					<md-button class="md-raised" data-ng-click="clickToClose()">
						{{_CANCEL}}
					</md-button>
				</md-dialog-actions>
			</md-dialog-content>
		</md-dialog>
	</script>
	<script type="text/ng-template" id="modules_fixed.html">
		<md-toolbar>
			<div class="md-toolbar-tools">
				<h2>{{_FIXED_TEXT}}</h2>
				<span flex></span>
				<md-button class="md-icon-button" data-ng-click="clickToClose()">
					<md-icon aria-label="Close {{_FIXED_TEXT}}" data-fittext>close</md-icon>
				</md-button>
			</div>
		</md-toolbar>
		<md-dialog-content>
			<md-content class="md-padding">
				<md-input-container class="md-block">
					<label>{{_CUSTOM_CSS_CLASS}}</label>
					<input type="text" data-ng-model="class" />
				</md-input-container>
				<md-input-container class="md-block">
					<label>{{_TEXT}}</label>
					<textarea ng-model="text" rows="3" md-select-on-focus></textarea>
				</md-input-container>
			</md-content>
			<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
				<md-button class="md-raised" data-ng-click="hide()">
					{{_SAVE}}
				</md-button>
				<md-button class="md-raised" data-ng-click="cancel()">
					{{_CANCEL}}
				</md-button>
			</md-dialog-actions>
		</md-dialog-content>
		
	</script>
	<script type="text/ng-template" id="modules_time.html">
		<md-toolbar>
			<div class="md-toolbar-tools">
				<h2>{{_TIME AND DATE}}</h2>
				<span flex></span>
				<md-button class="md-icon-button" data-ng-click="clickToClose()">
					<md-icon aria-label="Close {{_TIME AND DATE}}" data-fittext>close</md-icon>
				</md-button>
			</div>
		</md-toolbar>
		<md-dialog-content>
			<md-content class="md-padding">
				<md-input-container class="md-block">
					<label>{{_CUSTOM_CSS_CLASS}}</label>
					<input type="text" data-ng-model="class" />
				</md-input-container>
				<md-input-container class="md-block">
					<label>{{_TEXT BEFORE TIME}}</label>
					<textarea data-ng-model="text" rows="3" md-select-on-focus></textarea>
				</md-input-container>
				<md-input-container class="md-block">
					<label>{{_TIME_FORMAT}}</label>
					<input type="text" data-ng-model="format" />
				</md-input-container>
			</md-content>
			<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
				<md-button class="md-raised" data-ng-click="hide()">
					{{_SAVE}}
				</md-button>
				<md-button class="md-raised" data-ng-click="cancel()">
					{{_CANCEL}}
				</md-button>
			</md-dialog-actions>
		</md-dialog-content>
	</script>
	<script type="text/ng-template" id="modules_button.html">
		<md-toolbar>
			<div class="md-toolbar-tools">
				<h2>{{_BUTTON}}</h2>
				<span flex></span>
				<md-button class="md-icon-button" data-ng-click="clickToClose()">
					<md-icon aria-label="Close {{_BUTTON}}" data-fittext>close</md-icon>
				</md-button>
			</div>
		</md-toolbar>
		<md-dialog-content>
			<md-content class="md-padding">
				<md-input-container class="md-block">
					<label>{{_CUSTOM_CSS_CLASS}}</label>
					<input type="text" data-ng-model="class" />
				</md-input-container>
				<md-input-container class="md-block">
					<label>{{_TEXT ON BUTTON}}</label>
					<input type="text" data-ng-model="text" />
				</md-input-container>
				<md-input-container class="md-block">
					<label>{{_ACTION}}</label>
					<input type="text" data-ng-model="action" />
				</md-input-container>
			</md-content>
			<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="formDisabled">
				<md-button class="md-raised" data-ng-click="hide()">
					{{_SAVE}}
				</md-button>
				<md-button class="md-raised" data-ng-click="cancel()">
					{{_CANCEL}}
				</md-button>
			</md-dialog-actions>
		</md-dialog-content>
	</script>
	
</div>