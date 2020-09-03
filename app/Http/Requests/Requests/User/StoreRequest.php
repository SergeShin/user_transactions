<?php

namespace App\Http\Requests\Requests\User;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:App\User,name',
            'email' => 'required|email|unique:App\User,email',
            'password' => 'required',
            'permissions' => [
                'required',
                Rule::in([User::PERMISSIONS_USER, USer::PERMISSIONS_ADMIN]),
            ]
        ];
    }
}
