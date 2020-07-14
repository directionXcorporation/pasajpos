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
		<meta name="Generator" content="sedesoft.com" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="theme-color" content="#1b4a96"/>
		<title>
		    {{PAGE_TITLE}}
		</title>

		<link rel="shortcut icon" type="image/x-icon" href="/images/icon.jpg" />
		
		<style>
			{{PAGE_CSS_HEADER_HTML}}
		</style>
		
		{{PAGE_OTHER_HEADER_HTML}}
		
		<script>
			{{PAGE_JS_HEADER_HTML}}
		</script>
		
	</head>
	<body style="direction:ltr;" dir="ltr" class="body" data-ng-app="pasaj" layout="column">
		<div data-ng-controller="mainform" layout="column" style="height: 100%;" flex>
			<section layout="row" flex="grow">
				<md-sidenav id="mainsidemenuholder" class="md-sidenav-left" md-component-id="mainsidemenuholder" md-is-locked-open="$mdMedia('gt-xs') && sideNavOpen" md-whiteframe="4" md-disable-backdrop md-swipe-left="toggleMainNavBar()" style="width:300px;" md-theme="{{dynamicTheme}}" md-theme-watch ng-cloak>
					<md-toolbar class="md-primary" data-ng-model="parentobj.privatedata.menu" flex="grow">
						<ul class="menu-nested-list" flex="">
							<li data-ng-repeat="(key, node) in parentobj.privatedata.menu" data-ng-include="'sidemenu.html'" class="parent-list-item" data-ng-attr-md-whiteframe="{{expanded[node.dynaPageMenu_menuCode] ? '9' : '-1' }}" layout="column" flex=""></li>
							<script type="text/ng-template" id="sidemenu.html">
								<md-button class="submenu" data-ng-click="parentobj.menuClick(node)" data-ng-attr-md-colors="{{expanded[node.dynaPageMenu_menuCode] ? '{background: \'blue-grey\'}' : ''}}" data-ng-if="node.dynaPageMenu_menuName" data-ng-disabled="!appIsOnline && node.dynaPageDetails_availableOffline=='0'">
									<div layout="row">
										<div>
											<md-icon aria-label="Show {{node.dynaPageMenu_menuName}}" md-font-icon="{{node.dynaPageMenu_menuIcon}}">{{node.dynaPageMenu_menuIcon}}</md-icon>
										</div>
										<div flex>
											{{node.dynaPageMenu_menuName}}
										</div>
										<div>
											<md-icon aria-label="Expand/Collapse" data-ng-if="parentobj.checkLength(node.nodes)" class="fa"
												data-ng-attr-md-font-icon="{{ expanded[node.dynaPageMenu_menuName] ? 'keyboard_arrow_down' : 'keyboard_arrow_right'}}" 
												style="display: inline;">
												{{ expanded[node.dynaPageMenu_menuCode] ? 'keyboard_arrow_down' : 'keyboard_arrow_right'}}
											</md-icon>
										</div>
									</div>
								</md-button>
						  		<ul data-ng-if="parentobj.checkLength(node.nodes) && expanded[node.dynaPageMenu_menuCode]" class="submenuholder menu-toggle-list" data-ng-model="node.nodes">
									<li data-ng-repeat="(key, node) in node.nodes" data-ng-include="'sidemenu.html'" class="parent-list-item" layout="column" flex="" data-ng-attr-md-whiteframe="{{expanded[node.dynaPageMenu_menuCode] ? '2' : '-1' }}">
									</li>
								</ul>
							</script>
						</ul>
					</md-toolbar>
					<md-content layout-padding ng-controller="LeftCtrl" hide-gt-md>
						<md-button ng-click="close()" class="md-primary">
							{{Close Menu}}
						</md-button>
					</md-content>
				</md-sidenav>
				<md-content flex layout="column" ng-cloak>
						<md-progress-linear md-mode="query" data-ng-disabled="!parentobj.pageIsLoading"></md-progress-linear>
						<div md-swipe-right="openMainNavBar()" flex layout="column">
						    <md-button class='md-icon-button' layout='row' layout-algn='end start' aria-label='Make Sales Screen Fullscreen' data-ng-click='toggleMainNavBar();' style="position:absolute; right: 0px;z-index:9;">
						            <md-icon class='material-icons' style='vertical-align: baseline;'>fullscreen</md-icon>
						    </md-button>
							<div ui-view="main" flex layout="column"></div>
						</div>
						
						<div class="global-msg-container" data-ng-if="parentobj.globalmsg">
							<md-progress-linear md-mode="query" ng-disabled="!parentobj.isloading"></md-progress-linear>
							<div class="bottom-block">
								<span>>&nbsp;{{parentobj.globalmsg}}</span>
							</div>
						</div> 
				</md-content>
			</section>
		</div>
	    
		<style type="text/css">
			{{PAGE_CSS_FOOTER_HTML}}
		</style>
		
		<script type="text/javascript">
			{{PAGE_JS_FOOTER_HTML}}		
		</script>
		
		{{PAGE_OTHER_FOOTER_HTML}}
		
	</body>
</html>