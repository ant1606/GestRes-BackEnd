<?php

namespace App\Http\Controllers;

use App\Http\Resources\WebPageCollection;
use App\Http\Resources\WebPageResource;
use App\Models\WebPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class WebPageController extends ApiController
{
  //TODO Realizar los casos de Test faltantes de este controlador
  public function index(Request $request)
  {
    $web_pages = WebPage::query();

    $web_pages = $web_pages->where('user_id', Auth::user()->id);

    if ($request->has('searchTags') && $request->searchTags !== null && $request->searchTags !== []) {
      $web_pages = $web_pages->whereHas('tags', function ($query) use ($request) {
        $query->whereIn('tag_id', $request->searchTags);
      });
    }
    if ($request->has('searchNombre') && $request->searchNombre !== null)
      $web_pages = $web_pages->where('name', 'like', '%' . $request->searchNombre . '%');

    $web_pages = $web_pages->latest()->get();

    return $this->sendResponse(new WebPageCollection($web_pages), Response::HTTP_OK);
  }

  public function store(Request $request)
  {
    $request->merge(["user_id" => Auth::user()->id]);
    // dd($request);
    $webpage = WebPage::create($request->all());
    $webpage->tags()->syncWithoutDetaching($request->tags);

    return $this->sendResponse(new WebPageResource($webpage), Response::HTTP_CREATED, false);
  }


  public function update(WebPage $webpage, Request $request)
  {

    $webpage->fill($request->only([
      'name',
      'description',
      'url',
    ]));
    $existingTags = $webpage->tags()->pluck('taggables.tag_id')->toArray();

    if ($webpage->isClean() && (isset($request->tags) ? $request->tags : []) === $existingTags) {
      return $this->sendError(
        Response::HTTP_UNPROCESSABLE_ENTITY,
        "Se debe especificar al menos un valor diferente para actualizar"
      );
    }

    try {
      DB::beginTransaction();

      $webpage->save();
      $webpage->tags()->sync($request->tags);


      DB::commit();
      return $this->sendResponse(new WebPageResource($webpage), Response::HTTP_ACCEPTED, false);
    } catch (\Throwable $th) {
      DB::rollBack();
      // TODO Escribir los mensajes de error en un log $e->getMessage()
//      dd($th);
      return $this->sendError(
        Response::HTTP_UNPROCESSABLE_ENTITY,
        "Ocurrió un error al actualizar la página Web, hable con el administrador"
      );
    }
  }

  public function destroy(WebPage $webpage)
  {
    //TODO Insertar autorizacion para eliminar webpage sólo al usuario que lo creo
    $webpage->tags()->detach();
    $webpage->delete();

    return $this->sendResponse(new WebPageResource($webpage), Response::HTTP_ACCEPTED, false);
  }
}
