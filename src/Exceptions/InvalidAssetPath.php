<?php

namespace Sanlilin\LaravelTheme\Exceptions;

class InvalidAssetPath extends \Exception
{
    public static function missingThemeName($asset): InvalidAssetPath
    {
        return new static("Theme name was not specified in asset [$asset].");
    }
}
