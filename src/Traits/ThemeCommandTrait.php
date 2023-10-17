<?php

namespace Sanlilin\LaravelTheme\Traits;

use Sanlilin\LaravelTheme\Support\Theme;

trait ThemeCommandTrait
{
    protected Theme $theme;

    /**
     * Get the theme name.
     *
     * @return string
     */
    public function getThemeName(): string
    {
        return $this->getTheme()->getStudlyName();
    }

    /**
     * @return Theme
     */
    public function getTheme(): Theme
    {
        if (isset($this->theme) && $this->theme instanceof Theme) {
            return $this->theme;
        }
        $theme = $this->argument('theme') ?: app('themes.repository')->getUsedNow();

        $this->theme = app('themes.repository')->findOrFail($theme);

        return $this->theme;
    }
}
