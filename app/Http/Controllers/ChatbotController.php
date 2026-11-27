<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ChatbotUsageService;
use App\Services\GeminiChatService;
use App\Services\InventoryContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function __construct(
        private readonly GeminiChatService $gemini,
        private readonly InventoryContextService $inventoryContext,
        private readonly ChatbotUsageService $usage,
    ) {}

    public function index()
    {
        return view('chatbot.index');
    }

    public function usage(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 200,
            'success' => true,
            'data' => $this->usage->snapshot($request->user()),
        ]);
    }

    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        if (! config('services.gemini.enabled')) {
            return response()->json([
                'status' => 'error',
                'success' => false,
                'data' => [
                    'reply' => 'Chatbot sedang dinonaktifkan.',
                    'usage' => $this->usage->snapshot($request->user()),
                ],
            ], 503);
        }

        $message = trim($validated['message']);

        if ($this->isOffTopic($message)) {
            return $this->reply(GeminiChatService::OFF_TOPIC_REPLY, $request->user());
        }

        try {
            $context = $this->inventoryContext->build($request->user());
            $systemPrompt = GeminiChatService::SYSTEM_PROMPT
                ."\n\nKonteks data website (JSON):\n"
                .json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $this->usage->record($request->user());

            return $this->reply($this->gemini->ask($systemPrompt, $message), $request->user());
        } catch (\Throwable $e) {
            $this->logError($e);

            return response()->json([
                'status' => 200,
                'success' => false,
                'data' => [
                    'reply' => 'Maaf, layanan chatbot sedang tidak tersedia. Silakan coba lagi beberapa saat lagi.',
                    'usage' => $this->usage->snapshot($request->user()),
                ],
            ]);
        }
    }

    private function reply(string $reply, ?User $user): JsonResponse
    {
        return response()->json([
            'status' => 200,
            'success' => true,
            'data' => [
                'reply' => $reply,
                'usage' => $this->usage->snapshot($user),
            ],
        ]);
    }

    private function isOffTopic(string $message): bool
    {
        return (bool) preg_match(
            '/\b(?:cuaca|prakiraan|resep|masak|nasi goreng|olahraga|sepak bola|berita|terjemah|translate|puisi|pantun|lirik|lagu|film|bioskop|politik|pemilu|presiden|matematika|integral|turunan|startup|saham|kripto|crypto|bitcoin)\b/iu',
            $message
        );
    }

    private function logError(\Throwable $e): void
    {
        $key = (string) config('services.gemini.key');
        $message = $key !== ''
            ? str_replace([$key, rawurlencode($key)], '[REDACTED]', $e->getMessage())
            : $e->getMessage();

        Log::error('Chatbot error', [
            'exception' => $e::class,
            'message' => $message,
        ]);
    }
}
