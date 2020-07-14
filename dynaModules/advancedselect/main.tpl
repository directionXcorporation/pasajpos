<div layout="column">
    <div layout="row">
        <div flex>
            
                <!--<ui-select append-to-body="true" multiple ng-model="$ctrl.preselected" theme="bootstrap" sortable="true" close-on-select="false">
                        <ui-select-match placeholder="{{$ctrl.text}}">{{$item[$ctrl.namefieldnameJSON[0].valuefield]}}</ui-select-match>
                        <ui-select-choices repeat="item[$ctrl.idfieldname] as item in $ctrl.selectedValues | filter: $select.search">
                            <div data-ng-repeat="name in $ctrl.namefieldnameJSON track by name.valuefield" style="display:inline-block;">
                                <span ng-bind-html="item[name.valuefield] | highlight: $select.search"></span>
                                <span>{{name.separator}}</span>
                            </div>
                        </ui-select-choices>
                </ui-select>-->
                <!-- TODO: WHEN PARAMETER TYPE IS "A": IS ARRAY FOR COMMAND, WE SHOULd ALSO IDENTIFY WHICH PROPERTY TO GET NAD THEN READ THAT PROPERTY. IF NO PARAMETER DEFINED, THEN IMPLODE-->
            <md-input-container>
                <label translate>{{$ctrl.text}}</label>
                <md-select ng-model="$ctrl.preselected" md-on-close="clearSearchTerm()" data-md-container-class="selectheader" data-ng-if='$ctrl.type != "single"' multiple="1" data-ng-disabled="$ctrl.disabled">
                    <md-select-header>
                        <md-input-container>
                            <label><span translate>Searh</span>...</label>
                            <input ng-model="$ctrl.searchTerm" type="search" ng-keydown="$event.stopPropagation();" />
                        </md-input-container>
                    </md-select-header>
                    <md-optgroup label="{{$ctrl.text}}">
                        <md-option ng-value="item[$ctrl.idfieldname]" ng-repeat="item in $ctrl.data |
                          filter:$ctrl.searchTerm track by item[$ctrl.idfieldname]">
                            <div data-ng-repeat="name in $ctrl.namefieldnameJSON track by name.valuefield" style="display:inline-block;">
                                {{item[name.valuefield]}}
                                <span>{{name.separator}}</span>
                            </div>
                        </md-option>
                    </md-optgroup>
                </md-select>
                <md-select ng-model="$ctrl.preselected" md-on-close="clearSearchTerm()" data-md-container-class="selectheader" data-ng-if='$ctrl.type == "single"' data-ng-disabled="$ctrl.disabled">
                    <md-select-header>
                        <md-input-container>
                            <label><span translate>Searh</span>...</label>
                            <input ng-model="$ctrl.searchTerm" type="search" ng-keydown="$event.stopPropagation();" />
                        </md-input-container>
                    </md-select-header>
                    <md-optgroup label="{{$ctrl.text}}">
                        <md-option ng-value=""></md-option>
                        <md-option ng-value="item[$ctrl.idfieldname]" ng-repeat="item in $ctrl.data | filter:$ctrl.softsearchparameters | filter:$ctrl.searchTerm">
                            <div data-ng-repeat="name in $ctrl.namefieldnameJSON track by name.valuefield" style="display:inline-block;">
                                {{item[name.valuefield]}}
                                <span>{{name.separator}}</span>
                            </div>
                        </md-option>
                    </md-optgroup>
                </md-select>
            </md-input-container>
        </div>
        <div data-ng-if="$ctrl.pageid">
            <md-container>
                <md-button class="md-raised md-primary" ng-click="$ctrl.loadPage()" translate>Add</md-button>
            </md-container>
        </div>
    </div>
</div>