<?php

  namespace App\Http\Requests;

  use App\Helpers\TimeHelper;
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
        "advanced" => [
          'required',
          function (string $attribute, $value, $fail) {
            $seconds = str_contains($value, ":") ? TimeHelper::convertHourToSeconds($value) : $value;
            if($seconds <= 0){
              $fail("El valor avanzado debe ser mayor a 00:00:00 o a 0");
            }
          }
        ],
        "date" => 'required|date',
        "comment" => 'max:1000'
      ];
    }

    // public function withValidator($validator){
    //     $validator->sometimes(['reason', 'cost'], 'required', function ($input) {
    //         return $input->games >= 100;
    //     });
    // }
  }
