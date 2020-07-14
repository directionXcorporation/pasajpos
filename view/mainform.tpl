<!DOCTYPE html>
<html>
	<head>
	    
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="Content-Language" content="{{PAGE_LANGCODE}}" />
		<meta name="Author" content="Direction X Corporation" />
		<meta name="Language" content="English" />
		<meta itemprop="name" content="{{PAGE_TITLE}}" />
		<meta property="og:type" content="website" />
		<meta name="Abstract" content="{{PAGE_DESCRIPTION}}" />
		<meta name="application-name" content="PASAJ"/>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta itemprop="description" content="{{PAGE_DESCRIPTION}}" />
		<meta name="Title" content="{{PAGE_TITLE}}" />
		<meta name="keywords" content="{{PAGE_KEYWORDS}}"/>
		<meta name="description" content="{{PAGE_DESCRIPTION}}" />
		<meta name="Generator" content="directionX.ca" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="theme-color" content="#1b4a96"/>
		<link rel="manifest" href="/manifest.json">
		<title translate>
		    {{PAGE_TITLE}}
		</title>

		<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
		
		<style type="text/css" id="cssHeader">
			{{PAGE_CSS_HEADER_HTML}}
		</style>
		
		<!--HEADER HTML:START-->
		{{PAGE_OTHER_HEADER_HTML}}
		<!--HEADER HTML:END-->
		
		<script type="text/javascript" id="jsHeader">
    		(function() {
    		    'use strict';
    			{{PAGE_JS_HEADER_HTML}}
    		})();
		</script>
		
	</head>
	<body style="direction:ltr;" dir="ltr" class="body" data-ng-app="pasaj" layout="column">
		<div data-ng-controller="mainform" layout="column" style="height: 100%;" flex>
			<section layout="row" flex="grow" style="overflow:hidden;">
				<md-sidenav id="mainsidemenuholder" class="md-sidenav-left" md-component-id="mainsidemenuholder" md-is-locked-open="$mdMedia('gt-xs') && sideNavOpen" md-whiteframe="4" md-disable-backdrop md-swipe-left="toggleMainNavBar()" style="width:300px;" ng-cloak>
					<md-toolbar class="md-primary" data-ng-model="parentobj.privatedata.menu" flex="grow" style="overflow: overlay;">
					    <span>
            			    <md-select data-ng-model="preferedLang" placeholder="Select a language" data-ng-change="changeLanguage(preferedLang)" class="language-select">
            			        <md-option ng-value="lang" ng-repeat="lang in {{_AVAILABLE_LANGS}}"><span translate>{{ lang }}</span></md-option>
            			    </md-select>
            			</span>
						<ul class="menu-nested-list" flex="">
							<li data-ng-repeat="(key, node) in parentobj.privatedata.menu | orderBy:'dynaPageMenu_menuOrder'" data-ng-include="'sidemenu.html'" class="parent-list-item" data-ng-attr-md-whiteframe="{{(node.isExpanded && node.nodes && node.nodes.length) ? '9' : '-1' }}" layout="column" flex=""></li>
							<script type="text/ng-template" id="sidemenu.html">
								<md-button class="submenu expanded_{{node.isExpanded}}" data-ng-click="node.isExpanded = !node.isExpanded; parentobj.menuClick(node)" data-ng-class="{'active-menu': (node.isExpanded && node.nodes && node.nodes.length)}" data-ng-if="node.dynaPageMenu_menuName" data-ng-disabled="!appIsOnline && node.dynaPageDetails_availableOffline=='0'" id="sidebarMenuButton_{{node.dynaPageMenu_menuCode}}">
									<div layout="row">
										<div>
											<md-icon aria-label="Show {{node.dynaPageMenu_menuName}}" md-font-icon="{{node.dynaPageMenu_menuIcon}}">{{node.dynaPageMenu_menuIcon}}</md-icon>
										</div>
										<div flex translate>
											{{node.dynaPageMenu_menuName}}
										</div>
										<div>
											<md-icon aria-label="Expand/Collapse" data-ng-if="node.nodes.length" class="fa"
												data-ng-attr-md-font-icon="{{ node.isExpanded ? 'keyboard_arrow_down' : 'keyboard_arrow_right'}}" 
												style="display: inline;">
												{{ node.isExpanded ? 'keyboard_arrow_down' : 'keyboard_arrow_right'}}
											</md-icon>
										</div>
									</div>
								</md-button>
						  		<ul data-ng-if="node.nodes.length && node.isExpanded" class="submenuholder menu-toggle-list" data-ng-model="node.nodes">
									<li data-ng-repeat="(key, node) in node.nodes | orderBy:'dynaPageMenu_menuOrder'" data-ng-include="'sidemenu.html'" class="parent-list-item" layout="column" flex="" data-ng-attr-md-whiteframe="{{(node.isExpanded && node.nodes && node.nodes.length) ? '2' : '-1' }}">
									</li>
								</ul>
							</script>
						</ul>
					</md-toolbar>
					<md-content layout-padding ng-controller="LeftCtrl as $ctrl" hide-gt-xs>
						<md-button ng-click="$ctrl.close()" class="md-primary" translate>
							Close Menu
						</md-button>
					</md-content>
				</md-sidenav>
				<md-content flex layout="column" ng-cloak>
						<div md-swipe-right="openMainNavBar()" flex layout="column">
						    <md-button class='md-icon-button' layout='row' layout-algn='end start' aria-label='Go Fullscreen' data-ng-click='toggleMainNavBar();' style="position:absolute; right: 0px;z-index:9;">
						            <md-icon class='material-icons' style='vertical-align: baseline;'>fullscreen</md-icon>
						    </md-button>
							<div ui-view="main" flex layout="column"></div>
						</div>
						
						<div class="global-msg-container" data-ng-if="parentobj.globalmsg">
							<md-progress-linear md-mode="query" ng-disabled="!parentobj.isloading"></md-progress-linear>
							<div class="bottom-block">
								<span translate>{{parentobj.globalmsg}}</span>
							</div>
						</div> 
				</md-content>
			</section>
		</div>
		
	    <!--FOOTER HTML:START-->
    	{{PAGE_OTHER_FOOTER_HTML}}
    	<!--FOOTER HTML:END-->
    		
	    <!--FOOTER CSS:START-->
		<style type="text/css" id="cssFooter">
			{{PAGE_CSS_FOOTER_HTML}}
		</style>
		<!--FOOTER CSS:END-->
		
		<!--FOOTER JS:START-->
		<script type="text/javascript" id="jsFooter">
    		(function() {
    		    'use strict';
    			{{PAGE_JS_FOOTER_HTML}}		
    		})();
		</script>
		<!--FOOTER JS:END-->
		
	</body>
</html>