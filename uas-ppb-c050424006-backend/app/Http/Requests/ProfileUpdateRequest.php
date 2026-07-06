<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'nim_nis' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:255'],
            'nama_institusi' => ['nullable', 'string', 'max:255'],
            'jenis_institusi' => ['nullable', 'string', 'in:perguruan_tinggi,sekolah'],
            'program_studi' => ['nullable', 'string', 'max:255'],
            'target_ipk' => ['nullable', 'numeric', 'between:0,4.00'],
            'target_sks' => ['nullable', 'integer', 'between:1,200'],
            'foto_profil' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
