<?php
namespace Sanlilin\LaravelTheme\Http\Controllers;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Storage;
use Sanlilin\LaravelTheme\Support\Json;
use Illuminate\Console\Command as Console;
use Sanlilin\LaravelTheme\Support\Theme;
use Sanlilin\LaravelTheme\Support\Config;
use Sanlilin\LaravelTheme\Support\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Foundation\Application;
use Sanlilin\LaravelTheme\Support\CompressTheme;
use Sanlilin\LaravelTheme\Contracts\ActivatorInterface;
use Sanlilin\LaravelTheme\Support\Composer\ComposerRemove;
use Sanlilin\LaravelTheme\Support\Publishing\AssetPublisher;
use Sanlilin\LaravelTheme\Exceptions\CompressThemeException;
use Sanlilin\LaravelTheme\Support\Generators\LocalInstallGenerator;

class LaravelThemeController extends Controller
{
	/**
	 * artisan theme:list
	 * Show list of all themes.
	 * 显示所有主题的列表。
	 *
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function list(Request $request)
	{
		switch ($request->status) {
			case 'enabled':
				$themes = app('themes.repository')->getByStatus(1);
				break;
			case 'disabled':
				$themes = app('themes.repository')->getByStatus(0);
				break;
			default:
				$themes = app('themes.repository')->all();
				break;
		}
		$status = $request->status??'all';
		$collection = collect();
		/** @var Theme $theme */
		foreach ($themes as $theme) {
			$logo = $theme->getPath().'/Resources/assets'.$theme->get('logo');
			if(!file_exists($logo)){
				$logo_src = asset('assets/theme/'.$theme->getLowerName().'/'.$theme->get('logo'));
			} else {
				$logo_src = default_img();
			}
			$readme = $theme->getPath().'/readme.md';
			if(file_exists($readme)){
				$readme_html = (string) Markdown::parse(file_get_contents($readme));
			} else {
				$readme_html = "<p>{$theme->getDescription()}</p>";
			}
			$item = [
				'name' => $theme->getName(),
				'alias' => $theme->getAlias(),
				'version' => $theme->get('version'),
				'description' => $theme->getDescription(),
				'status' => $theme->isEnabled() ? 'Enabled' : 'Disabled',
				'priority' => $theme->get('priority'),
				'path' => $theme->getPath(),
				'logo' => $logo_src,
				'author' => $theme->get('author'),
				'readme' => $readme_html
			];
			$collection->push($item);
		}
		// 排序和分页操作
		$perPage = $request->per_page ?? 20; // 每页显示的数量
		$page = Paginator::resolveCurrentPage('page'); // 获取当前页码，默认为 'page'
		// 排序
		$sorted = $collection->sortBy('name', SORT_REGULAR, 'desc')->values();
		// 分页
		$sliced = $sorted->slice(($page - 1) * $perPage, $perPage);
		$data = new LengthAwarePaginator(
			$sliced,
			$sorted->count(),
			$perPage,
			$page,
			['path' => Paginator::resolveCurrentPath()]
		);

