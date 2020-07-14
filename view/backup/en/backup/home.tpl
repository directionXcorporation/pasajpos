<div data-ng-controller="parentobj.home">
	<div layout-padding>
		<md-grid-list
		md-cols-xs="2" md-cols-sm="3" md-cols-md="4" md-cols-gt-md="5"
		md-row-height="1:1">
			<md-grid-tile md-rowspan="1" md-colspan="1" data-ng-repeat-start="node in parentobj.privatedata.homemenu" class="md-whiteframe-3dp"  data-ng-show="node.dynaPageMenu_menuName">
				<md-button data-ng-click="parentobj.menuClick(node.dynaPageMenu_pageId,node.dynaPageMenu_menuCode, parentobj.checkLength(node.nodes),node.nodes,node.dynaPageMenu_parentCode);" class="home-menu-item" style="margin: 0px;" data-ng-disabled="!appIsOnline && node.dynaPageDetails_availableOffline=='0'">
					<md-icon aria-label="Show {{node.dynaPageMenu_menuName}}" md-font-icon="{{node.dynaPageMenu_menuIcon}}" flex="" data-fittext style="display: inline;">{{node.dynaPageMenu_menuIcon}}</md-icon>
					<md-grid-tile-footer style="justify-content: center;">
						<span flex="grow">
							{{node.dynaPageMenu_menuName}}
						</span>
						<md-icon aria-label="Expand/Collapse" data-ng-if="parentobj.checkLength(node.nodes)" class="fa"
							data-ng-attr-md-font-icon="{{ expanded[node.dynaPageMenu_menuCode] ? 'keyboard_arrow_down' : 'keyboard_arrow_right'}}" 
							style="display: inline;">
							{{ expanded[node.dynaPageMenu_menuCode] ? 'keyboard_arrow_down' : 'keyboard_arrow_right'}}
						</md-icon>
					</md-grid-tile-footer>
				</md-button>
			</md-grid-tile>
			<md-grid-tile data-ng-repeat-end="" md-rowspan="1" md-colspan="1" data-ng-if="$last && node.dynaPageMenu_parentCode" class="md-whiteframe-3dp">
				<md-button data-ng-click="parentobj.menuReturn(node.dynaPageMenu_parentCode);" class="home-menu-item"  style="margin: 0px;">
					<md-icon aria-label="Return to previouse menu" md-font-icon="keyboard_backspace" data-fittext style="display: inline;">keyboard_backspace</md-icon>
					<md-grid-tile-footer style="justify-content: center;">
						<h3 data-fittext>
							{{MENU_BACK_TEXT}}
						</h3>
					</md-grid-tile-footer>
				</md-button>
			</md-grid-tile>
		</md-grid-list>
	</div>
</div>