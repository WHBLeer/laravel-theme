<?php

namespace Sanlilin\LaravelTheme\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * Class ThemeStatus.
 *
 * @method static self enable()
 * @method static self disable()
 */
class ThemeStatus extends Enum
{
    protected static function values(): array
    {
        return [
            'disable' => 0,
            'enable' => 1,
        ];
    }
}
