<?php
return [
    'id' => 'app-backend-tests',
    'components' => [
        'request' => [
            'scriptFile' => dirname(__DIR__) . '/web/index-test.php',
            'scriptUrl' => '/index-test.php',
        ],
    ],
];
