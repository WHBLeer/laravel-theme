<?php

namespace Sanlilin\LaravelTheme\Contracts;

interface RunableInterface
{
    /**
     * Run the specified command.
     *
     * @param  string  $command
     */
    public function run(string $command);
}
