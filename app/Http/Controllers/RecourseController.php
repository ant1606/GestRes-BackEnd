<?php

  namespace App\Http\Controllers;

  use App\Enums\StatusRecourseEnum;
  use App\Enums\UnitMeasureProgressEnum;
  use App\Http\Requests\RecoursePostRequest;
  use App\Http\Requests\RecourseUpdateRequest;
  use App\Http\Resources\RecourseCollection;
  use App\Http\Resources\RecourseResource;
  use App\Http\Services\RecourseService;
  use App\Models\ProgressHistory;
  use App\Models\Recourse;
  use App\Models\Settings;
  use App\Models\StatusHistory;
  use Carbon\Carbon;
  use Illuminate\Http\JsonResponse;
  use Illuminate\Http\Request;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\DB;
  use Symfony\Component\HttpFoundation\Response;


  class RecourseController extends ApiController
  {
    public function __construct(protected RecourseService $recourseService)
    {
      // $this->middleware('transform.input:' . RecourseResource::class)->only(['store', 'update']);
    }

    public function index(Request $request): JsonResponse
    {
      $data = $this->recourseService->get_recourses(
        $request->input('searchTags', []),
        $request->input('searchNombre'),
        $request->input('searchTipo'),
        $request->input('searchEstado'),
      );
      return $this->sendResponse(new RecourseCollection($data), Response::HTTP_OK);
    }

    public function store(RecoursePostRequest $request): JsonResponse
    {
      $data = $this->recourseService->save_recourse($request->toArray());
      return $this->sendResponse(new RecourseResource($data), Response::HTTP_CREATED);
    }

    public function show(Recourse $recourse): JsonResponse
    {
      $recourse->load('status', 'progress', 'tags');
      return $this->sendResponse(new RecourseResource($recourse), Response::HTTP_OK);
    }

    public function update(Recourse $recourse, RecourseUpdateRequest $request): JsonResponse
    {
      $data = $this->recourseService->update_recourse($recourse, $request->toArray());
      return $this->sendResponse(new RecourseResource($data),Response::HTTP_ACCEPTED, false);
    }

    public function destroy(Recourse $recourse): JsonResponse
    {
      $data = $this->recourseService->delete_recourse($recourse);
      return $this->sendResponse($data, Response::HTTP_ACCEPTED);
    }

  }
