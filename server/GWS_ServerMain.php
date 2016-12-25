<?php
/*
 * This is an example how your index.php could look like
*/
# Security headers

require_once 'module/Websockets/vendor/autoload.php';

# Load config
require_once 'protected/config.php'; # <-- You might need to adjust this path.

# Init GDO and GWF core
require_once 'inc/gwf4.class.php';

# Websockets

GWF_HTML::init();
GWF_Debug::setDieOnError(false);
GWF_Debug::setMailOnError(false);
$_GET['ajax'] = 1;

# Init GWF
$gwf = new GWF4(getcwd(), array(
# Default values
	'init' => true,
	'bootstrap' => true,
	'website_init' => false,
	'autoload_modules' => true,
	'load_module' => false,
	'load_config' => false,
	'start_debug' => true,
	'get_user' => false,
	'do_logging' => true,
	'log_request' => false,
	'blocking' => true,
	'no_session' => true,
	'store_last_url' => true,
	'ignore_user_abort' => false,
));


# Load GWS
if (false === ($gws = GWF_Module::loadModuleDB("Websockets", true, true, true))) {
	die('Module not found.');
}

require_once 'GWS_Global.php';
require_once 'GWS_ServerUtil.php';
require_once $gws->defaultProcessorPath();
require_once $gws->cfgWebsocketProcessorPath();
require_once 'GWS_Server.php';

$processor = $gws->cfgWebsocketProcessorClass();

$server = new GWS_Server();
$server->initGWSServer(new $processor(), $gws);
$server->mainloop($gws->cfgTimerInterval());
