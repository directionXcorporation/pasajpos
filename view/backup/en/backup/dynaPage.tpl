<script type="text/ng-template" id="dynaPage.html">
    <div class="w3-container" ng-cloak flex layout="column">
    	<md-toolbar class="md-table-toolbar md-default" data-ng-if="!$ctrl.inModal">
    		<div class="md-toolbar-tools">
    			<span>{{$ctrl.pageCode}}</span>
    			<span flex></span>
    		</div>
    	</md-toolbar>
    	<div ui-i18n="{{$ctrl.lang}}" flex layout="column">
    		<md-progress-linear md-mode="query" data-ng-disabled="!$ctrl.formDisabled && !$ctrl.pageIsLoading"></md-progress-linear>
    		<div ng-include="'images/logo_animated.svg'" style="width:100%; height:100%;" data-ng-show="$ctrl.pageIsLoading"></div>
    		<div id="pageContent" flex layout="column" data-ng-hide="$ctrl.pageIsLoading">
    			<gridster options="$ctrl.data.options" class="customGridster" style="width: 100%; margin-left:auto; margin-right:auto;" flex layout="column" id="mainPageGridster" on-element-resize="">
    				<gridster-item item="item" data-ng-repeat="item in $ctrl.selectedScreen.items" flex layout-wrap>
    					<div bind-html-compile="item.element" flex layout="column" layout-wrap></div>
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
{{_PAGE_TEMPLATES}}
	
{{_PAGE_MODULE_TEMPLATES}}

{{_PAGE_INJECTIONS_TEMPLATES}}