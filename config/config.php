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

	'menusshow' => false,
	'stubs' => [
		'enabled' => false,
		'files'   => [
			//路由
			'routes/web'      => 'Routes/web.php',
			'routes/api'      => 'Routes/api.php',
			//模板
			'views/index'           => 'Resources/views/index.blade.php',
			'views/giveaway'        => 'Resources/views/giveaway.blade.php',
			'views/wishlist'        => 'Resources/views/wishlist.blade.php',
			'views/cart'            => 'Resources/views/cart.blade.php',
			'views/page'            => 'Resources/views/page.blade.php',
			'views/thanks'          => 'Resources/views/thanks.blade.php',
			'views/shop'            => 'Resources/views/shop.blade.php',
			'views/checkout'        => 'Resources/views/checkout.blade.php',
			'views/details'         => 'Resources/views/details.blade.php',
			'views/layouts/auth'    => 'Resources/views/layouts/auth.blade.php',
			'views/layouts/header'  => 'Resources/views/layouts/header.blade.php',
			'views/layouts/app'     => 'Resources/views/layouts/app.blade.php',
			'views/layouts/footer'  => 'Resources/views/layouts/footer.blade.php',
			//静态资源
			'assets/js/index'   => 'Resources/assets/js/index.js',
			'assets/js/jquery-3.5.1.min'   => 'Resources/assets/js/jquery-3.5.1.min.js',
			'assets/css/index' => 'Resources/assets/css/index.scss',
			'assets/logo'     => 'Resources/assets/logo.png',
			'assets/lang'     => 'Resources/lang/en.json',
			'scaffold/config' => 'Config/config.php',
			'scaffold/helper' => 'Support/helper.php',
			'readme'          => 'readme.md',
			'gitignore'       => '.gitignore',
		],
		'replacements' => [
			'routes/web'      => ['LOWER_NAME', 'STUDLY_NAME'],
			'routes/api'      => ['LOWER_NAME'],
			'json'            => ['LOWER_NAME', 'STUDLY_NAME', 'THEME_NAMESPACE', 'PROVIDER_NAMESPACE'],
			'readme'          => ['LOWER_NAME', 'STUDLY_NAME', 'THEME_NAMESPACE', 'PROVIDER_NAMESPACE'],
			'assets/lang'     => ['LOWER_NAME', 'STUDLY_NAME', 'THEME_NAMESPACE', 'PROVIDER_NAMESPACE'],
			'views/index'     => ['LOWER_NAME'],
			'views/master'    => ['LOWER_NAME', 'STUDLY_NAME'],
			'scaffold/config' => ['LOWER_NAME', 'STUDLY_NAME'],
			'scaffold/helper' => ['STUDLY_NAME'],
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
			'events'     => ['path' => 'Events', 'generate' => true],
			'controller' => ['path' => 'Http/Controllers', 'generate' => true],
			'model'      => ['path' => 'Models', 'generate' => true],
			'provider'   => ['path' => 'Providers', 'generate' => true],
			'assets'     => ['path' => 'Resources/assets', 'generate' => true],
			'lang'       => ['path' => 'Resources/lang', 'generate' => true],
			'views'      => ['path' => 'Resources/views', 'generate' => true],
			'routes'     => ['path' => 'Routes', 'generate' => true],
			'support'    => ['path' => 'Support', 'generate' => true],
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
