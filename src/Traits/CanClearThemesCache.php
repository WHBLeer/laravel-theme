<?php

namespace Sanlilin\LaravelTheme\Traits;

trait CanClearThemesCache
{
    /**
     * Clear the themes cache if it is enabled.
     */
    public function clearCache(): void
    {
        if (config('themes.cache.enabled') === true) {
            app('cache')->forget(config('themes.cache.key'));
        }
    }
}
