<?php

namespace App\Http\Controllers;

use App\Enums\StatusRecourseEnum;
use App\Enums\TypeRecourseEnum;
use App\Models\Settings;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use function PHPSTORM_META\map;

class SettingsController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Settings  $settings
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        // dd(Settings::getData("TYPE_LIBRO"));
        // dd(collect(TypeRecourseEnum::cases()));

        // dd($value);
        $value = $request->value;
        switch ($value) {
            case 'type':
                $typeEnum = TypeRecourseEnum::class;
                break;
            case 'status':
                $typeEnum = StatusRecourseEnum::class;
                break;
            default:
                $typeEnum = null;
                break;
        }

        if ($typeEnum) {
            // dd(Settings::getData("TYPE_LIBRO"));
            // dd($typeEnum::cases());

            // dd($res);
            $res = collect($typeEnum::cases())->map(function ($case) {
                return Settings::getData($case->name);
            });

            return $this->showAll($res, Response::HTTP_OK);
        } else {
            return $this->errorResponse("Error al procesar la data", Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Settings  $settings
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Settings $settings)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Settings  $settings
     * @return \Illuminate\Http\Response
     */
    public function destroy(Settings $settings)
    {
        //
    }
}
