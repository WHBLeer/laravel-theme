<?php

namespace Sanlilin\LaravelTheme\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Session;


class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	public function __construct()
	{
	}

	/**
	 * 自定义信息返回
	 * @param     $message
	 * @param int $code
	 * @return JsonResponse
	 *
	 * @author: hongbinwang
	 * @time  : 2023/8/21 17:41
	 */
	public function json($message, int $code=0): JsonResponse
	{
		return response()->json([
			'code' => $code,
			'message' => $message,
		]);
	}

	/**
	 * 自定义错误信息返回
	 * @param $message
	 * @return JsonResponse
	 *
	 * @author: hongbinwang
	 * @time: 2023/8/21 17:41
	 */
	public function jsonError($message): JsonResponse
	{
		Session::flash('json-error', $message);
		$error['errors']['error'] = $message;
		return response()->json($error, 421);
	}

	/**
	 * 自定义成功信息返回
	 * @param $message
	 * @return JsonResponse
	 *
	 * @author: hongbinwang
	 * @time: 2023/8/21 17:41
	 */
	public function jsonSuccess($message): JsonResponse
	{
		Session::flash('json-success', $message);
		return response()->json($message);
	}

	/**
	 * 通用方法：在保存模型时进行异常捕获
	 * @param $model
	 * @return string
	 *
	 * @author: hongbinwang
	 * @time  : 2023/11/10 18:06
	 */
	public function modelSave($model): string
	{
		try {
			return $model->save();
		} catch (\Exception $e) {
			return __('Error saving model:') . $e->getMessage();
		}
	}
}
