<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExistsInModelAttribute;
use Illuminate\Support\Facades\Auth;

class ItemFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // if user is admin return true, false otherwise
        return Auth::user()->hasRole('admin') ?: false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'min:3'],
            'description' => ['nullable'],
            'status' => [
                'required', 
                // new \App\Rules\ExistsInModelAttribute(Item::class, 'status')
            ],
        ];
    }
}