		$enabled = count(app('themes.repository')->getByStatus(1));
		$disabled = count(app('themes.repository')->getByStatus(0));
		$all = count(app('themes.repository')->all());
		return view('laravel-theme::list',compact('data','status','enabled','disabled','all'));
	}

	/**
	 * 主题市场列表。
	 *
	 * @param Request $request
	 *
	 * @return Application|Factory|View
	 * @throws Exception
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function market(Request $request)
	{
		if (! Config::get('token')) {
			throw new Exception("Please authenticate using the 'login' command before proceeding.");
		}
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
		foreach ($rows as $sn => &$item) {
			$theme = data_get($themes, $sn);
			array_map(fn ($version) => [
				$version['id'],
				$version['version'],
				$version['description'],
				$version['download_times'],
				$version['status_str'],
				$version['price'],
			], data_get($theme, 'versions'));
		}
		return view('laravel-theme::market',compact('rows'));
	}

	/**
	 * 主题配置。
	 *
	 * @param Request $request
	 * @return Application|Factory|View|RedirectResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function setting(Request $request)
	{
		/** @var Theme $theme */
		$theme = app('themes.repository')->findOrFail($request->theme);
		if (!file_exists($theme->getPath().'/config.json')) {
			return back()->with('error', __('The current topic does not have a configuration file'));
		}
		$config = json_decode(file_get_contents($theme->getPath().'/config.json'));
		return view('laravel-theme::setting',compact('theme','config'));
	}

	/**
	 * 保存主题配置。
	 *
	 * @param Request $request
	 * @param         $theme
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function config(Request $request,$theme)
	{
		/** @var Theme $theme */
		$theme = app('themes.repository')->findOrFail($theme);
		$config = $request->config??[];
		file_put_contents($theme->getPath().'/config.json',json_encode($config,JSON_UNESCAPED_UNICODE));
		return $this->jsonSuccess("Theme [{$theme}] config updated successful.");
	}

	/**
	 * artisan theme:disable
	 * Disable the specified theme.
	 * 禁用指定的主题。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function disable(Request $request)
	{
		/** @var Theme $theme */
		$theme = app('themes.repository')->findOrFail($request->theme);

		if ($theme->isEnabled()) {
			$theme->disable();

			$default_theme = app('themes.repository')->first();
			$default_theme->enable();
			return $this->jsonSuccess("Theme [{$theme}] disabled successful. Theme [{$default_theme}] enabled successful.");
		} else {
			return $this->jsonSuccess("Theme [{$theme}] has already disabled.");
		}
	}

	/**
	 * artisan theme:enable
	 * Enable the specified theme.
	 * 启用指定的主题。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function enable(Request $request)
	{
		$themes = app('themes.repository')->all();
		/** @var Theme $theme */
		foreach ($themes as $theme) {
			if ($theme->isEnabled()) {
				$theme->disable();
			}
		}

		/** @var Theme $theme */
		$theme = app('themes.repository')->findOrFail($request->theme);
		$theme->enable();
		return $this->jsonSuccess("Theme [{$theme}] enabled successful.");
	}

	/**
	 * artisan theme:delete
	 * Delete a theme from the application
	 * 从应用程序中删除主题
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function delete(Request $request)
	{
		try {
			/** @var Theme $theme */
			$theme = app('themes.repository')->findOrFail($request->theme);

			ComposerRemove::make()->appendRemoveThemeRequires(
				$theme->getStudlyName(),
				$theme->getAllComposerRequires()
			)->run();

			$theme->delete();

			return $this->jsonSuccess("Theme {$request->theme} has been deleted.");
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		}
	}

	/**
	 * 批量处理主题
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function batch(Request $request)
	{
		dd($request->all());
		try {
			/** @var Theme $theme */
			$theme = app('themes.repository')->findOrFail($request->theme);

			ComposerRemove::make()->appendRemoveThemeRequires(
				$theme->getStudlyName(),
				$theme->getAllComposerRequires()
			)->run();

			$theme->delete();

			return $this->jsonSuccess("Theme {$request->theme} has been deleted.");
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		}
	}

	/**
	 * artisan theme:install
	 * Install the theme through the file directory.
	 * 通过文件目录安装主题。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse|int
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function install(Request $request)
	{
		$path = $request->path;
		try {
			$code = LocalInstallGenerator::make()
				->setLocalPath($path)
				->setFilesystem(app('files'))
				->setThemeRepository(app('themes.repository'))
				->setActivator(app(ActivatorInterface::class))
				->setActive(false)
				->setConsole(new Console())
				->generate();

			return $code;
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		}
	}

	/**
	 * artisan theme:publish
	 * Publish a theme's assets to the application
	 * 将主题的资产发布到应用程序中
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function publish(Request $request)
	{
		$theme = app('themes.repository')->findOrFail($request->theme);
		with(new AssetPublisher($theme))
			->setRepository(app('themes.repository'))
			->setConsole(new Console())
			->publish();

		return $this->jsonSuccess("Theme {$theme->getStudlyName()} published successfully");
	}

	/**
	 * artisan theme:register
	 * register to the theme server.
	 * 注册到主题市场。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function register(Request $request)
	{
		try {
			$name = $request->name;
			$account = $request->account;
			$password = $request->password;
			if (Str::length($password) < 8) {
				return $this->jsonError('The password must be at least 8 characters.');
			}

			$result = app('themes.client')->register(
				$account,
				$name,
				$password,
				$password
			);

			$token = data_get($result, 'token');
			Config::set('token', $token);

			return $this->jsonSuccess('Authenticated successfully.'.PHP_EOL);
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		}
	}

	/**
	 * artisan theme:login
	 * Login to the theme server.
	 * 登录到主题市场。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function login(Request $request)
	{
		try {
			$result = app('themes.client')->login(
				$email = $request->account,
				$password = $request->password
			);
			$token = data_get($result, 'token');
			Config::set('token', $token);

			return $this->jsonSuccess('Authenticated successfully.'.PHP_EOL);
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		}
	}

	/**
	 * artisan theme:upload
	 * Upload the theme to the server.
	 * 将主题上传到主题市场。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @throws CompressThemeException
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function upload(Request $request)
	{
		try {
			if (! Config::get('token')) {
				return $this->jsonError("Please authenticate using the 'login' command before proceeding.");
			}

			/** @var Theme $theme */
			$theme = app('themes.repository')->findOrFail($request->theme);

			Log::info("Theme {$theme->getStudlyName()} starts to compress");

			if (! (new CompressTheme($theme))->handle()) {
				return $this->jsonError("Theme {$theme->getStudlyName()} compression Failed");
			}
			Log::info("Theme {$theme->getStudlyName()} compression completed");

			$compressPath = $theme->getCompressFilePath();

			$stream = fopen($compressPath, 'r+');

			try {
				app('themes.client')->upload([
					'body' => $stream,
					'headers' => ['theme-info' => json_encode($theme->json()->getAttributes(), true)],
					'progress' => 0,
				]);
			} catch (\Exception $exception) {
				return $this->jsonError('Theme upload failed : '.$exception->getMessage());
			}

			app('files')->delete($compressPath);

			if (is_resource($stream)) {
				fclose($stream);
			}
			return $this->jsonSuccess('Theme upload completed');
		} catch (\Mockery\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		}
	}


	/**
	 * Download theme from server to local.
	 * 从主题市场获取主题版本。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function version(Request $request)
	{
		$path = Str::uuid().'.zip';
		try {
			if (! Config::get('token')) {
				return $this->jsonError("Please authenticate using the 'login' command before proceeding.");
			}
			$themes = data_get(app('themes.client')->themes(1), 'data');
			$sn = $request->input_sn;

			if (! $theme = data_get($themes, $sn)) {
				return $this->jsonError(__("The theme number: {$sn} does not exist"));
			}

			array_map(fn ($version) => [
				$version['id'],
				$version['version'],
				$version['description'],
				$version['download_times'],
				$version['status_str'],
				$version['price'],
			], data_get($theme, 'versions'));

			$versionId = $request->input_version_id;

			if (! in_array($versionId, Arr::pluck($theme['versions'], 'id'))) {
				return $this->jsonError(__("The theme version: {$versionId} does not exist"));
			}

			Storage::put($path, app('themes.client')->download($versionId));

			try {
				$code = LocalInstallGenerator::make()
					->setLocalPath(Storage::path($path))
					->setFilesystem(app('files'))
					->setThemeRepository(app('themes.repository'))
					->setActivator(app(ActivatorInterface::class))
					->setActive(false)
					->setConsole(new Console())
					->generate();

				return $this->jsonError(__('Theme downloaded successfully'));
			} catch (\Exception $exception) {
				return $this->jsonError($exception->getMessage());
			}
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		} finally {
			Storage::delete($path);
		}
	}

	/**
	 * artisan theme:download
	 * Download theme from server to local.
	 * 从主题市场下载主题到本地。
	 *
	 * @param Request $request
	 *
	 * @return JsonResponse
	 * @author: hongbinwang
	 * @time  : 2023/10/18 15:23
	 */
	public function download(Request $request)
	{
		$path = Str::uuid().'.zip';
		try {
			if (! Config::get('token')) {
				return $this->jsonError("Please authenticate using the 'login' command before proceeding.");
			}
			$themes = data_get(app('themes.client')->themes(1), 'data');
			$sn = $request->input_sn;

			if (! $theme = data_get($themes, $sn)) {
				return $this->jsonError(__("The theme number: {$sn} does not exist"));
			}

			array_map(fn ($version) => [
				$version['id'],
				$version['version'],
				$version['description'],
				$version['download_times'],
				$version['status_str'],
				$version['price'],
			], data_get($theme, 'versions'));

			$versionId = $request->input_version_id;

			if (! in_array($versionId, Arr::pluck($theme['versions'], 'id'))) {
				return $this->jsonError(__("The theme version: {$versionId} does not exist"));
			}

			Storage::put($path, app('themes.client')->download($versionId));

			try {
				$code = LocalInstallGenerator::make()
					->setLocalPath(Storage::path($path))
					->setFilesystem(app('files'))
					->setThemeRepository(app('themes.repository'))
					->setActivator(app(ActivatorInterface::class))
					->setActive(false)
					->setConsole(new Console())
					->generate();

				return $this->jsonError(__('Theme downloaded successfully'));
			} catch (\Exception $exception) {
				return $this->jsonError($exception->getMessage());
			}
		} catch (\Exception $exception) {
			return $this->jsonError($exception->getMessage());
		} finally {
			Storage::delete($path);
		}
	}
}