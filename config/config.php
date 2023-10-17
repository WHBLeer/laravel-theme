<?php

return [

    'namespace' => 'Themes',

    // 应用市场
    'market' => [
        // 应用市场 api 域名
        'api_base' => 'https://developer.uhaveshop.com/theme',
        // 应用市场默认调用的 client class
        'default' => \Sanlilin\LaravelTheme\Support\Client\Market::class,
    ],

    'stubs' => [
        'enabled' => false,
        'files'   => [
            'routes/web'      => 'Routes/web.php',
            'routes/api'      => 'Routes/api.php',
            'views/index'     => 'Resources/views/index.blade.php',
            'views/master'    => 'Resources/views/layouts/master.blade.php',
            'scaffold/config' => 'Config/config.php',
            'assets/js/app'   => 'Resources/assets/js/app.js',
            'assets/sass/app' => 'Resources/assets/sass/app.scss',
            'webpack'         => 'webpack.mix.js',
            'package'         => 'package.json',
            'gitignore'       => '.gitignore',
        ],
        'replacements' => [
            'routes/web'      => ['LOWER_NAME', 'STUDLY_NAME'],
            'routes/api'      => ['LOWER_NAME'],
            'json'            => ['LOWER_NAME', 'STUDLY_NAME', 'THEME_NAMESPACE', 'PROVIDER_NAMESPACE'],
            'views/index'     => ['LOWER_NAME'],
            'views/master'    => ['LOWER_NAME', 'STUDLY_NAME'],
            'scaffold/config' => ['STUDLY_NAME'],
            'webpack'         => ['LOWER_NAME'],
        ],
        'gitkeep' => true,
    ],
    'paths' => [

        'themes' => base_path('themes'),

        // 资源发布目录
        'assets' => public_path('themes'),

        // 默认应用创建目录结构
        'generator' => [
            'config'     => ['path' => 'Config', 'generate' => true],
            'seeder'     => ['path' => 'Database/Seeders', 'generate' => true],
            'migration'  => ['path' => 'Database/Migrations', 'generate' => true],
            'routes'     => ['path' => 'Routes', 'generate' => true],
            'controller' => ['path' => 'Http/Controllers', 'generate' => true],
            'provider'   => ['path' => 'Providers', 'generate' => true],
            'assets'     => ['path' => 'Resources/assets', 'generate' => true],
            'lang'       => ['path' => 'Resources/lang', 'generate' => true],
            'views'      => ['path' => 'Resources/views', 'generate' => true],
            'model'      => ['path' => 'Models', 'generate' => true],
        ],
    ],
    // 事件监听
    'listen' => [
        // 应用安装以后
        'themes.installed' => [
            \Sanlilin\LaravelTheme\Listeners\ThemePublish::class,
            \Sanlilin\LaravelTheme\Listeners\ThemeMigrate::class,
        ],
        // 应用禁用之前
        'themes.disabling' => [],

        // 应用禁用之后
        'themes.disabled' => [],

        // 应用启用之前
        'themes.enabling' => [],

        // 应用启用之后
        'themes.enabled' => [],

        // 应用删除之前
        'themes.deleting' => [],

        // 应用删除之后
        'themes.deleted' => [],
    ],

    // 自定义命令
    'commands' => [],

    'cache' => [
        'enabled'  => false,
        'key'      => 'laravel-theme',
        'lifetime' => 60,
    ],
    'register' => [
        'translations' => true,
        'files' => 'register',
    ],

    'activators' => [
        'file' => [
            'class'          => \Sanlilin\LaravelTheme\Activators\FileActivator::class,
            'statuses-file'  => base_path('theme_statuses.json'),
            'cache-key'      => 'activator.installed',
            'cache-lifetime' => 604800,
        ],
    ],

    'activator' => 'file',

];
