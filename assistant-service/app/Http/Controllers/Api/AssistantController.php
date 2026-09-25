<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ConversationResource;
use App\Http\Resources\Api\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AssistantController extends Controller
{
    /**
     * Handle incoming chat message:
     * 1. Save user Message to preserve history
     * 2. Query/cache products catalog from products-service
     * 3. Call AI/generate contextual response
     * 4. Save and return assistant Message
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'user_id' => 'nullable|integer',
            'conversation_id' => 'nullable|exists:conversations,id',
        ]);

        // 1. Get or create Conversation
        if (!empty($validated['conversation_id'])) {
            $conversation = Conversation::findOrFail($validated['conversation_id']);
        } else {
            $conversation = Conversation::create([
                'user_id' => $validated['user_id'] ?? null,
                'title' => Str::limit($validated['message'], 40),
            ]);
        }

        // 2. Persist user message first (so history is never lost even if external call fails)
        $userMessage = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
        ]);

        // 3. Query products catalog with 60-second caching to avoid overloading products-service
        $productsUrl = rtrim(config('services.microservices.products', 'http://localhost:8002'), '/');
        $serviceToken = config('services.internal_token');

        $catalog = Cache::remember('products.catalog', 60, function () use ($productsUrl, $serviceToken) {
            try {
                $response = Http::withHeaders([
                    'X-Service-Token' => $serviceToken,
                    'Accept' => 'application/json',
                ])
                ->timeout(5)
                ->get("{$productsUrl}/api/products");

                return $response->successful() ? ($response->json('data') ?? []) : [];
            } catch (\Throwable $e) {
                return [];
            }
        });

        // 4. Generate AI response
        $assistantReply = $this->generateAssistantResponse($validated['message'], $catalog);

        // 5. Persist assistant reply
        $botMessage = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $assistantReply,
        ]);

        return response()->json([
            'conversation_id' => $conversation->id,
            'user_message' => new MessageResource($userMessage),
            'assistant_message' => new MessageResource($botMessage),
        ]);
    }

    /**
     * Retrieve all conversations for a specific user.
     */
    public function userConversations(string $userId): JsonResponse
    {
        $conversations = Conversation::where('user_id', $userId)
            ->with(['messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->latest()
            ->get();

        return response()->json([
            'data' => ConversationResource::collection($conversations),
        ]);
    }

    /**
     * Display a specific conversation with all its messages.
     */
    public function show(string $id): JsonResponse
    {
        $conversation = Conversation::with('messages')->find($id);

        if (!$conversation) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }

        return response()->json([
            'data' => new ConversationResource($conversation),
        ]);
    }

    /**
     * Generate assistant response using AI or contextual catalog matching.
     */
    protected function generateAssistantResponse(string $userPrompt, array $catalog): string
    {
        $apiKey = config('services.ai_api_key');

        // If an external AI API key is configured and valid, we can forward to LLM
        if (!empty($apiKey) && $apiKey !== 'your_gemini_or_openai_api_key_here') {
            try {
                // OpenAI / compatible LLM endpoint example
                $response = Http::withToken($apiKey)
                    ->timeout(10)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            ['role' => 'system', 'content' => "You are TechHouse AI Assistant, an expert in tech products and electronics. Available products: " . json_encode($catalog)],
                            ['role' => 'user', 'content' => $userPrompt],
                        ],
                    ]);

                if ($response->successful()) {
                    return $response->json('choices.0.message.content');
                }
            } catch (\Throwable $e) {
                // Fallback to catalog matching engine
            }
        }

        // Intelligent catalog matching & recommendations fallback
        $lowerPrompt = strtolower($userPrompt);
        $matches = [];

        foreach ($catalog as $product) {
            $name = strtolower($product['name'] ?? '');
            $desc = strtolower($product['description'] ?? '');
            if (str_contains($lowerPrompt, $name) || str_contains($desc, $lowerPrompt)) {
                $matches[] = $product;
            }
        }

        if (count($matches) > 0) {
            $reply = "¡Hola! Encontré las siguientes opciones ideales para ti en nuestro catálogo de TechHouse:\n\n";
            foreach (array_slice($matches, 0, 3) as $item) {
                $reply .= "• **{$item['name']}** — \${$item['price']} (Stock: {$item['stock']})\n";
                if (!empty($item['description'])) {
                    $reply .= "  _{$item['description']}_\n";
                }
            }
            $reply .= "\n¿Te gustaría agregar alguno a tu orden o necesitas más especificaciones técnicas?";
            return $reply;
        }

        if (count($catalog) > 0) {
            $sample = array_slice($catalog, 0, 3);
            $names = implode(', ', array_column($sample, 'name'));
            return "¡Hola! Soy tu asistente de TechHouse. ¿Buscas algún producto en especial? Tenemos disponibles artículos como {$names} y muchos más. ¿Qué tipo de tecnología estás buscando hoy?";
        }

        return "¡Hola! Soy tu asistente de compras en TechHouse. Estoy aquí para recomendarte los mejores gadgets, componentes y equipos tecnológicos. ¿En qué puedo ayudarte hoy?";
    }
}
