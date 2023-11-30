<?php

if (! function_exists('theme_path')) {
    function theme_path(string $name, string $path = ''): string
    {
        $theme = app('themes.repository')->find($name);

        return $theme->getPath().($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
}

if (! function_exists('theme_native')) {
	function theme_native(string $name): bool
	{
		$theme = app('themes.repository')->find($name);

		return $theme ? true : false;
	}
}

if (! function_exists('theme_enabled')) {
	function theme_enabled(string $name): string
	{
		$theme = app('themes.repository')->find($name);

		return $theme && $theme->isEnabled();
	}
}