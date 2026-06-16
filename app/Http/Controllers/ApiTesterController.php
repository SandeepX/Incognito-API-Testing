<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProxyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiTesterController extends Controller
{
    public function index()
    {
        return view('apitester.index');
    }

    public function proxy(ProxyRequest $request)
    {
        $validated = $request->validated();

        $method = strtoupper($validated['method']);
        $url = $validated['url'];

        $http = Http::timeout(30)->withOptions(['verify' => false]);

        $headerArray = [];
        if (!empty($validated['headers'])) {
            foreach ($validated['headers'] as $h) {
                if (!empty($h['key'])) {
                    $headerArray[$h['key']] = $h['value'] ?? '';
                }
            }
        }
        if (!empty($headerArray)) {
            $http->withHeaders($headerArray);
        }

        $startTime = microtime(true);
        $response = null;

        try {
            if ($method === 'GET') {
                $response = $http->get($url);
            } elseif ($method === 'POST') {
                $body = $this->buildBody($validated);
                $response = $http->send('POST', $url, $body);
            } elseif ($method === 'PUT') {
                $body = $this->buildBody($validated);
                $response = $http->send('PUT', $url, $body);
            } elseif ($method === 'PATCH') {
                $body = $this->buildBody($validated);
                $response = $http->send('PATCH', $url, $body);
            } elseif ($method === 'DELETE') {
                $body = $this->buildBody($validated);
                $response = $http->send('DELETE', $url, $body);
            } elseif ($method === 'HEAD') {
                $response = $http->head($url);
            } elseif ($method === 'OPTIONS') {
                $response = $http->send('OPTIONS', $url);
            }

            $elapsed = round((microtime(true) - $startTime) * 1000);

            $responseHeaders = [];
            if ($response) {
                foreach ($response->headers() as $key => $values) {
                    $responseHeaders[$key] = implode(', ', $values);
                }
            }

            return response()->json([
                'status' => $response?->status(),
                'statusText' => $this->statusText($response?->status()),
                'headers' => $responseHeaders,
                'body' => $response?->body(),
                'size' => strlen($response?->body() ?? ''),
                'time' => $elapsed,
            ]);

        } catch (\Throwable $e) {
            $elapsed = round((microtime(true) - $startTime) * 1000);
            return response()->json([
                'error' => $e->getMessage(),
                'time' => $elapsed,
            ], 500);
        }
    }

    private function buildBody(array $validated): array
    {
        $bodyType = $validated['bodyType'] ?? 'none';

        if ($bodyType === 'json' && !empty($validated['body'])) {
            return ['json' => json_decode($validated['body'], true)];
        }

        if ($bodyType === 'form-data' && !empty($validated['formData'])) {
            $form = [];
            foreach ($validated['formData'] as $f) {
                if (!empty($f['key'])) {
                    $form[$f['key']] = $f['value'] ?? '';
                }
            }
            return ['form_params' => $form];
        }

        return [];
    }

    private function statusText(?int $code): string
    {
        $statuses = [
            200 => 'OK', 201 => 'Created', 204 => 'No Content',
            301 => 'Moved Permanently', 302 => 'Found', 304 => 'Not Modified',
            400 => 'Bad Request', 401 => 'Unauthorized', 403 => 'Forbidden',
            404 => 'Not Found', 405 => 'Method Not Allowed', 409 => 'Conflict',
            422 => 'Unprocessable Content', 429 => 'Too Many Requests',
            500 => 'Internal Server Error', 502 => 'Bad Gateway',
            503 => 'Service Unavailable',
        ];
        return $statuses[$code] ?? 'Unknown';
    }
}
