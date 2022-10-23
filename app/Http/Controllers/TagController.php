<?php

namespace App\Http\Controllers;

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
        if (isset($request->filter) && Str::length($request->filter) >= 1 && Str::length($request->filter) <= 2) {
            return $this->errorResponse(
                "El valor del filtro debe ser mayor a 2 caracteres.",
                Response::HTTP_LENGTH_REQUIRED
            );
        }

        $tags = Tag::where('name', 'like', '%' . $request->filter . '%')
            ->get();

        if ($tags->count() === 0)
            return $this->errorResponse(
                "No se encontraron resultados.",
                Response::HTTP_ACCEPTED
            );

        return $this->showAll($tags, Response::HTTP_ACCEPTED);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:tags|max:50',
        ]);

        $tag = Tag::create([
            "name" => $request->name,
            "style" => $request->style,
        ]);

        return $this->showOne($tag, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function show(Tag $tag)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tag $tag)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tag  $tag
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tag $tag)
    {
        //
    }
}
