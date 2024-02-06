<?php

  namespace App\Http\Requests;

  use Illuminate\Foundation\Http\FormRequest;
  use Illuminate\Support\Str;
  use Illuminate\Validation\Rule;

  class TagIndexRequest extends FormRequest
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
        "searchNombre" => "min:3",
        "sortNombre" => Rule::in(['desc', 'asc'])
      ];
    }

    public function prepareForValidation()
    {
      $this->merge([
        'name' => Str::lower($this->sortNombre),
      ]);
    }
  }
