<?php

namespace App\Http\Requests\UPI;

use Illuminate\Foundation\Http\FormRequest;

class PlaybackStateRequest extends FormRequest
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
            'episode_id' => ['required', 'exists:episodes,id'],
            'translation_id' => ['required', 'exists:translations,id'],
            'time' => ['required'],
        ];
    }
}
