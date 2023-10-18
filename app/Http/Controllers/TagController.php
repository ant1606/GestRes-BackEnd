<?php

namespace App\Http\Controllers;

use App\Enums\TagStyleEnum;
use App\Http\Requests\TagRequest;
use App\Http\Resources\TagCollection;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class TagController extends ApiController
{

  public function __construct()
  {
    // $this->middleware('transform.input:' . TagResource::class)->only(['store', 'update']);
  }

  public function index(Request $request)
  {
    $tags = Tag::query();

    if ($request->has('searchNombre') && $request->searchNombre !== null)
      $tags = $tags->where('name', 'like', '%' . $request->searchNombre . '%');

    if (
      $request->has('sortNombre') &&
      $request->sortNombre !== null &&
      (Str::lower($request->sortNombre) === 'desc' || Str::lower($request->sortNombre) === 'asc')
    ) {
      $tags = $tags->orderBy('name', $request->sortNombre);
    } else {

      $tags = $tags->latest();
    }

    // Agregar recuento de recursos asociados con cada etiqueta
    $tags = $tags->withCount('recourses')->get();
    // dd($tags);

    return $this->showAllResource(new TagCollection($tags), Response::HTTP_ACCEPTED);
  }

  public function store(TagRequest $request)
  {
    $tag = Tag::create([
      "name" => Str::upper($request->name),
      "style" => $this->randomTagStyle(),
    ]);

    return $this->showOne(new TagResource($tag), Response::HTTP_CREATED);
  }

  public function show(Tag $tag)
  {
    return $this->showOne(new TagResource($tag), Response::HTTP_ACCEPTED);
  }

  public function update(TagRequest $request, Tag $tag)
  {
    // dd($request->name);
    $tag->fill($request->only([
      "name"
    ]));

    if ($tag->isClean()) {
      return $this->errorResponse(
        ["api_response" => ["No se realizó ninguna modificacion de la etiqueta. Se cancelo la operación"]],
        Response::HTTP_UNPROCESSABLE_ENTITY
      );
    }

    $tag->save();

    return $this->showOne(new TagResource($tag), Response::HTTP_ACCEPTED);
    // dd($tag->name);
  }

  public function destroy(Tag $tag)
  {
    $tag->delete();
    $tag->recourses()->detach();
    //TODO Se sugiere que al momento de eliminar, se lance un evento para eliminar las relaciones de Tag
    //   static::deleting(function ($tag) {
    //     // Eliminar las relaciones con recursos, canales y páginas web
    //     $tag->recourses()->detach();
    //     $tag->channels()->detach();
    //     $tag->webPages()->detach();
    // });

    return $this->showOne(new TagResource($tag), Response::HTTP_ACCEPTED);
  }

  public function getTagsForTagSelector()
  {
    //TODO Ver como estandarizar esta respuesta y hacer el cambio con el frontend
    $tags = Tag::all();
    return $this->showAll($tags, Response::HTTP_ACCEPTED);
  }

  private function randomTagStyle()
  {
    return array_rand([
      TagStyleEnum::TAG_STYLE_BLUE->value => 1,
      TagStyleEnum::TAG_STYLE_EMERALD->value => 1,
      TagStyleEnum::TAG_STYLE_GREEN->value => 1,
      TagStyleEnum::TAG_STYLE_INDIGO->value => 1,
      TagStyleEnum::TAG_STYLE_LIME->value => 1,
      TagStyleEnum::TAG_STYLE_ORANGE->value => 1,
      TagStyleEnum::TAG_STYLE_PINK->value => 1,
      TagStyleEnum::TAG_STYLE_PURPLE->value => 1,
      TagStyleEnum::TAG_STYLE_RED->value => 1,
      TagStyleEnum::TAG_STYLE_ROSE->value => 1,
      TagStyleEnum::TAG_STYLE_SKY->value => 1,
      TagStyleEnum::TAG_STYLE_TEAL->value => 1,
      TagStyleEnum::TAG_STYLE_YELLOW->value => 1,
      TagStyleEnum::TAG_STYLE_GRAY->value => 1
    ]);
  }
}
