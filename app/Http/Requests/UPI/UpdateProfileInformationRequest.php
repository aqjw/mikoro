<?php

namespace App\Http\Requests\UPI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateProfileInformationRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($this->user()->id),
            ],
            'slug' => [
                'required',
                'min:3',
                'max:255',
                Rule::unique('users')->ignore($this->user()->id),
                Rule::notIn($this->getExcludedWords()),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->slug),
        ]);
    }

    protected function getExcludedWords(): array
    {
        return [
            'admin',
            'root',
            'system',
            'user',
            'users',
            'test',
            'support',
            'help',
            'contact',
            'login',
            'register',
            'logout',
            'profile',
            'account',
            'settings',
            'dashboard',
            'api',
            'about',
            'terms',
            'privacy',
            'faq',
            'blog',
            'shop',
            'store',
            'checkout',
            'cart',
            'payment',
            'order',
            'invoice',
            'download',
            'upload',
            'file',
            'search',
            'news',
            'feed',
            'home',
            'index',
            'main',
            'create',
            'delete',
            'edit',
            'update',
            'manage',
            'config',
            'configurations',
            'setting',
            'maintenance',
            'error',
            'success',
            'failure',
            'accept',
            'deny',
            'approve',
        ];
    }
}
