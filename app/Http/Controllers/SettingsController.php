<?php

  namespace App\Http\Controllers;

  use App\Http\Services\SettingsService;
  use App\Models\Settings;
  use Exception;
  use Illuminate\Http\JsonResponse;
  use Symfony\Component\HttpFoundation\Response;

//TODO Hacer casos de prueba del controlador
  class SettingsController extends ApiController
  {
    public function __construct(protected SettingsService $settingsService)
    {
    }

    public function index(): JsonResponse
    {
      $settings = Settings::all();
      return $this->sendResponse($settings->toArray(), Response::HTTP_OK, false);
    }

    /**
     * @throws Exception
     */
    public function show(string $value): JsonResponse
    {
      $data = $this->settingsService->get_settings_values_by_settingsType($value);
      return $this->sendResponse($data, Response::HTTP_OK, false);
    }
  }
