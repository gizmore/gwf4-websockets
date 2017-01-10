<md-toolbar class="md-theme-indigo" layout-align="right" ng-controller="WSStatsCtrl">

	<h1 class="md-toolbar-tools"><?php echo $lang->lang('title_stats'); ?></h1>
	<md-content layout-margin class="gwf-connect-bar">
		<gwf-status><label>Now Memory</label><value>{{data.stats.memory}}</value></gwf-status>
		<gwf-status><label>PeakMemory</label><value>{{data.stats.peak}}</value></gwf-status>
		<gwf-status><label>Users</label><value>{{data.stats.users}}</value></gwf-status>
	</md-content>
	
	<gwf-buttons>
		<md-button ng-click="refresh()">Refresh</md-button>
	</gwf-buttons>

</md-toolbar>
