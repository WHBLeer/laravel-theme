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


mv ./src/Traits/PluginCommandTrait.php ./src/Traits/ThemeCommandTrait.php
mv ./resources/lang/zh-CN/plugins.php ./resources/lang/zh-CN/themes.php
mv ./resources/lang/en/plugins.php ./resources/lang/en/themes.php

