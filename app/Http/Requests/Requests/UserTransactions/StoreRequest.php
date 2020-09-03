<?php

namespace App\Http\Requests\Requests\UserTransactions;

use App\Transaction;
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
            'amount' => 'required|integer|min:0',
            'type' => [
                'required',
                Rule::in([Transaction::TYPE_CREDIT, Transaction::TYPE_DEBIT])
            ]
        ];
    }
}
