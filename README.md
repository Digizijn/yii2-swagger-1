Yii2 Swagger Extension
=================
Yii2 extension for API Documentation generation using Swagger http://swagger.io

Installation
------------

1. Add package repository to composer.json:
```
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/mobidev-php/yii2-swagger.git"
        }
    ],
```
2. Run command:
```
composer require "mobidev/yii2-swagger" "dev-master"
```

Usage
-----
1. Add module settings to console config:
```php
return [
    'bootstrap' => ['gii', 'swagger'],
    'modules' => [
        'gii' => 'yii\gii\Module',
        'swagger' => [
            'class' => 'mobidev\swagger\Module',
            'jsonPath' => '@api/web/swagger-dev.json',
            'host' => 'api.192.168.33.68.xip.io',
            'basePath' => '/v1',
            'description' => 'My Project API documentation (swagger-2.0 specification)',
            'defaultInput' => 'body',
        ],
    ],
];
```
2. Run command for generating json document:
```
./yii swagger/generate/json
```