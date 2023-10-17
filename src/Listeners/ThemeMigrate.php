<?php

namespace Sanlilin\LaravelTheme\Listeners;

use Illuminate\Support\Facades\Artisan;
use Sanlilin\LaravelTheme\Support\Theme;

class ThemeMigrate
{
    public function handle(Theme $theme)
    {
        Artisan::call('theme:migrate', ['theme' => $theme->getName()]);
    }
}
