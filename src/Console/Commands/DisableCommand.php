<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelTheme\Support\Theme;

class DisableCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'theme:disable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Disable the specified theme.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		/** @var Theme $theme */
		$theme = $this->laravel['themes.repository']->findOrFail($this->argument('theme'));

		if ($theme->isEnabled()) {
			$theme->disable();
			$this->info("Theme [{$theme}] disabled successful.");

			$default_theme = $this->laravel['themes.repository']->first();
			$default_theme->enable();
			$this->info("Theme [{$default_theme}] enabled successful.");
		} else {
			$this->comment("Theme [{$theme}] has already disabled.");
		}

		return 0;
	}


	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments(): array
	{
		return [
			['theme', InputArgument::OPTIONAL, 'Theme name.'],
		];
	}
}
