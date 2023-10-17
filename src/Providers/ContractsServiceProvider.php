<?php

namespace Sanlilin\LaravelTheme\Providers;

use Carbon\Laravel\ServiceProvider;
use Sanlilin\LaravelTheme\Contracts\RepositoryInterface;
use Sanlilin\LaravelTheme\Support\Repositories\FileRepository;

class ContractsServiceProvider extends ServiceProvider
{
    /**
     * Register some binding.
     */
    public function register()
    {
        $this->app->bind(RepositoryInterface::class, FileRepository::class);
    }
}
