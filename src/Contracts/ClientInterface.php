<?php

namespace Sanlilin\LaravelTheme\Contracts;

use Psr\Http\Message\StreamInterface;

interface ClientInterface
{
    /**
     * 用户登录.
     *
     * @param  string  $account
     * @param  string  $password
     * @return array
     */
    public function login(string $account, string $password): array;

    /**
     * 用户注册.
     *
     * @param  string  $account
     * @param  string  $password
     * @param  string  $name
     * @param  string  $passwordConfirmation
     * @return array
     */
    public function register(string $account, string $name, string $password, string $passwordConfirmation): array;

    /**
     * 选择应用版本进行下载.
     *
     * @param  int  $versionId
     * @return StreamInterface
     */
    public function download(int $versionId): StreamInterface;

    /**
     * 用户应用上传.
     *
     * @param  array  $options
     * @return array
     */
    public function upload(array $options): array;

    /**
     * 获取应用市场发布的应用.
     *
     * @param  int  $page
     * @return array
     */
    public function themes(int $page): array;
}
