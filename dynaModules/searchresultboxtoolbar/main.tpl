<md-toolbar class="toolbar" style="height: 100%;">
	<div class="md-toolbar-tools">
		<div flex layout="row">
			<md-input-container flex style="margin: 0px">
				<md-select aria-label="Change View" data-ng-model="$ctrl.selectedView" data-ng-if="$ctrl.changeView" data-ng-change="$ctrl.changeView($ctrl.selectedView)">
					<md-option data-ng-repeat="view in $ctrl.availableTableViews" data-ng-value="view">
						<span translate>{{view.tableViewDetail_viewName}}</span>
					</md-option>
				</md-select>
			</md-input-container>
			<md-container>
        		<md-button class="md-icon-button" aria-label="Delete View" data-ng-if="$ctrl.selectedView.tableViewDetail_viewId && $ctrl.createTableView" data-ng-click="$ctrl.createTableView({tableCode: $ctrl.tablemapcode, tableState: $ctrl.selectedView.tableViewDetail_tableState, db: $ctrl.db, optionalData: {tableViewStatus_status: 0, tableViewDetail_viewId: $ctrl.selectedView.tableViewDetail_viewId, tableView: $ctrl.selectedView}});">
        			<md-icon class='material-icons'>delete</md-icon>
        		</md-button>
        		<md-button  class="md-icon-button" aria-label="Delete View" data-ng-if="$ctrl.selectedView.tableViewDetail_viewId && $ctrl.createTableView" data-ng-click="$ctrl.createTableView({tableCode: $ctrl.tablemapcode, tableState: $ctrl.tablestatevar, db: $ctrl.db, optionalData: {tableViewDetail_viewId: $ctrl.selectedView.tableViewDetail_viewId, tableView: $ctrl.selectedView}});">
        			<md-icon class='material-icons'>edit</md-icon>
        		</md-button>
        		<md-button class="md-icon-button" aria-label="Save View" data-ng-if="$ctrl.createTableView" data-ng-click="$ctrl.createTableView({tableCode: $ctrl.tablemapcode, tableState: $ctrl.tablestatevar, db: $ctrl.db, optionalData: {tableViewStatus_status: 1}});">
        					<md-icon class='material-icons'>save</md-icon>
        		</md-button>
    		</md-container>
		</div>
	</div>
</md-toolbar>