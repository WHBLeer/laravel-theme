<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Console\Command;
use Sanlilin\LaravelTheme\Support\Config;

class LoginCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'theme:login';

    /**
     * @var string
     */
    protected $description = 'Login to the theme server.';

    public function handle(): int
    {
        try {
            $result = app('themes.client')->login(
                $email = $this->ask('Email'),
                $password = $this->secret('Password')
            );
            $this->store(data_get($result, 'token'));

            return 0;
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());

            return E_ERROR;
        }
    }

    protected function store(string $token): void
    {
        Config::set('token', $token);

        $this->info('Authenticated successfully.'.PHP_EOL);
    }
}
