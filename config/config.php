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
			'views/marketing'       => 'Resources/views/marketing.blade.php',
			'views/checkout'        => 'Resources/views/checkout.blade.php',
			'views/details'         => 'Resources/views/details.blade.php',
			'views/layouts/auth'    => 'Resources/views/layouts/auth.blade.php',
			'views/layouts/header'  => 'Resources/views/layouts/header.blade.php',
			'views/layouts/app'     => 'Resources/views/layouts/app.blade.php',
			'views/layouts/footer'  => 'Resources/views/layouts/footer.blade.php',
			'views/components/product'      => 'Resources/views/components/product.blade.php',
			'views/components/image_zoom'   => 'Resources/views/components/image_zoom.blade.php',
			'views/components/pagination'   => 'Resources/views/components/pagination.blade.php',
			'views/components/slider/ads'       => 'Resources/views/components/slider/ads.blade.php',
			'views/components/slider/banner'    => 'Resources/views/components/slider/banner.blade.php',
			'views/components/slider/offer'     => 'Resources/views/components/slider/offer.blade.php',
			'views/components/menu/menu'            => 'Resources/views/components/menu/menu.blade.php',
			'views/components/menu/header_parent'   => 'Resources/views/components/menu/header_parent.blade.php',
			'views/components/menu/header_child'    => 'Resources/views/components/menu/header_child.blade.php',
			'views/components/menu/footer_parent'   => 'Resources/views/components/menu/footer_parent.blade.php',
			'views/components/menu/footer_child'    => 'Resources/views/components/menu/footer_child.blade.php',
			//静态资源
			'assets/js/app'   => 'Resources/assets/js/app.js',
			'assets/js/nicescroll.min'   => 'Resources/assets/js/nicescroll.min.js',
			'assets/js/jquery.unveil'   => 'Resources/assets/js/jquery.unveil.js',
			'assets/js/index'   => 'Resources/assets/js/index.js',
			'assets/js/jquery-3.5.1.min'   => 'Resources/assets/js/jquery-3.5.1.min.js',
			'assets/js/photoswipe-ui-default'   => 'Resources/assets/js/photoswipe-ui-default.js',
			'assets/js/stripe'   => 'Resources/assets/js/stripe.js',
			'assets/js/main'   => 'Resources/assets/js/main.js',
			'assets/js/bootstrap.min'   => 'Resources/assets/js/bootstrap.min.js',
			'assets/js/ajax'   => 'Resources/assets/js/ajax.js',
			'assets/js/swp'   => 'Resources/assets/js/swp.js',
			'assets/js/shop'   => 'Resources/assets/js/shop.js',
			'assets/js/video'   => 'Resources/assets/js/video.js',
			'assets/js/common'   => 'Resources/assets/js/common.js',
			'assets/js/details'   => 'Resources/assets/js/details.js',
			'assets/js/slider'   => 'Resources/assets/js/slider.js',
			'assets/js/popper.min'   => 'Resources/assets/js/popper.min.js',
			'assets/js/jquery-ui'   => 'Resources/assets/js/jquery-ui.js',
			'assets/js/slick.min'   => 'Resources/assets/js/slick.min.js',
			'assets/js/imagezoom'   => 'Resources/assets/js/imagezoom.js',
			'assets/js/photoswipe'   => 'Resources/assets/js/photoswipe.js',
			'assets/js/checkout'   => 'Resources/assets/js/checkout.js',
			'assets/sass/app' => 'Resources/assets/sass/app.scss',
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
