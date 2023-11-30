<?php

if (! function_exists('theme_path')) {
    function theme_path(string $name, string $path = ''): string
    {
        $theme = app('themes.repository')->find($name);

        return $theme->getPath().($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (! function_exists('theme_view')) {
	function theme_view(string $name): bool
	{
		$theme = app('themes.repository')->find($name);

		return $theme ? true : false;
	}
}

if (! function_exists('theme_asset')) {
	function theme_asset(string $name): string
	{
		$theme = app('themes.repository')->find($name);

		return $theme && $theme->isEnabled();
	}
}