<?php

namespace Sanlilin\LaravelTheme\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Sanlilin\LaravelTheme\Providers\MenuServiceProvider;
use Sanlilin\LaravelTheme\Providers\RouteServiceProvider;
use Sanlilin\LaravelTheme\Contracts\ActivatorInterface;
use Sanlilin\LaravelTheme\Contracts\ClientInterface;
use Sanlilin\LaravelTheme\Contracts\RepositoryInterface;
use Sanlilin\LaravelTheme\Exceptions\InvalidActivatorClass;
use Sanlilin\LaravelTheme\Support\Repositories\FileRepository;
use Sanlilin\LaravelTheme\Support\Stub;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Booting the package.
     */
    public function boot()
    {
        $this->registerThemes();
        $this->registerPublishing();
	    $this->registerViews();
	    $this->app->register(MenuServiceProvider::class);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/config.php', 'themes');
        $this->setPsr4();
        $this->registerServices();
        $this->setupStubPath();
        $this->registerProviders();
		$this->registerBlade();
	    $this->app->register(RouteServiceProvider::class);
	    
    }

    /**
     * Register all themes.
     */
    protected function registerThemes(): void
    {
        $this->app->register(BootstrapServiceProvider::class);
    }

    protected function setPsr4(): void
    {
        if (file_exists(base_path('/vendor/autoload.php'))) {
            $loader = require base_path('/vendor/autoload.php');
            $namespace = $this->app['config']->get('themes.namespace');
            $path = $this->app['config']->get('themes.paths.themes');
            $loader->setPsr4("{$namespace}\\", ["{$path}/"]);
        }
    }

    /**
     * Setup stub path.
     */
    public function setupStubPath(): void
    {
        $path = $this->app['config']->get('theme.stubs.path') ?? __DIR__.'/../../stubs';
        Stub::setBasePath($path);

        $this->app->booted(function ($app) {
            /** @var RepositoryInterface $themeRepository */
            $themeRepository = $app[RepositoryInterface::class];
            if ($themeRepository->config('stubs.enabled') === true) {
                Stub::setBasePath($themeRepository->config('stubs.path'));
            }
        });
    }

    protected function registerServices(): void
    {
        $this->app->singleton(RepositoryInterface::class, function ($app) {
            $path = $app['config']->get('themes.paths.themes');

            return new FileRepository($app, $path);
        });
        $this->app->singleton(ActivatorInterface::class, function ($app) {
            $activator = $app['config']->get('themes.activator');
            $class = $app['config']->get('themes.activators.'.$activator)['class'];

            if ($class === null) {
                throw InvalidActivatorClass::missingConfig();
            }

            return new $class($app);
        });
        $this->app->singleton(ClientInterface::class, function ($app) {
            $class = $app['config']->get('themes.market.default');
            if ($class === null) {
                throw InvalidActivatorClass::missingConfig();
            }

            return new $class();
        });
        $this->app->alias(RepositoryInterface::class, 'themes.repository');
        $this->app->alias(ActivatorInterface::class, 'themes.activator');
        $this->app->alias(ClientInterface::class, 'themes.client');
    }

    /**
     * Register providers.
     */
    protected function registerProviders(): void
    {
        $this->app->register(ConsoleServiceProvider::class);
        $this->app->register(ContractsServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

	/**
	 * Register views.
	 *
	 * @return void
	 */
	public function registerViews()
	{
		$sourcePath = __DIR__.'/../../resources/views';
		$this->loadViewsFrom($sourcePath,'laravel-theme');

		if ($this->app->runningInConsole()) {
			$viewPath = resource_path('views/vendor/laravel-theme');

			$this->publishes([
				$sourcePath => $viewPath
			], 'laravel-theme-views');
		}
	}
	
	/**
	 * Register blade.
	 *
	 * @return void
	 */
	public function registerBlade()
	{
		Blade::if('theme', function ($expression) {
			$theme = $this->app['themes.repository']->findOrFail($expression);
			return $theme && $theme->isEnabled();
		});

	}

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [RepositoryInterface::class, 'themes.repository'];
    }

    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/config.php' => config_path('themes.php'),
            ], 'laravel-theme-config');

	        $this->loadJsonTranslationsFrom(__DIR__.'/../../resources/lang');

            $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        }
    }
}
