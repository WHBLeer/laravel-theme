<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Sanlilin\LaravelTheme\Support\Theme;

class ListCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'theme:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show list of all themes.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->table([__('theme_name'), __('status'), __('priority'), __('path')], $this->getRows());

        return 0;
    }

    /**
     * Get table rows.
     *
     * @return array
     */
    public function getRows()
    {
        $rows = [];

        /** @var Theme $theme */
        foreach ($this->getThemes() as $theme) {
            $rows[] = [
                $theme->getName(),
                $theme->isEnabled() ? 'Enabled' : 'Disabled',
                $theme->get('priority'),
                $theme->getPath(),
            ];
        }

        return $rows;
    }

    public function getThemes()
    {
        switch ($this->option('only')) {
            case 'enabled':
                return $this->laravel['themes.repository']->getByStatus(1);
                break;

            case 'disabled':
                return $this->laravel['themes.repository']->getByStatus(0);
                break;

            case 'priority':
                return $this->laravel['themes.repository']->getPriority($this->option('direction'));
                break;

            default:
                return $this->laravel['themes.repository']->all();
                break;
        }
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['only', 'o', InputOption::VALUE_OPTIONAL, 'Types of themes will be displayed.', null],
            ['direction', 'd', InputOption::VALUE_OPTIONAL, 'The direction of ordering.', 'asc'],
        ];
    }
}
