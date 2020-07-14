<script type="text/ng-template" id="dynaPage.html">
    <div class="w3-container" ng-cloak flex layout="column">
    	<md-toolbar class="md-table-toolbar page-toolbar toolbar" data-ng-if="!$ctrl.inModal">
    		<div class="md-toolbar-tools" layout-wrap="" layout="row">
    			<div flex class="toolbar-item" layout-align="start center" id="pageCodeText">
    			    <span translate>{{$ctrl.pageCode}}</span>
    			</div>
    			<div layout="row" data-ng-if="$ctrl.enablePageEdit" class="toolbar-item">
					<md-button class="md-icon-button" data-ng-click="$ctrl.moduleManager.openGridsterOptions($ctrl.data.options)" id="openGridsterOptions">
						<md-icon class='material-icons' aria-label="Open Options">build</md-icon>
					</md-button>
					<md-button class="md-icon-button" data-ng-click="$ctrl.moduleManager.openModuleSelection($ctrl.data, $ctrl.selectedScreen.screenId)" id="openModuleSelection">
						<md-icon class='material-icons' aria-label="Add New Element">widgets</md-icon>
					</md-button>
					<md-button class="md-icon-button" data-ng-click="$ctrl.moduleManager.clickToSave($ctrl.data)">
						<md-icon class='material-icons' aria-label="Save Sales Screen Data">save</md-icon>
					</md-button>
					<md-button class="md-icon-button" data-ng-click="$ctrl.moduleManager.addNewScreen($ctrl.data)" id="addNewScreen">
						<md-icon class='material-icons' aria-label="Add New Screen">add</md-icon>
					</md-button>
					<div>
    					<md-select ng-model="$ctrl.selectedScreen" class="md-no-underline" data-ng-change="$ctrl.changeScreen($ctrl.selectedScreen.screenId);">
    						<md-option data-ng-repeat="screen in $ctrl.data.screens" data-ng-value="screen">{{screen.screenName}}</md-option>
    					</md-select>
					</div>
				</div>
				<div class="toolbar-item" layout-wrap="" flex="30">
    			    <smartticketinput pagecode="$ctrl.pageCode"></smartticketinput>
    			</div>
				<div class="toolbar-item" style="width:24px;"></div>
    		</div>
    	</md-toolbar>
    	<div ui-i18n="{{$ctrl.lang}}" flex layout="column" class="main-content">
    		<md-progress-linear md-mode="query" data-ng-disabled="!$ctrl.formDisabled && !$ctrl.pageIsLoading"></md-progress-linear>
    		<div ng-include="'images/logo_animated.svg'" style="width:100%; height:100%;" data-ng-show="$ctrl.pageIsLoading"></div>
    		<div id="pageContent" flex layout="column" data-ng-hide="$ctrl.pageIsLoading">
    			<gridster options="$ctrl.data.options" class="customGridster" style="width: 100%; margin-left:auto; margin-right:auto;" flex layout="column" id="mainPageGridster" on-element-resize="">
    				<gridster-item item="item" data-ng-repeat="item in $ctrl.selectedScreen.items" flex layout-wrap>
    					<div bind-html-compile="item.element" flex layout="column" layout-wrap class="gridster-item-content"></div>
						<div class="drag-handler" data-ng-show="$ctrl.enablePageEdit"><md-icon class='material-icons' aria-label="Drg Element">open_with</md-icon></div>
    				</gridster-item>
    			</gridster>
    			<div data-ng-if="$ctrl.data.stickyScreen && $ctrl.data.stickyScreen.items.length" layout="{{$ctrl.data.stickyScreen.layout}}" layout-align="{{$ctrl.data.stickyScreen.layoutAlign}}" class="floating-content">
    			    <div data-ng-repeat="item in $ctrl.data.stickyScreen.items">
    			        <div bind-html-compile="item.element" flex layout="column" layout-wrap></div>
    			    </div>
    			</div>
    		</div>
    	</div>
    </div>
</script>
<!--DYNAPAGE TEMPLATES::START-->
    {{_PAGE_TEMPLATES}}
    {{_PAGE_MODULE_TEMPLATES}}
    {{_PAGE_INJECTIONS_TEMPLATES}}
<!--DYNAPAGE TEMPLATES::END-->