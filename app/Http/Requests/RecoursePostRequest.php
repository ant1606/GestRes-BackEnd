<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecoursePostRequest extends FormRequest
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
            "name" => 'required|max:150',
            "source" => 'required|max:255',
            // "author" => 'el autor',
            // "editorial" => 'Nombre de mi recurso',
            "type_id" => 'required|in:1,2', //1 sera video, 2 sera libro, por el momento
            // "total_pages" => 1,
            // "total_chapters" => 1,
            // "total_videos" => 150,
            // "total_hours" => "15:30:12"
        ];
    }
}
