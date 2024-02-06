<?php

  namespace App\Http\Requests;

  use Illuminate\Foundation\Http\FormRequest;
  use Illuminate\Validation\Rule;

  class YoutubeSubscriptionStoreRequest extends FormRequest
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
        'access_token' => 'required',
        'order' => [
          'required',
          Rule::in(['alphabetical', 'relevance', 'unread'])
        ]
      ];
    }
  }
