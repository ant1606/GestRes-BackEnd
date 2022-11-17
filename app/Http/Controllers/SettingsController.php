<?php

namespace App\Http\Controllers;

use App\Enums\StatusRecourseEnum;
use App\Enums\TypeRecourseEnum;
use App\Enums\TypeSettingsEnum;
use App\Models\Settings;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function PHPSTORM_META\map;

class SettingsController extends ApiController
{

    public function index()
    {
        $settings = Settings::all();
        return $this->showAll($settings, Response::HTTP_OK);
    }

    public function show($value)
    {
        $typeEnum = null;

        foreach (TypeSettingsEnum::cases() as $type) {
            if ($type->name === $value) {
                $typeEnum = $type->value;
                break;
            }
        }

        if ($typeEnum) {
            $res = collect($typeEnum::cases())->map(function ($case) {
                return Settings::getData($case->name);
            });

            return $this->showAll($res, Response::HTTP_OK);
        } else {
            return $this->errorResponse("Error al procesar la data", Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
