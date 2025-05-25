<?php

namespace Lenorix\LaravelAiChat\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Lenorix\Ai\Chat\CoreMessage;
use Lenorix\Ai\Chat\CoreMessageRole;
use Lenorix\LaravelAiChat\Ai\Actions\GuessAiChatNameAction;

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

    public function addMessage(CoreMessage|string $message): ?AiChatMessage
    {
        if ($message instanceof CoreMessage) {
            if (empty($message->content) && empty($message->toolCalls)) {
                Log::debug('Invalid message provided.', [
                    'message' => $message,
                ]);

                return null;
            }

            $role = $message->role->value;
            $content = $message->content;
            $toolCalls = $message->toolCalls;
            $toolCallId = $message->toolCallId;
        } else {
            $role = CoreMessageRole::USER->value;
            $content = $message;
            $toolCalls = null;
            $toolCallId = null;
        }

        if (! is_string($content)) {
            $content = json_encode($content);
        }

        return $this->messages()->create([
            'role' => $role,
            'content' => $content,
            'tool_calls' => $toolCalls,
            'tool_call_id' => $toolCallId,
        ]);
    }

    public function chatMessages(int $maxLatestMessages = 200): HasMany
    {
        $latestMessages = $this->messages()
            ->latest('created_at')
            ->latest('id')
            ->whereIn('role', [
                CoreMessageRole::USER->value,
                CoreMessageRole::ASSISTANT->value,
                // NOTE: Very important to include TOOL messages.
                // They are replies to assistant tool calls that
                // are mandatory to be after the assistant tool
                // calls message.
                CoreMessageRole::TOOL->value,
            ])
            ->take($maxLatestMessages);

        return $this->messages()
            ->whereIn('id', function ($query) use ($latestMessages) {
                $query->select('id')
                    ->fromSub($latestMessages, 'latest_messages');
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

        Cache::put('ai_chat_name_'.$this->id, $name, now()->addMinutes(30));

        return $name;
    }

    public function nameWithFallback(): string
    {
        $name = $this->name;
        if ($name) {
            return $name;
        }

        return 'Unnamed'; // Better queue it, could be slow enough to fail the request.
        /*try {
            return $this->guessName();
        } catch (\Exception $e) {
            return 'Unnamed';
        }*/
    }
}
