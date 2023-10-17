<?php

namespace Sanlilin\LaravelTheme\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sanlilin\LaravelTheme\Traits\HasMarketTokens;
use Sanlilin\LaravelTheme\Traits\ThemeCommandTrait;

class DownLoadCommand extends Command
{
    use ThemeCommandTrait, HasMarketTokens;

    protected $name = 'theme:download';

    protected $description = 'Download theme from server to local.';

    public function handle(): int
    {
        $path = Str::uuid().'.zip';
        try {
            $this->ensure_api_token_is_available();
            $themes = data_get(app('themes.client')->themes(1), 'data');
            $rows = array_reduce($themes, function ($rows, $item) {
                $rows[] = [
                    count($rows),
                    $item['name'],
                    $item['author'],
                    $item['download_times'],
                ];

                return $rows;
            }, []);
            $this->comment(__('themes.theme_list'));

            $this->table([
                __('themes.serial_number'),
                __('themes.name'),
                __('themes.author'),
                __('themes.download_times'),
            ], $rows);

            $sn = $this->ask(__('themes.input_sn'));

            if (! $theme = data_get($themes, $sn)) {
                throw new \InvalidArgumentException(__('themes.sn_not_exist'));
            }

            $versions = array_map(fn ($version) => [
                $version['id'],
                $version['version'],
                $version['description'],
                $version['download_times'],
                $version['status_str'],
                $version['price'],
            ], data_get($theme, 'versions'));

            $this->comment(__('themes.version_list'));

            $this->table([
                __('themes.id'),
                __('themes.version'),
                __('themes.description'),
                __('themes.download_times'),
                __('themes.status'),
                __('themes.price'),
            ], $versions);

            $versionId = $this->ask(__('themes.input_version_id'));

            if (! in_array($versionId, Arr::pluck($theme['versions'], 'id'))) {
                throw new \InvalidArgumentException(__('themes.version_not_exist'));
            }

            Storage::put($path, app('themes.client')->download($versionId));

            Artisan::call('theme:install', ['path' => Storage::path($path)]);

            $this->info(__('themes.download_successful'));
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());

            return E_ERROR;
        } finally {
            Storage::delete($path);
        }

        return 0;
    }
}
