<style>
	div.generaltable md-input-container{
		margin: 0px;
		padding:0px;
		height: 100%;
	}
	div.generaltable md-icon{
		cursor: pointer;
		color: grey;
	}
</style>
<div data-ng-if="$ctrl.tableIsLoading" translate>Loading Table...</div>
<div data-ng-hide="$ctrl.tableIsLoading" class="data-grid generaltable" ui-grid="$ctrl.gridoptions" ui-grid-auto-resize ui-grid-edit></div>