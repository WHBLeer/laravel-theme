<?php

namespace Sanlilin\LaravelTheme\Support\Config;

class GenerateConfigReader
{
    public static function read(string $value): GeneratorPath
    {
        return new GeneratorPath(config("themes.paths.generator.$value"));
    }
}
