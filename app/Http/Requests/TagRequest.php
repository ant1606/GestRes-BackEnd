<?php

namespace App\Http\Requests;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class TagRequest extends FormRequest
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
    // 'email|unique:users,email,' . $user->id,
    //         dd($this);
    return [
      'name' => 'required|unique:tags,name,' . $this->id . '|max:50',
    ];
  }

  public function prepareForValidation()
  {
    $this->merge([
      'name' => Str::upper($this->name),
    ]);
  }
}
