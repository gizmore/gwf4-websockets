<md-toolbar class="md-theme-indigo" layout-align="right" ng-controller="WSStatsCtrl">

	<h1 class="md-toolbar-tools"><?php echo $lang->lang('title_stats'); ?></h1>
	<md-content layout-margin class="gwf-connect-bar">
		<gwf-status><label>CPU</label><value>{{data.cpu}}</value></gwf-status>
		<gwf-status><label>Users</label><value>{{data.users}}</value></gwf-status>
		<gwf-status><label>Now Memory</label><value>{{data.mem}}</value></gwf-status>
		<gwf-status><label>PeakMemory</label><value>{{data.peak}}</value></gwf-status>
	</md-content>
	
	<gwf-buttons>
		<md-button ng-click="refresh()">Refresh</md-button>
	</gwf-buttons>

</md-toolbar>
