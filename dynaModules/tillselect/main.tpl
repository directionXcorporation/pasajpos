<div layout="row">
    <div layout="row" flex>
            <md-input-container>
                <label translate>Select Store</label>
                <md-select ng-model="$ctrl.selectedstoreid" multiple="0" id="tillselect_stores">
                    <md-select-header>
                        <md-input-container>
                            <label><span translate>Searh</span>...</label>
                            <input ng-model="$ctrl.storeSearchTerm" type="search" ng-keydown="$event.stopPropagation();" />
                        </md-input-container>
                    </md-select-header>
                    <md-optgroup label="Select Store">
                        <md-option ng-value=""></md-option>
                        <md-option ng-value="store.storesDetail_storeId" ng-repeat="store in $ctrl.stores |
                          filter:$ctrl.storeSearchTerm track by store.storesDetail_storeId">
                            {{store.storesDetail_storeName}}
                        </md-option>
                    </md-optgroup>
                </md-select>
            </md-input-container>
            <md-input-container>
                <label translate>Select Till</label>
                <md-select ng-model="$ctrl.selectedtillid" data-ng-disabled="!$ctrl.selectedstoreid" id="tillselect_tills">
                    <md-option ng-value=""></md-option>
                    <md-option ng-value="till['tillsDetail_tillId']" ng-repeat="till in $ctrl.tills |
                          filter:{tillsDetail_storeId: $ctrl.selectedstoreid} track by till.tillsDetail_tillId">
                            {{till.tillsDetail_tillNumber | uppercase}}- {{till.tillsDetail_tillCode | uppercase}}
                    </md-option>
                </md-select>
            </md-input-container>
        </div>
</div>