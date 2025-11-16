<?php
return yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../config/main.php'),
    require(__DIR__ . '/../config/test.php'),
    require(__DIR__ . '/../config/test-local.php'),
    [
        'id' => 'app-common-tests',
        'basePath' => dirname(__DIR__),
    ]
);
