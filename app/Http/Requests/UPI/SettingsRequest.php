<?php

namespace App\Http\Requests\UPI;

use App\Enums\QualityOption;
use App\Enums\VisibilityOption;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],

            'settings.notifications' => ['required', 'array'],
            'settings.notifications.comment_reply' => ['required', 'boolean'],
            'settings.notifications.site_updates' => ['required', 'boolean'],
            'settings.notifications.news_alerts' => ['required', 'boolean'],

            'settings.player' => ['required', 'array'],
            'settings.player.default_quality' => ['required', Rule::enum(QualityOption::class)],
            'settings.player.default_translation' => ['nullable', 'exists:translations,id'],

            'settings.privacy' => ['required', 'array'],
            'settings.privacy.list_visibility' => ['required', Rule::enum(VisibilityOption::class)],
            'settings.privacy.history_visibility' => ['required', Rule::enum(VisibilityOption::class)],
        ];
    }
}
