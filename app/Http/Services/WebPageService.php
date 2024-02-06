<?php

  namespace App\Http\Services;

  use App\Models\WebPage;
  use Exception;
  use Illuminate\Database\Eloquent\Collection;
  use Illuminate\Support\Facades\Auth;
  use Illuminate\Support\Facades\DB;
  use Symfony\Component\HttpFoundation\Response;

  class WebPageService
  {
    public function get_web_pages(array $searchTags = [], string $searchNombre = null): Collection|array
    {
      $web_pages = WebPage::query();

      $web_pages = $web_pages->where('user_id', Auth::user()->id);

      if ($searchTags !== []) {
        $web_pages = $web_pages->whereHas('tags', function ($query) use ($searchTags) {
          $query->whereIn('tag_id', $searchTags);
        });
      }
      if ($searchNombre !== null)
        $web_pages = $web_pages->where('name', 'like', '%' . $searchNombre . '%');

      return $web_pages->latest()->get();
    }

    public function save_web_page(string $url, string $name = null, string $description = null, array $tags = [])
    {
      $webpage = WebPage::create([
        "url" => $url,
        "name" => $name,
        "description" => $description,
        "count_visits" => 0,
        "user_id" => Auth::user()->id
      ]);
      $webpage->tags()->syncWithoutDetaching($tags);
      return $webpage;
    }

    /**
     * @throws Exception
     */
    public function update_web_page(WebPage $webpage, string $url, string $name = null, string $description = null, array $tags = []): WebPage
    {
      $webpage->fill([
        'name' => $name,
        'description' => $description,
        'url' => $url,
      ]);
      $existingTags = $webpage->tags()->pluck('taggables.tag_id')->toArray();

      if ($webpage->isClean() && $tags === $existingTags) {
        throw new Exception(
          "Se debe especificar al menos un valor diferente para actualizar",
          Response::HTTP_UNPROCESSABLE_ENTITY
        );
      }

      try {
        DB::beginTransaction();
        $webpage->save();
        $webpage->tags()->sync($tags);
        DB::commit();
        return $webpage;
      } catch (Exception $e) {
        DB::rollBack();
        // TODO Escribir los mensajes de error en un log $e->getMessage()
        // dd($th);
        throw new Exception(
          "Ocurrió un error al actualizar la página Web, hable con el administrador",
          Response::HTTP_UNPROCESSABLE_ENTITY
        );
      }
    }

    public function delete_web_page(WebPage $webPage): WebPage
    {
      //TODO Insertar autorización para eliminar webpage sólo al usuario que lo creo
      $webPage->tags()->detach();
      $webPage->delete();

      return $webPage;
    }

  }
