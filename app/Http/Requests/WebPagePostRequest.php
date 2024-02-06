<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WebPagePostRequest extends FormRequest
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
          'url'=>['required'],
          'name'=>['string','nullable'],
          'description'=>['string', 'nullable'],
          'count_visits'=>['numeric']
        ];
    }
}
