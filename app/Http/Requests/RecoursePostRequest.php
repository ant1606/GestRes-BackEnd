<?php

namespace App\Http\Requests;

use App\Models\Settings;
use App\Enums\TypeRecourseEnum;
use Illuminate\Validation\Rule;
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
            "author" => 'max:75',
            "editorial" => 'max:75',
            "type_id" => [
                'required',
                'in:' . Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id") . ',' .
                    Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id")
            ],
            "total_pages" => [
                'nullable',
                'integer',
                Rule::requiredIf(fn () => $this->type_id == Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"))
            ],
            "total_chapters" => [
                'nullable',
                'integer',
                Rule::requiredIf(fn () =>  $this->type_id == Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"))
            ],
            "total_videos" => [
                'nullable',
                'integer',
                Rule::requiredIf(fn () => $this->type_id == Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"))
            ],
            "total_hours" => [
                'nullable',
                'regex:#(\d+)[:](\d{2})[:](\d{2})#',
                Rule::requiredIf(fn () => $this->type_id == Settings::getData(TypeRecourseEnum::TYPE_VIDEO->name, "id"))
            ],
            //https://stackoverflow.com/questions/32807517/laravel-preg-match-unknown-modifier
            //Se usa otro delimitador en regex (#)  ya que generaba slash generaba error preg_match(): Unknown modifier 'g'
        ];
    }
}
