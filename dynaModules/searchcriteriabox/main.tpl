<form name="form" style="height: 100%;width:100%;">
    <md-content style="height: 100%;" id="{{$ctrl.id}}_content">
        <md-tabs style="min-height: unset;" layout-fill>
            <md-tab data-ng-repeat="screen in $ctrl.data.screens | orderBy: 'order'">
                <md-tab-label translate>{{screen.screenName}}</md-tab-label>
                <md-tab-body layout-wrap>
        	            <gridster options="$ctrl.data.options" class="nestedGridster" flex>
        		            <gridster-item item="item" data-ng-repeat="item in screen.items">
        		                <div bind-html-compile="item.element" class="nestedGridsterItem"></div>
        		            </gridster-item>
        	            </gridster>
                </md-tab-body>
            </md-tab>
        </md-tabs>
    </md-content>
</form>