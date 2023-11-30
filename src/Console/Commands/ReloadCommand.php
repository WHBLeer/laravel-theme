<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Sanlilin\LaravelTheme\Support\Theme;

class ReloadCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'theme:reload';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Reload the specified theme.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		/**
		 * check if user entred an argument.
		 */
		if ($this->argument('theme') === null) {
			$this->reloadAll();
			return 0;
		}

		/** @var Theme $theme */
		$theme = $this->laravel['themes.repository']->findOrFail($this->argument('theme'));
		if ($theme->isEnabled()) $theme->disable();

		$theme->enable();
		$this->info("Theme [{$theme}] reload successful.");

		return 0;
	}

	/**
	 * reloadAll.
	 *
	 * @return void
	 */
	public function reloadAll(): void
	{
		$themes = $this->laravel['themes.repository']->all();
		/** @var Theme $theme */
		foreach ($themes as $theme) {
			if ($theme->isEnabled()) $theme->disable();

			$theme->enable();
			$this->info("Theme [{$theme}] reload successful.");
		}
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
