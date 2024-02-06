<?php

  namespace App\Http\Controllers;

  use App\Http\Requests\WebPagePostRequest;
  use App\Http\Resources\WebPageCollection;
  use App\Http\Resources\WebPageResource;
  use App\Http\Services\WebPageService;
  use App\Models\WebPage;
  use Exception;
  use Illuminate\Http\JsonResponse;
  use Illuminate\Http\Request;
  use Symfony\Component\HttpFoundation\Response;

  //TODO Realizar los casos de Test faltantes de este controlador
  class WebPageController extends ApiController
  {

    public function __construct(protected WebPageService $webPageService)
    {
    }

    public function index(Request $request): JsonResponse
    {
      $data = $this->webPageService->get_web_pages(
        $request->input('searchTags', []),
        $request->input('searchNombre'),
      );
      return $this->sendResponse(new WebPageCollection($data), Response::HTTP_OK);
    }

    public function store(WebPagePostRequest $request): JsonResponse
    {
      $data = $this->webPageService->save_web_page(
        $request->input('url'),
        $request->input('name'),
        $request->input('description'),
        $request->input('tags', []),
      );
      return $this->sendResponse(new WebPageResource($data), Response::HTTP_CREATED, false);
    }

    /**
     * @throws Exception
     */
    public function update(WebPage $webpage, Request $request): JsonResponse
    {
      $data = $this->webPageService->update_web_page(
        $webpage,
        $request->input('url'),
        $request->input('name'),
        $request->input('description'),
        $request->input('tags', []),
      );

      return $this->sendResponse(new WebPageResource($data), Response::HTTP_ACCEPTED, false);
    }

    public function destroy(WebPage $webpage): JsonResponse
    {
      $data = $this->webPageService->delete_web_page($webpage);
      return $this->sendResponse(new WebPageResource($data), Response::HTTP_ACCEPTED, false);
    }
  }
