<?php

namespace Sanlilin\LaravelTheme\Listeners;

use Illuminate\Support\Facades\Artisan;
use Sanlilin\LaravelTheme\Support\Theme;

class ThemePublish
{
    public function handle(Theme $theme)
    {
        Artisan::call('theme:publish', ['theme' => $theme->getName()]);
    }
}
