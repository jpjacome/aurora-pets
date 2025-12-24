<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatbotAdminCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'comment' => 'required|string|max:5000',
            'conversation_context' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'comment.required' => 'Please enter a comment',
            'comment.max' => 'Comment is too long',
        ];
    }
}
