<?php

namespace Sanlilin\LaravelTheme\Support\Client;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\StreamInterface;
use Sanlilin\LaravelTheme\Contracts\ClientInterface;
use Sanlilin\LaravelTheme\Traits\HasGuzzleClient;

class Market implements ClientInterface
{
    use HasGuzzleClient;

    /**
     * 用户登录.
     *
     * @param  string  $account
     * @param  string  $password
     * @return array
     *
     * @throws GuzzleException
     */
    public function login(string $account, string $password): array
    {
        return $this->httpPostJson('/api/thememarket/login', [
            'email' => $account,
            'password' => $password,
        ]);
    }

    /**
     * 用户注册.
     *
     * @param  string  $account
     * @param  string  $password
     * @param  string  $name
     * @param  string  $passwordConfirmation
     * @return array
     *
     * @throws GuzzleException
     */
    public function register(string $account, string $name, string $password, string $passwordConfirmation): array
    {
        return $this->httpPostJson('/api/thememarket/register', [
            'name' => $name,
            'email' => $account,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ]);
    }

    /**
     * 选择应用版本进行下载.
     *
     * @param  int  $versionId
     * @return StreamInterface
     *
     * @throws GuzzleException
     */
    public function download(int $versionId): StreamInterface
    {
        try {
            return $this->client()->request('POST', ltrim('/api/thememarket/themes/download/'.$versionId, '/'))->getBody();
        } catch (ClientException $e) {
            $response = $e->getResponse();
            if ($message = data_get(json_decode($response->getBody()->getContents(), true), 'message')) {
                throw new \Exception($message, $e->getCode());
            }
            if ($message = $response->getReasonPhrase()) {
                throw new \Exception($message, $e->getCode());
            }
            throw $e;
        }
    }

    /**
     * 用户应用上传.
     *
     * @param  array  $options
     * @return array
     *
     * @throws GuzzleException
     */
    public function upload(array $options): array
    {
        return $this->request('/api/thememarket/themes', 'POST', $options);
    }

    /**
     * 获取应用市场发布的应用.
     *
     * @param  int  $page
     * @return array
     *
     * @throws GuzzleException
     */
    public function themes(int $page): array
    {
        return $this->httpGet('/api/thememarket/themes', [
            'page' => $page,
            'status' => 'release',
        ]);
    }
}
