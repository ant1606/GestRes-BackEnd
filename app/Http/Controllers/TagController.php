<?php

  namespace App\Http\Controllers;

  use App\Http\Requests\TagIndexRequest;
  use App\Http\Requests\TagRequest;
  use App\Http\Resources\TagCollection;
  use App\Http\Resources\TagResource;
  use App\Http\Services\TagService;
  use App\Models\Tag;
  use Exception;
  use Illuminate\Http\JsonResponse;
  use Symfony\Component\HttpFoundation\Response;

  class TagController extends ApiController
  {

    public function __construct(protected TagService $tagService)
    {
      // $this->middleware('transform.input:' . TagResource::class)->only(['store', 'update']);
    }

    public function index(TagIndexRequest $request): JsonResponse
    {
      $data = $this->tagService->get_tags($request->input("searchNombre"), $request->input('sortNombre'));
      return $this->sendResponse(new TagCollection($data), Response::HTTP_OK);
    }

    public function getTagsForTagSelector(): JsonResponse
    {
      $tags = Tag::all();
      return $this->sendResponse($tags->toArray(), Response::HTTP_ACCEPTED);
    }

    public function store(TagRequest $request): JsonResponse
    {
      $data = $this->tagService->save_tag($request->input('name'));
      return $this->sendResponse(new TagResource($data), Response::HTTP_CREATED);
    }

    public function show(Tag $tag): JsonResponse
    {
      return $this->sendResponse(new TagResource($tag), Response::HTTP_ACCEPTED);
    }

    /**
     * @throws Exception
     */
    public function update(TagRequest $request, Tag $tag): JsonResponse
    {
      $data = $this->tagService->update_tag($tag, $request->input("name"));
      return $this->sendResponse(new TagResource($data), Response::HTTP_ACCEPTED);
    }

    public function destroy(Tag $tag): JsonResponse
    {
      $data = $this->tagService->delete_tag($tag);
      return $this->sendResponse(new TagResource($data), Response::HTTP_ACCEPTED);
    }

  }
