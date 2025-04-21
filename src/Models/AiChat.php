<?php

namespace Lenorix\LaravelAiChat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;

use MalteKuhr\LaravelGPT\Enums\ChatRole;
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

    public function addMessage(ChatMessage|string $message): AiChatMessage
    {
        if ($message instanceof ChatMessage) {
            $role = $message->role->value;
            $content = $message->content;
        } else {
            $role = ChatRole::USER->value;
            $content = $message;
        }

        if (!in_array($role, [ChatRole::USER->value, ChatRole::ASSISTANT->value])) {
            throw new \InvalidArgumentException('Invalid role provided. Must be either "user" or "assistant".');
        }

        return $this->messages()->create([
            'role' => $role,
            'content' => $content,
        ]);
    }
}
