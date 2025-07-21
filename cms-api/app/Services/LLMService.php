<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LLMService
{
    protected $apiKey;
    protected $baseUrl;
    protected $defaultModel;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.api_key');
        $this->baseUrl = config('services.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $this->defaultModel = config('services.openrouter.model', 'gryphe/mythomax-l2-13b');
        
        if (empty($this->apiKey)) {
            throw new \RuntimeException('OpenRouter API key not configured');
        }
    }

    public function generateText(
        string $prompt, 
        int $maxTokens = 150,
        string $model = null,
        float $temperature = 0.7
    ): ?string {
        $model = $model ?: $this->defaultModel;
        $cacheKey = 'openrouter:'.md5($prompt.$model.$maxTokens);

        return Cache::remember($cacheKey, now()->addHours(6), function() use ($prompt, $maxTokens, $model, $temperature) {
            try {
                $response = Http::timeout(30)
                    ->retry(3, 1000)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'HTTP-Referer' => config('app.url'), // Required by OpenRouter
                        'X-Title' => config('app.name'),    // Required by OpenRouter
                    ])->post($this->baseUrl . '/chat/completions', [
                        'model' => $model,
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'max_tokens' => $maxTokens,
                        'temperature' => $temperature,
                    ]);

                if (!$response->successful()) {
                    Log::error('OpenRouter API request failed', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                        'model' => $model
                    ]);
                    return null;
                }

                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;

            } catch (\Exception $e) {
                Log::error('OpenRouter Service error', [
                    'error' => $e->getMessage(),
                    'model' => $model
                ]);
                return null;
            }
        });
    }

    /**
     * Get available models from OpenRouter
     */
    public function getAvailableModels(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/models');

            return $response->successful() 
                ? $response->json()['data'] ?? []
                : [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch OpenRouter models', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get pricing information for models
     */
    public function getPricingInfo(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->get($this->baseUrl . '/auth/key');

            return $response->successful() 
                ? $response->json() 
                : [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch OpenRouter pricing', ['error' => $e->getMessage()]);
            return [];
        }
    }
}