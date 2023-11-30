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
            $this->comment(__('theme_list'));

            $this->table([
                __('serial_number'),
                __('theme_name'),
                __('author'),
                __('download_times'),
            ], $rows);

            $sn = $this->ask(__('input_sn'));

            if (! $theme = data_get($themes, $sn)) {
                throw new \InvalidArgumentException(__('sn_not_exist'));
            }

            $versions = array_map(fn ($version) => [
                $version['id'],
                $version['version'],
                $version['description'],
                $version['download_times'],
                $version['status_str'],
                $version['price'],
            ], data_get($theme, 'versions'));

            $this->comment(__('version_list'));

            $this->table([
                __('id'),
                __('version'),
                __('description'),
                __('download_times'),
                __('status'),
                __('price'),
            ], $versions);

            $versionId = $this->ask(__('input_version_id'));

            if (! in_array($versionId, Arr::pluck($theme['versions'], 'id'))) {
                throw new \InvalidArgumentException(__('version_not_exist'));
            }

            Storage::put($path, app('themes.client')->download($versionId));

            Artisan::call('theme:install', ['path' => Storage::path($path)]);

            $this->info(__('download_successful'));
        } catch (\Exception $exception) {
            $this->error($exception->getMessage());

            return E_ERROR;
        } finally {
            Storage::delete($path);
        }

        return 0;
    }
}
