<md-toolbar class="toolbar" style="height: 100%;">
	<div class="md-toolbar-tools">
		<div flex>
		    <md-button class="md-icon-button" aria-label="Previous Page" data-ng-disabled="($ctrl.pagenumber <= 1) || ($ctrl.callinprogress)" data-ng-click="$ctrl.pagenumber = $ctrl.pagenumber - 1;">
    			<md-icon class='material-icons'>skip_previous</md-icon>
    		</md-button>
			<md-container flex>
				<!--<md-select aria-label="Change Page" data-ng-model="$ctrl.pagenumber" data-ng-if="$ctrl.totalitems">
					<md-option data-ng-repeat="pageNumber in $ctrl.allPagesArray" data-ng-value="pageNumber">
						{{pageNumber}}
					</md-option>
				</md-select>-->
				    {{$ctrl.pagenumber}}
			</md-container>
    		<md-button class="md-icon-button" aria-label="Next Page" data-ng-disabled="!$ctrl.totalitems || (($ctrl.pagenumber * $ctrl.pagesize) >= $ctrl.totalitems) || ($ctrl.callinprogress)" data-ng-click="$ctrl.pagenumber = $ctrl.pagenumber + 1;">
    			<md-icon class='material-icons'>skip_next</md-icon>
    		</md-button>
		</div>
		<div>
		    <md-select aria-label="Change Page Size" data-ng-model="$ctrl.pagesize" data-ng-show="$ctrl.allpagesizes.length" data-ng-change="$ctrl.pagenumber = -1;" data-ng-disabled="$ctrl.callinprogress">
				<md-option data-ng-repeat="pagesize in $ctrl.allpagesizes" data-ng-value="pagesize">
					{{pagesize}} <span translate>per page</span>
				</md-option>
			</md-select>
		</div>
	</div>
</md-toolbar>