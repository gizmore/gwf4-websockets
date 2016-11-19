<md-toolbar class="md-theme-indigo"layout-align="right">
	<h1 class="md-toolbar-tools"><?php echo $lang->lang('title_connect'); ?></h1>
	<md-content layout-margin ng-controller="ConnectCtrl" class="gwf-connect-bar">
		<div><?php echo $lang->lang('connection_state'); ?>: {{data.state.text}}</div>
		<section>
			<md-button ng-if="!data.state.bool" ng-click="connect()"><?php echo $lang->lang('btn_connect')?></md-button>
			<md-button ng-if="data.state.bool" ng-click="disconnect()><?php echo $lang->lang('btn_disconnect')?></md-button>
		</section>
	</md-content>
</md-toolbar>
