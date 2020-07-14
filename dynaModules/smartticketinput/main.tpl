<div layout="row" style="position: relative; display: inline-flex;width:100%;height: 100%;" layout-wrap="" layout-align="start center">
    <div flex>
        <md-autocomplete
            md-selected-item="$ctrl.selectedItem"
            md-search-text-change="$ctrl.searchIntro($ctrl.searchText)"
            md-search-text="$ctrl.searchText"
            md-selected-item-change="$ctrl.startIntro(intro.dynaIntro_introId)"
            md-items="intro in $ctrl.searchIntro($ctrl.searchText)"
            md-item-text="intro.dynaIntro_introTitle"
            md-min-length="3" 
            md-delay="500"
        placeholder="Try searching How to ...">
            <md-item-template>
                <span>{{intro.dynaIntro_introTitle}}</span>
            </md-item-template>
            <md-not-found>
                <div translate>I could not find what you were looking for</div>.
                <div ng-click="$ctrl.startChat($ctrl.searchText)" translate>Chat with a real person</div>
            </md-not-found>
        </md-autocomplete>
    </div>
    <div></div>
    <div>
        <md-button class="md-icon-button" data-ng-click="$ctrl.toggleHints($ctrl.pagecode, $ctrl.selectedScreen.screenId)" id="openElementSelection">
        	<md-icon class='material-icons' aria-label="Help">help</md-icon>
        </md-button>
    </div>
</div>