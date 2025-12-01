<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIController extends Controller
{
    protected $geminiApiKey;
    protected $defaultModel;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.api_key');
        $this->defaultModel = config('services.gemini.model', 'gemini-2.5-flash');
    }

    /**
     * Get Gemini API endpoint for a specific model
     */
    protected function getEndpoint(string $model): string
    {
        return "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
    }

    /**
     * Generate AI content based on action type
     */
    public function generate(Request $request)
    {
        $request->validate([
            'action' => 'required|in:write,continue,improve,summarize,translate,expand',
            'content' => 'required|string',
            'model' => 'nullable|string',
            'maxTokens' => 'nullable|integer|min:100|max:8192'
        ]);

        // Check API key first
        if (empty($this->geminiApiKey)) {
            return response()->json([
                'success' => false,
                'error' => 'GEMINI_API_KEY belum dikonfigurasi di file .env'
            ], 400);
        }

        $action = $request->input('action');
        $content = $request->input('content');
        $model = $request->input('model', $this->defaultModel);
        $maxTokens = $request->input('maxTokens', 1024);

        try {
            $prompt = $this->buildPrompt($action, $content);
            $result = $this->callGemini($prompt, $model, $maxTokens);

            return response()->json([
                'success' => true,
                'result' => $result,
                'action' => $action,
                'model' => $model
            ]);

        } catch (\Exception $e) {
            Log::error('AI Generation Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available models
     */
    public function models()
    {
        return response()->json([
            'models' => config('services.gemini.models'),
            'default' => $this->defaultModel
        ]);
    }

    /**
     * Build prompt based on action type
     */
    protected function buildPrompt(string $action, string $content): string
    {
        $prompts = [
            'write' => "Tulis konten profesional dalam Bahasa Indonesia tentang: {$content}. 
                        Gunakan format yang rapi dengan paragraf yang terstruktur. 
                        Hasil harus dalam format HTML yang valid dengan tag <p>, <h2>, <h3>, <ul>, <li> jika diperlukan.",
            
            'continue' => "Berikut adalah dokumen yang sedang ditulis:\n\n{$content}\n\n
                          Lanjutkan penulisan dokumen ini secara natural dan konsisten dengan gaya penulisan yang ada.
                          Tambahkan 2-3 paragraf lagi yang relevan. Hasil dalam format HTML.",
            
            'improve' => "Perbaiki dan tingkatkan kualitas teks berikut agar lebih profesional, jelas, dan mudah dibaca:\n\n{$content}\n\n
                         Pertahankan makna aslinya tetapi perbaiki grammar, struktur kalimat, dan pilihan kata.
                         Hasil dalam format HTML yang rapi.",
            
            'summarize' => "Buatkan ringkasan yang padat dan informatif dari dokumen berikut:\n\n{$content}\n\n
                          Ringkasan harus mencakup poin-poin utama dalam 2-3 paragraf.
                          Hasil dalam format HTML.",
            
            'translate' => "Terjemahkan teks berikut ke Bahasa Indonesia yang baik dan benar:\n\n{$content}\n\n
                          Pertahankan format dan nuansa aslinya. Hasil dalam format HTML.",
            
            'expand' => "Perluas dan kembangkan teks berikut dengan menambahkan detail, contoh, dan penjelasan yang relevan:\n\n{$content}\n\n
                        Buat teks menjadi lebih komprehensif dan informatif sambil mempertahankan konteks asli.
                        Hasil dalam format HTML yang terstruktur."
        ];

        return $prompts[$action] ?? $prompts['write'];
    }

    /**
     * Call Gemini API
     */
    protected function callGemini(string $prompt, string $model = null, int $maxTokens = 1024): string
    {
        if (empty($this->geminiApiKey)) {
            throw new \Exception('Gemini API key belum dikonfigurasi. Tambahkan GEMINI_API_KEY ke file .env');
        }

        $model = $model ?? $this->defaultModel;
        $endpoint = $this->getEndpoint($model);

        $response = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(60)->post("{$endpoint}?key={$this->geminiApiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => $maxTokens,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ]);

        if (!$response->successful()) {
            Log::error('Gemini API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            throw new \Exception('Gemini API error: ' . $response->status());
        }

        $data = $response->json();
        
        // Extract text from response
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Clean up the response (remove markdown code blocks if present)
        $text = preg_replace('/```html?\n?/', '', $text);
        $text = preg_replace('/```\n?/', '', $text);
        
        return trim($text);
    }

    /**
     * Check AI service status
     */
    public function status()
    {
        return response()->json([
            'configured' => !empty($this->geminiApiKey),
            'model' => 'gemini-1.5-flash',
            'provider' => 'Google AI'
        ]);
    }
}
