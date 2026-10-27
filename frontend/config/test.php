<?php
return [
    'id' => 'app-frontend-tests',
    'components' => [
        'request' => [
            'scriptFile' => dirname(__DIR__) . '/web/index-test.php',
            'scriptUrl' => '/index-test.php',
        ],
    ],
];
