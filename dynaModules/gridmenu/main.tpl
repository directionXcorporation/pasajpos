<div layout-padding>
		<md-grid-list
		md-cols-xs="2" md-cols-sm="3" md-cols-md="4" md-cols-gt-md="5"
		md-row-height="1:1">
			<md-grid-tile md-rowspan="1" md-colspan="1" data-ng-repeat-start="node in $ctrl.currentData | orderBy : $ctrl.orderbycolumn" class="md-whiteframe-3dp"  data-ng-show="node[$ctrl.namecolumn]">
				<md-button data-ng-if="!node[$ctrl.childcolumn].length" data-ng-click="$ctrl.clickaction(node)" class="home-menu-item" style="margin: 0px;" data-ng-disabled="!appIsOnline && $ctrl.availableofflinecolumn=='0'">
					<md-icon aria-label="Show {{node[$ctrl.namecolumn]}}" md-font-icon="{{node[$ctrl.iconcolumn]}}" flex="" data-fittext style="display: inline;">{{node[$ctrl.iconcolumn]}}</md-icon>
					<md-grid-tile-footer style="justify-content: center;">
						<span flex="grow" translate>
							{{node[$ctrl.namecolumn]}}
						</span>
					</md-grid-tile-footer>
				</md-button>
				<md-button data-ng-if="node[$ctrl.childcolumn].length" data-ng-click="$ctrl.parentData[node[$ctrl.idcolumn]] = $ctrl.currentData; $ctrl.currentData = node[$ctrl.childcolumn];" class="home-menu-item" style="margin: 0px;" data-ng-disabled="!appIsOnline && $ctrl.availableofflinecolumn=='0'">
					<md-icon aria-label="Show {{node[$ctrl.namecolumn]}}" md-font-icon="{{node[$ctrl.iconcolumn]}}" flex="" data-fittext style="display: inline;">{{node[$ctrl.iconcolumn]}}</md-icon>
					<md-grid-tile-footer style="justify-content: center;">
						<span flex="grow" translate>
							{{node[$ctrl.namecolumn]}}
						</span>
						<md-icon aria-label="Expand" data-ng-if="node[$ctrl.childcolumn].length" class="fa"
							data-ng-attr-md-font-icon="keyboard_arrow_right" 
							style="display: inline;">
							keyboard_arrow_right
						</md-icon>
					</md-grid-tile-footer>
				</md-button>
			</md-grid-tile>
			<md-grid-tile data-ng-repeat-end="" md-rowspan="1" md-colspan="1" data-ng-if="$last && node[$ctrl.parentcolumn]" class="md-whiteframe-3dp">
				<md-button data-ng-click="$ctrl.currentData=$ctrl.parentData[node[$ctrl.parentcolumn]];" class="home-menu-item"  style="margin: 0px;">
					<md-icon aria-label="Return to previouse menu" md-font-icon="keyboard_backspace" data-fittext style="display: inline;">keyboard_backspace</md-icon>
					<md-grid-tile-footer style="justify-content: center;">
						<h3 data-fittext translate>
							Back
						</h3>
					</md-grid-tile-footer>
				</md-button>
			</md-grid-tile>
		</md-grid-list>
</div>