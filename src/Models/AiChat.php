<?php

namespace Lenorix\LaravelAiChat\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Lenorix\LaravelAiChat\Ai\Actions\GuessAiChatNameAction;
use MalteKuhr\LaravelGPT\Enums\ChatRole;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\FunctionCallRequiresFunctionsException;
use MalteKuhr\LaravelGPT\Exceptions\GPTFunction\MissingFunctionException;
use MalteKuhr\LaravelGPT\Models\ChatMessage;

class AiChat extends Model
{
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(AiChatMessage::class, 'ai_chat_id');
    }

    public function addMessage(ChatMessage|string $message): ?AiChatMessage
    {
        if ($message instanceof ChatMessage) {
            if (empty($message->content)) {
                Log::debug('Invalid message provided.', [
                    'message' => $message,
                ]);

                return null;
            }

            $role = $message->role->value;
            $content = $message->content;
        } else {
            $role = ChatRole::USER->value;
            $content = $message;
        }

        return $this->messages()->create([
            'role' => $role,
            'content' => $content,
        ]);
    }

    public function chatMessages(int $maxLatestMessages = 200): HasMany
    {
        $latestMessages = $this->messages()
            ->latest('created_at')
            ->latest('id')
            ->whereIn('role', [
                ChatRole::USER->value,
                ChatRole::ASSISTANT->value,
            ])
            ->take($maxLatestMessages);

        return $this->messages()
            ->whereIn('id', function ($query) use ($latestMessages) {
                $query->select('id')
                    ->from($latestMessages);
            })
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc');
    }

    /**
     * @throws MissingFunctionException
     * @throws FunctionCallRequiresFunctionsException
     */
    public function guessName(): string
    {
        $name = Cache::get('ai_chat_name_'.$this->id);
        if ($name) {
            return $name;
        }

        $messages = $this->chatMessages()
            ->get()
            ->toArray();

        $messagesJson = json_encode($messages);
        $name = GuessAiChatNameAction::make()
            ->send(json_encode(<<<EOT
                ```json
                $messagesJson
                ```
                EOT,
                JSON_PRETTY_PRINT
            ));

        Cache::put('ai_chat_name_'.$this->id, $name, now()->addMinutes(5));

        return $name;
    }

    public function nameWithFallback(): string
    {
        $name = $this->name;
        if ($name) {
            return $name;
        }

        try {
            return $this->guessName();
        } catch (\Exception $e) {
            return 'Unnamed';
        }
    }
}
