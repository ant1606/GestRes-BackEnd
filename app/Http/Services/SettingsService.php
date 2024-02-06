<?php

  namespace App\Http\Services;

  use App\Enums\TypeSettingsEnum;
  use App\Models\Settings;
  use Exception;
  use Symfony\Component\HttpFoundation\Response;

  class SettingsService
  {
    /**
     * @param string $settingType Debe ser uno de los casos de TypeSettingsEnum
     * @throws Exception
     */
    public function get_settings_values_by_settingsType(string $settingType): array
    {
      $typeEnum = null;

      foreach (TypeSettingsEnum::cases() as $type) {
        if ($type->name === $settingType) {
          $typeEnum = $type->value;
          break;
        }
      }

      if ($typeEnum) {
        $res = collect($typeEnum::cases())->map(function ($case) {
          return Settings::getData($case->name);
        });
        return $res->toArray();
      }

      throw  new Exception("Error al procesar la data de Settings", Response::HTTP_UNPROCESSABLE_ENTITY);
    }
  }
