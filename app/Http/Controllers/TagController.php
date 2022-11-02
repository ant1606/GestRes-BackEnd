<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagRequest;
use App\Http\Resources\TagCollection;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isNull;

class TagController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        // if (isset($request->filter) && Str::length($request->filter) >= 1 && Str::length($request->filter) <= 2) {
        //     return $this->errorResponse(
        //         "El valor del filtro debe ser mayor a 2 caracteres.",
        //         Response::HTTP_LENGTH_REQUIRED
        //     );
        // }

        // $tags = Tag::where('name', 'like', '%' . $request->filter . '%')
        //     ->orderBy('name', 'asc')
        //     ->get();

        // if ($tags->count() === 0)
        //     return $this->errorResponse(
        //         "No se encontraron resultados.",
        //         Response::HTTP_ACCEPTED
        //     );

        // return $this->showAll($tags, Response::HTTP_ACCEPTED);

        // return TagResource::collection(Tag::all());
        // return new TagCollection(Tag::all());
        return $this->showAllResource(new TagCollection(Tag::all()), Response::HTTP_ACCEPTED);
    }

    public function store(TagRequest $request)
    {
        $tag = Tag::create([
            "name" => Str::upper($request->name),
            "style" => $request->style,
        ]);

        return $this->showOne($tag, Response::HTTP_CREATED);
    }

    public function show(Tag $tag)
    {
        // dd($tag);
        // return new TagResource(Tag::findOrFail($tag));
        return new TagResource($tag);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
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

        return $this->showOne($tag, Response::HTTP_ACCEPTED);
        // dd($tag->name);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Number  $tag 
     * @return \Illuminate\Http\Response
     */
    public function destroy($tag)
    {
        $tagObj = Tag::findOrFail($tag);

        $tagObj->delete();

        return $this->showOne($tagObj, Response::HTTP_ACCEPTED);
    }
}
