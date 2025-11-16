<?php
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../vendor/yiisoft/yii2/Yii.php';
require_once __DIR__ . '/../../config/bootstrap.php';

Yii::setAlias('@tests', dirname(__DIR__));

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../../config/main.php'),
    require(__DIR__ . '/../../config/test.php'),
    require(__DIR__ . '/../../config/test-local.php'),
    [
        'id' => 'app-common-tests',
        'basePath' => dirname(dirname(__DIR__)),
    ]
);

$application = new yii\web\Application($config);
unset($config);
