<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagRequest;
use App\Http\Resources\TagCollection;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isNull;

class TagController extends ApiController
{

    public function __construct()
    {
        $this->middleware('transform.input:' . TagResource::class)->only(['store', 'update']);
    }

    public function index(Request $request)
    {
        $tags = Tag::query();

        if ($request->has('searchNombre') && $request->searchNombre !== null)
            $tags = Tag::where('name', 'like', '%' . $request->searchNombre . '%');

        if (
            $request->has('sortNombre') &&
            $request->sortNombre !== null &&
            (Str::lower($request->sortNombre) === 'desc' || Str::lower($request->sortNombre) === 'asc')
        ) {
            $tags = $tags->orderBy('name', $request->sortNombre);
        } else {
            $tags = $tags->latest();
        }

        $tags = $tags->get();

        return $this->showAllResource(new TagCollection($tags), Response::HTTP_ACCEPTED);
    }

    public function store(TagRequest $request)
    {
        $tag = Tag::create([
            "name" => Str::upper($request->name),
            "style" => $request->style,
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
                "No se realizó ninguna modificacion de la etiqueta. Se cancelo la operación",
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

        return $this->showOne(new TagResource($tag), Response::HTTP_ACCEPTED);
    }
}
