<?php

  namespace App\Http\Services;

  use App\Helpers\TagHelper;
  use App\Models\Tag;
  use Exception;
  use Illuminate\Support\Str;
  use Symfony\Component\HttpFoundation\Response;

  class TagService
  {
    public function get_tags(string $searchNombre = null, string $sortNombre = null)
    {
      return Tag::when($searchNombre !== null, function ($query) use ($searchNombre) {
        $query->where('name', 'like', '%' . $searchNombre. '%');
      })
        ->when($sortNombre !== null ,
          function ($query) use ($sortNombre) {
            $query->orderBy('name', $sortNombre);
          }, function ($query) {
            $query->latest();
          })
        ->withCount('recourses')
        ->withCount('youtubesubscription')
        ->withCount('webpages')
        ->get();
    }

    // TEstear cual forma ee más rapida
    //  public function index2(Request $request)
//  {
//    $tags = Tag::query();
//
//    if ($request->has('searchNombre') && $request->searchNombre !== null)
//      $tags = $tags->where('name', 'like', '%' . $request->searchNombre . '%');
//
//    if (
//      $request->has('sortNombre') &&
//      $request->sortNombre !== null &&
//      (Str::lower($request->sortNombre) === 'desc' || Str::lower($request->sortNombre) === 'asc')
//    ) {
//      $tags = $tags->orderBy('name', $request->sortNombre);
//    } else {
//
//      $tags = $tags->latest();
//    }
//
//    // Agregar recuento de recursos, suscripciones y páginas web asociados con cada etiqueta
//    $tags = $tags->withCount('recourses')->withCount('youtubesubscription')->withCount('webpages')->get();
//    // dd($tags);
//
//    return $this->sendResponse(new TagCollection($tags), Response::HTTP_ACCEPTED);
////    return $this->showAllResource(new TagCollection($tags), Response::HTTP_ACCEPTED);
//  }

    public function save_tag(string $name)
    {
      $tag = Tag::create([
        "name" => Str::upper($name),
        "style" => TagHelper::getRandomTagStyle()
      ]);
      return $tag;
    }

    /**
     * @throws Exception
     */
    public function update_tag(Tag $tag, string $name ): Tag
    {
      $tag->fill(['name'=>$name]);

      if ($tag->isClean()) {
        throw new Exception(
          "No se realizó ninguna modificación del Tag. Se cancelo la operación",
          Response::HTTP_UNPROCESSABLE_ENTITY
        );
      }

      $tag->save();
      return $tag;
    }

    public function delete_tag(Tag $tag): Tag
    {
      $tag->delete();
      $tag->recourses()->detach();
      $tag->youtubesubscription()->detach();
      $tag->webpages()->detach();
      //TODO Se sugiere que al momento de eliminar, se lance un evento para eliminar las relaciones de Tag
      //   static::deleting(function ($tag) {
      //     // Eliminar las relaciones con recursos, canales y páginas web
      //     $tag->recourses()->detach();
      //     $tag->channels()->detach();
      //     $tag->webPages()->detach();
      // });
      return $tag;
    }

  }
