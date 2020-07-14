<script type="text/ng-template" id="moduleManager_selectModule.tpl">
	<md-dialog aria-label="Select Element">
		<md-toolbar>
			<div class="md-toolbar-tools">
				<h2 translate>Available Elements</h2>
				<span flex></span>
				<md-button class="md-icon-button" data-ng-click="$ctrl.clickToClose()">
					<md-icon aria-label="Close Element Selection" data-fittext>close</md-icon>
				</md-button>
			</div>
		</md-toolbar>
		<md-dialog-content>
			<md-button class="md-raised" data-ng-click='$ctrl.makeModule(module)' data-ng-repeat="module in $ctrl.availableModules" id="{{module.dynaPageModules_moduleId}}">
				{{module.dynaPageModules_name}}
				<md-tooltip md-direction="top" >{{module.dynaPageModules_description}}</md-tooltip>
			</md-button>
			<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="$ctrl.formDisabled">
				<md-button class="md-raised" data-ng-click="$ctrl.clickToClose()" translate>
					Cancel
				</md-button>
				<md-button class="md-raised" data-ng-click="$ctrl.clickToSave()" translate>
					Save
				</md-button>
			</md-dialog-actions>
		</md-dialog-content>
	</md-dialog>
</script>


<script type="text/ng-template" id="moduleManager_pageSettings.tpl">
	<md-dialog aria-label="Sales Screen Options">
		<md-toolbar>
			<div class="md-toolbar-tools">
				<h2 translate>Sales Screen Options</h2>
				<span flex></span>
				<md-button class="md-icon-button" data-ng-click="$ctrl.clickToClose($ctrl.options)">
					<md-icon aria-label="Close Sales Screen Options" data-fittext>close</md-icon>
				</md-button>
			</div>
		</md-toolbar>
		<md-dialog-content>
			<md-input-container class="md-block">
				<label translate>MIN NUMBER OF COLUMNS</label>
				<input type="number" data-ng-model="$ctrl.options.minCols" />
			</md-input-container>
			<md-input-container class="md-block">
				<label translate>MARGIN</label>
				<input type="number" data-ng-model="$ctrl.options.margin" />
			</md-input-container>
			<md-input-container class="md-block">
				<label translate>MIN NUMBER OF ROWS</label>
				<input type="number" data-ng-model="$ctrl.options.minRows" />
			</md-input-container>
			<md-switch class="md-primary" md-no-ink aria-label="ITEM swapping" ng-model="$ctrl.options.swap" ng-true-value="true" ng-false-value="false" translate>
				ITEM swapping
			</md-switch>
			<md-switch class="md-primary" md-no-ink aria-label="PUSH ITEMS" ng-model="$ctrl.options.pushItems" ng-true-value="true" ng-false-value="false" translate>
				PUSH ITEMS
			</md-switch>
			<md-input-container class="md-block">
				<label translate>CUSTOM_CSS</label>
				<textarea ng-model="$ctrl.options.customCss" rows="5" md-select-on-focus></textarea>
			</md-input-container>
			<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="$ctrl.formDisabled">
				<md-button class="md-raised md-primary" data-ng-click="$ctrl.clickToSave($ctrl.options)" translate >
					SAVE
				</md-button>
				<md-button class="md-raised" data-ng-click="$ctrl.clickToClose($ctrl.options)" translate>
					CANCEL
				</md-button>
			</md-dialog-actions>
		</md-dialog-content>
	</md-dialog>
</script>
<script type="text/ng-template" id="moduleManager_addNewScreen.tpl">
	<md-dialog aria-label="NEW SCREEN DETAILS">
		<md-toolbar>
			<div class="md-toolbar-tools">
				<h2 translate>NEW SCREEN DETAILS</h2>
				<span flex></span>
				<md-button class="md-icon-button" data-ng-click="$ctrl.clickToClose()">
					<md-icon aria-label="Close NEW SCREEN DETAILS" data-fittext>close</md-icon>
				</md-button>
			</div>
		</md-toolbar>
		<md-dialog-content>
			<div translate>{{$ctrl.msg}}</div>
			<md-input-container class="md-block">
				<label translate>NAME OF SCREEN</label>
				<input type="text" data-ng-model="$ctrl.screenName" />
			</md-input-container>
			<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="$ctrl.formDisabled">
				<md-button class="md-raised md-primary" data-ng-click="$ctrl.clickToSave()" translate>
					SAVE
				</md-button>
				<md-button class="md-raised" data-ng-click="$ctrl.clickToClose()" translate>
					CANCEL
				</md-button>
			</md-dialog-actions>
		</md-dialog-content>
	</md-dialog>	
</script>

<script type="text/ng-template" id="moduleManager_selectModule_moduleParameters.tpl">
	<md-dialog aria-label="Module Parameters">
		<md-toolbar>
			<div class="md-toolbar-tools">
				<h2 translate>Module Parameters</h2>
				<span flex></span>
				<md-button class="md-icon-button" data-ng-click="$ctrl.clickToClose()">
					<md-icon aria-label="Close Module Parameters" data-fittext>close</md-icon>
				</md-button>
			</div>
		</md-toolbar>
		<md-dialog-content>
			<md-content layout="row" layout-padding>
				<div layout="column">
					<md-input-container class="md-block">
						<label translate>Number of rows</label>
						<input type="number" data-ng-model="$ctrl.newmodule.rows" />
					</md-input-container>
					<md-input-container class="md-block">
						<label translate>Number of columns</label>
						<input type="number" data-ng-model="$ctrl.newmodule.cols" />
					</md-input-container>
				</div>
				<div layout="column">
					<md-input-container class="md-block">
						<label translate>X Position</label>
						<input type="number" data-ng-model="$ctrl.newmodule.x" />
					</md-input-container>
					<md-input-container class="md-block">
						<label translate>Y Position</label>
						<input type="number" data-ng-model="$ctrl.newmodule.y" />
					</md-input-container>
				</div>
			</md-content>
			<md-content layout="row" layout-padding>
				<div>
					<md-input-container class="md-block" data-ng-repeat="parameter in $ctrl.inputParameters">
						<label translate>{{parameter.parameterName}} ({{parameter.parameterType}}) e.g. {{parameter.default}}</label>
						<input type="text" data-ng-model="$ctrl.parametersValue[parameter.parameterName]" />
					</md-input-container>
				</div>
			</md-content>
			<md-dialog-actions layout="row" style="font-size:2.3vw;" data-ng-hide="$ctrl.formDisabled">
				<md-button class="md-raised md-primary" data-ng-click="$ctrl.clickToSave()" translate>
					SAVE
				</md-button>
				<md-button class="md-raised" data-ng-click="$ctrl.clickToClose()" translate>
					CANCEL
				</md-button>
			</md-dialog-actions>
		</md-dialog-content>
	</md-dialog>	
</script>