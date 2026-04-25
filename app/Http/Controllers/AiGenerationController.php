<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiGenerationController extends Controller
{
    public function promo(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'productName' => ['required', 'string', 'max:120'],
        ]);

        $productName = trim($payload['productName']);

        $textPrompt = sprintf(
            'Write a short, catchy, and persuasive promotional caption for a small business product called: "%s". Include 2-3 relevant emojis. Keep it concise, 3 sentences maximum.',
            $productName,
        );

        $imagePrompt = sprintf(
            'A 3D minimalist render of %s, product photography style, clean pure white background, soft studio lighting, highly detailed, modern and appealing style.',
            $productName,
        );

        return response()->json([
            'text' => $this->generateText($textPrompt),
            'image' => $this->generateImage($imagePrompt),
        ]);
    }

    public function productAssets(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'productName' => ['required', 'string', 'max:120'],
            'variation' => ['nullable', 'string', 'max:120'],
        ]);

        $productName = trim($payload['productName']);
        $variation = trim($payload['variation'] ?? 'product photography style');

        $descriptionPrompt = sprintf(
            'Write a very short (1-2 sentences) and appealing product description for a small business product called: "%s". Include 1 relevant emoji.',
            $productName,
        );

        $imagePrompt = sprintf(
            'A 3D minimalist render of %s, %s, clean pure white background, soft studio lighting, highly detailed, modern and appealing style.',
            $productName,
            $variation,
        );

        return response()->json([
            'description' => $this->generateText($descriptionPrompt),
            'image' => $this->generateImage($imagePrompt),
        ]);
    }

    protected function generateText(string $prompt): string
    {
        $response = Http::withToken(config('services.pollinations.key'))
            ->acceptJson()
            ->timeout(30)
            ->post('https://gen.pollinations.ai/v1/chat/completions', [
                'model' => config('services.pollinations.text_model'),
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        return data_get($this->handleJsonFailure($response), 'choices.0.message.content', '');
    }

    protected function generateImage(string $prompt): ?string
    {
        $response = Http::withToken(config('services.pollinations.key'))
            ->timeout(45)
            ->get('https://gen.pollinations.ai/image/'.rawurlencode($prompt), [
                'model' => config('services.pollinations.image_model'),
                'width' => config('services.pollinations.width'),
                'height' => config('services.pollinations.height'),
                'seed' => 0,
                'enhance' => 'false',
            ]);

        if (! $response->successful()) {
            return null;
        }

        $contentType = $response->header('Content-Type', 'image/jpeg');

        return sprintf('data:%s;base64,%s', $contentType, base64_encode($response->body()));
    }

    protected function handleJsonFailure(Response $response): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        abort(response()->json([
            'message' => data_get($response->json(), 'error.message', 'AI provider request failed.'),
        ], $response->status()));
    }
}