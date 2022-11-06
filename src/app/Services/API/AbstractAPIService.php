<?php

namespace App\Services\API;

use App\Exceptions\APIException;
use Illuminate\Support\Facades\Http;

abstract class AbstractAPIService
{
    protected function get($url, $query = null)
    {
        $response = Http::get($url, $query);

        if ($response->status() !== 200) {
            $this->handleError('GET', $url, $response->status());
        }

        return $response;
    }

    protected function post($url, $data = [])
    {
        $response = Http::post($url, $data);

        if ($response->status() !== 200) {
            $this->handleError('POST', $url, $response->status());
        }

        return $response;
    }

    protected function put($url, $data = [])
    {
        $response = Http::put($url, $data);

        if ($response->status() !== 200) {
            $this->handleError('PUT', $url, $response->status());
        }

        return $response;
    }

    protected function delete($url, $data = [])
    {
        $response = Http::delete($url, $data);

        if ($response->status() !== 200) {
            $this->handleError('PUT', $url, $response->status());
        }

        return $response;
    }

    protected function handleError($method, $url, $code)
    {
        throw new APIException("Internal $method request to $url failed with code $code.");
    }
}
