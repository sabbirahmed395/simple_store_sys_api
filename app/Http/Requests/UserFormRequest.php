<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UserFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::user()->hasRole('admin') ?: false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules = [
            'name' => ['required'],
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['nullable'],
            'role' => ['required'],
        ];

        if (request()->method() == 'POST') {
            $rules['password'] = ['required', 'min:6'];
            $rules['email'] = ['required', 'email', 'unique:users,email'];
        }

        return $rules;
    }
}
