<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\AssistantKnowledgePolicy;
use Illuminate\Support\Facades\Http;

class AiAssistantService
{
    public function __construct(
        private AssistantContextService $context,
        private AssistantKnowledgePolicy $policy,
    ) {}

    public function isEnabled(): bool
    {
        $config = Setting::getAi();

        return (bool) ($config['enabled'] ?? false)
            && filled($config['base_url'] ?? '')
            && filled($config['api_key'] ?? '');
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{ok: bool, message: string, reply?: string, blocked?: bool, reason?: string}
     */
    public function chat(array $messages): array
    {
        if (! $this->isEnabled()) {
            return ['ok' => false, 'message' => 'AI assistant is not configured. Set it up in Settings → Integrations.'];
        }

        $messages = $this->policy->sanitizeMessages($messages);

        if ($messages === []) {
            return ['ok' => false, 'message' => AssistantKnowledgePolicy::REFUSAL_NO_CONTEXT, 'blocked' => true, 'reason' => 'empty'];
        }

        $query = $this->policy->extractSearchQuery($messages);
        $built = $this->context->build($query);
        $evaluation = $this->policy->evaluateRequest($query, $built['results']);

        if (! $evaluation['allowed']) {
            return [
                'ok' => true,
                'message' => 'OK',
                'reply' => $evaluation['message'],
                'blocked' => true,
                'reason' => $evaluation['reason'],
            ];
        }

        $config = Setting::getAi();
        $system = $this->policy->buildSystemPrompt(
            trim((string) ($config['system_prompt'] ?? '')),
            $built['context'],
        );

        $payload = [
            ['role' => 'system', 'content' => $system],
            ...$this->conversationForModel($messages),
        ];

        try {
            $response = Http::timeout(60)
                ->withToken($config['api_key'])
                ->post(rtrim($config['base_url'], '/').'/chat/completions', [
                    'model' => $config['model'] ?? 'gpt-4o-mini',
                    'messages' => $payload,
                    'temperature' => 0.1,
                ]);

            if (! $response->successful()) {
                return ['ok' => false, 'message' => 'AI request failed: '.$response->body()];
            }

            $reply = $response->json('choices.0.message.content');

            if (! is_string($reply) || trim($reply) === '') {
                return ['ok' => false, 'message' => 'Empty response from AI provider.'];
            }

            return ['ok' => true, 'message' => 'OK', 'reply' => trim($reply)];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Only send recent turns so older chat cannot widen scope or inject instructions.
     *
     * @param  list<array{role: string, content: string}>  $messages
     * @return list<array{role: string, content: string}>
     */
    private function conversationForModel(array $messages): array
    {
        return collect($messages)->slice(-4)->values()->all();
    }
}
