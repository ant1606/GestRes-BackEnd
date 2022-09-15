<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgressHistoryStoreRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "done" => 'required|numeric|min:1',
            "pending" => 'required|numeric|min:0'
        ];
    }

    // public function withValidator($validator){
    //     $validator->sometimes(['reason', 'cost'], 'required', function ($input) {
    //         return $input->games >= 100;
    //     });
    // }
}
