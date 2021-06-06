<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Support\ConfigCache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FixerRequestController
{

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $baseUrl;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('fixer.base_url');
        $this->apiKey = config('fixer.api_key');
    }

    /**
     * @param string $path
     * @param array $params
     * @return array
     * @throws GuzzleException
     */
    public function getData(string $path, array $params = []): array
    {
        $url = sprintf("%s%s?access_key=%s", $this->baseUrl, $path, $this->apiKey);

        foreach ($params as $param => $value) {
            $url .= '&'.urlencode($param).'='.urlencode($value);
        }

        $client = new Client();

        return json_decode($client->get($url)->getBody()->getContents(), true);
    }

    /**
     * Each user should get a from the fixer.io to enable them to have services.
     * To method is called when the user add their api key on the settings page
     *
     */
    public function updateUserApi()
    {
        $result = Settings::findByUserId(Auth::id());

        config()->set('fixer.user_api_key', $result->user_api_key);
        ConfigCache::clear();
    }
}
