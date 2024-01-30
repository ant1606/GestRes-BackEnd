<?php

namespace App\Http\Controllers;

use App\Enums\StatusRecourseEnum;
use App\Enums\TypeRecourseEnum;
use App\Enums\TypeSettingsEnum;
use App\Http\Resources\SettingsCollection;
use App\Models\Settings;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function PHPSTORM_META\map;

class SettingsController extends ApiController
{

  //TODO Hacer caso de prueba del siguiente método
    public function index()
    {
        $settings = Settings::all();
        return $this->sendResponse($settings->toArray(), Response::HTTP_OK, false);
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
            return $this->sendResponse($res->toArray(), Response::HTTP_OK, false);
        } else {
            return $this->sendError(Response::HTTP_UNPROCESSABLE_ENTITY, "Error al procesar la data");
        }
    }
}
