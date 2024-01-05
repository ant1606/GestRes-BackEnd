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
      "advanced" => 'required|numeric|min:1',
      "date" => 'required|date',
      "comment" => 'max:100'
    ];
  }

  // public function withValidator($validator){
  //     $validator->sometimes(['reason', 'cost'], 'required', function ($input) {
  //         return $input->games >= 100;
  //     });
  // }
}
