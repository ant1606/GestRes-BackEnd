<?php

namespace App\Http\Controllers;

use App\Http\Resources\WebPageCollection;
use App\Http\Resources\WebPageResource;
use App\Models\WebPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class WebPageController extends ApiController
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
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

    return $this->showAllResource(new WebPageCollection($web_pages), Response::HTTP_OK);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $request->merge(["user_id" => Auth::user()->id]);
    // dd($request);
    $webpage = WebPage::create($request->all());
    $webpage->tags()->syncWithoutDetaching($request->tags);

    return $this->showOne(new WebPageResource($webpage), Response::HTTP_CREATED);
  }

  /**
   * Display the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function show($id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int  $id
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    //
  }
}
