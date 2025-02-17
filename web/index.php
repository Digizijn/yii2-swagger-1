<?php

/**
 * This constant defines whether the application should be in debug mode or not. Defaults to false.
 */
defined('YII_DEBUG') || define('YII_DEBUG', getenv('APP_DEBUG') ? getenv('APP_DEBUG') : false);

/**
 * This constant defines in which environment the application is running. Defaults to 'prod', meaning production environment.
 * You may define this constant in the bootstrap script. The value could be 'prod' (production), 'dev' (development), 'test', 'staging', etc.
 */
defined('YII_ENV') or define('YII_ENV', getenv('APP_ENV') ? strtolower(getenv('APP_ENV')) : 'dev');

/**
 * Whether the application is running in production environment
 */
defined('YII_ENV_PROD') or define('YII_ENV_PROD', YII_ENV === 'prod');

/**
 * Whether the application is running in development environment
 */
defined('YII_ENV_DEV') or define('YII_ENV_DEV', YII_ENV === 'dev');

/**
 * Whether the application is running in testing environment
 */
defined('YII_ENV_TEST') or define('YII_ENV_TEST', YII_ENV === 'test');

/**
 * Whether the application should cache shit
 */
defined('YII_CACHE') or define('YII_CACHE', YII_ENV_PROD && !isset($_GET['no-cache']));

/**
 * This constant defines whether error handling should be enabled. Defaults to true.
 */
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', true);


require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

$app = new eo\base\Application($config);
$app->on(yii\web\Application::EVENT_BEFORE_REQUEST, function(yii\base\Event $event){
	$event->sender->response->on(yii\web\Response::EVENT_BEFORE_SEND, function($e){
		ob_start("ob_gzhandler");
	});
	$event->sender->response->on(yii\web\Response::EVENT_AFTER_SEND, function($e){
		ob_end_flush();
	});
});
$app->run();


///**
// * Gets the application start timestamp.
// */
//defined('YII_BEGIN_TIME') or define('YII_BEGIN_TIME', microtime(true));
///**
// * This constant defines the framework installation directory.
// */
//defined('YII2_PATH') or define('YII2_PATH', __DIR__);
