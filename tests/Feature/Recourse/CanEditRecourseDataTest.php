<?php

namespace Tests\Feature\Recourse;

use App\Enums\TypeRecourseEnum;
use App\Enums\UnitMeasureProgressEnum;
use App\Models\Recourse;
use App\Models\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class CanEditRecourseDataTest extends TestCase
{
  use RefreshDatabase;
  use RecourseDataTrait;

  /**
   * Casos para realizar los test en la edición de Recurso
   *    CASOS EDICION
   * [x] 1) Si se edita solo los datos del recurso y este no tiene progresos registrados
   *    Solo deben modificarse los datos del recurso cambiados
   *
   * [x] 2) Si se edita solo los datos del recurso y este tiene progresos registrados
   *    Solo deben modificarse los datos del recurso cambiados
   *
   * [x] 3) Si se edita el tipo del recurso y este no tiene progresos registrados
   *    Debe mostrarse una notificación al usuario indicandole que se cambio el tipo del recurso, y si el usuario acepta, Deberá cambiar el tipo del recurso y la nueva unidad de medida que haya seleccionado, y se deberá generar un nuevo registro con el total de la nueva unidad de medida seleccionada
   *
   * [x] 4) Si se edita el tipo del recurso y este tiene progresos registrados
   *    Deberá cambiar el tipo del recurso y la nueva unidad de medida que haya seleccionado, y se deberá eliminar todos los progresos existentes y generar un nuevo registro con el total de la nueva unidad de medida seleccionada
   *
   * [x] 5) Si se edita la unidad de medida del recurso y este no tiene progresos registrados
   *    Debe mostrarse una notificación al usuario indicandole que se cambio la unidad de medida del progreso del recurso, y si el usuario acepta, Deberá cambiar la nueva unidad de medida que haya seleccionado, y se deberá eliminar todos los progresos existentes y generar un nuevo registro con el total de la nueva unidad de medida seleccionada
   *
   * [x] 6) Si se edita la unidad de medida del recurso y este tiene progresos registrados
   *    Deberá cambiar la nueva unidad de medida que haya seleccionado, y se deberá eliminar todos los progresos existentes y generar un nuevo registro con el total de la nueva unidad de medida seleccionada
   *
   * [x] 7) No se modifica el tipo ni la unidad de medida del recurso pero aumenta el valor de la unidad de medida de progreso y no tiene progresos existentes
   *    Se modificará (aumentará) el monto de la unidad de medida tanto en el registro del recurso como en el registro de progreso generado por el sistema
   *
   * [x] 8) No se modifica el tipo ni la unidad de medida del recurso pero aumenta el valor de la unidad de medida de progreso y tiene registros existentes
   *    Se deberá mantener los registros del progreso y a estos se les deberá aumentar la diferencia existente entre el total de la unidad de medida nueva y la anterior, y en los registros del recurso deberá actualizarse el total
   *
   * [x] 9) No se modifica el tipo ni la unidad de medida del recurso pero disminuy el valor de la unidad de medida de progreso y no tiene progresos existentes
   *    Se modificará (disminuirá) el monto de la unidad de medida tanto en el registro del recurso como en el registro de progreso generado por el sistema
   *
   * [x] 10) No se modifica el tipo ni la unidad de medida del recurso pero disminuy el valor de la unidad de medida de progreso y tiene registros existentes
   *    Se deberá mantener los registros del progreso y a estos se les deberá reducir la diferencia existente entre el total de la unidad de medida nueva y la anterior, y en los registros del recurso deberá actualizarse el total
   *
   * [x]  11) No se modifica el tipo ni la unidad de medida del recurso pero disminuye el valor de la unidad de medida de progreso  alcanzando al avance del último registro existente
   *    Se deberá actualizar la cantidad pendiente de los registros y el recurso pasará a estado Completado.
   *    ** Si se modifica la cantidad pendiente de progresos, pero el estado del recurso no ha cambiado, esto deberíá realizarse en el frontend
   *
   * [x] 12) No se modifica el tipo ni la unidad de medida del recurso pero disminuye el valor de la unidad de medida de progreso , siendo este menor al  avance del último registro existente
   *    Se deberá eliminar el ultimo registro del progreso y actualizar la cantidad pendiente del resto de los registros de progreso
   */
  //TODO GEnerar los test unitarios para el StoreUpdateRequest
  //TODO Generar los test para el siguiente caso :  Al momento de editar un recurso y si se modifica el tipo y los totales, mandar un mensaje de confirmacion indicando que se borraran los avances registrados de estado y avance del recurso

  /** @test */
  public function recourse_can_be_edited_when_change_only_required_values()
  {
//    $this->withoutExceptionHandling();
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);

    $this->assertDatabaseCount('recourses', 1);

    $recourseUpdate = [
      "name" => "Recurso Actualizado",
      "source" => $recourse['source'],
      "type_id" => Settings::getData(TypeRecourseEnum::TYPE_LIBRO->name, "id"),
      'unit_measure_progress_id' => Settings::getData(UnitMeasureProgressEnum::UNIT_PAGES->name, "id"),
      "total_pages" => 257,
      "total_chapters" => 13,
    ];

    $response = $this->actingAs($user)->putJson(route('recourses.update', $recourse), $recourseUpdate);

    $response->assertStatus(Response::HTTP_ACCEPTED);
    $this->assertDatabaseCount('recourses', 1);
    $this->assertDatabaseHas('recourses', [
      "name" => $recourseUpdate["name"],
      "source" => $recourse["source"],
      "author" => $recourse["author"],
      "editorial" => $recourse["editorial"],
      "type_id" => $recourseUpdate["type_id"],
      "total_pages" => $recourseUpdate["total_pages"],
      "total_chapters" => $recourseUpdate["total_chapters"],
      "total_videos" => $recourse["total_videos"],
      "total_hours" => $recourse["total_hours"]
    ]);
    $response->assertJsonFragment(["nombre"=>$recourseUpdate["name"]]);
  }

  //TODO Revisar este test, fallada en ocasiones
  // Respuesta de error de test: The HTTP status code "0" is not valid.
  /** @test */
  public function recourse_can_be_edited_when_change_all_values()
  {
    $this->markTestSkipped();
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);

    $this->assertDatabaseCount('recourses', 1);

    $recourseUpdate = $this->recourseValidData(["name" => 'Mi Recurso Actualizado nuevo', "user_id"=>$user->id]);
    $response = $this->actingAs($user)->putJson(route('recourses.update', $recourse), $recourseUpdate);

    $response->assertStatus(Response::HTTP_ACCEPTED);
    $this->assertDatabaseCount('recourses', 1);
    $this->assertDatabaseHas('recourses', [
      "name" => $recourseUpdate["name"],
      "source" => $recourseUpdate["source"],
      "author" => $recourseUpdate["author"],
      "editorial" => $recourseUpdate["editorial"],
      "type_id" => $recourseUpdate["type_id"],
      "total_pages" => $recourseUpdate["total_pages"],
      "total_chapters" => $recourseUpdate["total_chapters"],
      "total_videos" => $recourseUpdate["total_videos"],
      "total_hours" => $recourseUpdate["total_hours"]
    ]);
    $response->assertJsonFragment(["nombre"=>$recourseUpdate["name"]]);
  }

  //TODO Revisar este test, fallado en ocasiones
  /** @test */
  public function recourse_cannot_be_edited_when_any_values_have_not_changed()
  {
    $this->markTestSkipped();
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);
    $this->assertDatabaseCount('recourses', 1);

    $response = $this->actingAs($user)->putJson(route('recourses.update', $recourse), $recourse->attributesToArray());

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

    $this->assertDatabaseHas('recourses', [
      "name" => $recourse->name,
      "source" => $recourse->source,
      "author" => $recourse->author,
      "editorial" => $recourse->editorial,
      "type_id" => $recourse->type_id,
      "total_pages" => $recourse->total_pages,
      "total_chapters" => $recourse->total_chapters,
      "total_videos" => $recourse->total_videos,
      "total_hours" => $recourse->total_hours
    ]);
    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
      ]
    ]);
    $response->assertJsonPath("error.message", "Se debe especificar al menos un valor diferente para actualizar");
  }

  /** @test */
  public function recourse_cannot_be_edited_when_only_have_optionals_values()
  {
    $user = User::factory()->create();
    $recourse = Recourse::factory()->create(["user_id" => $user->id]);

    $this->assertDatabaseCount('recourses', 1);

    $recourseUpdate = $this->recourseValidData([
      "name" => null,
      "source"=>null,
      "type_id" => null,
      "total_pages" => null,
      "total_chapters" => null,
      "total_videos" => null
    ]);

    $response = $this->actingAs($user)->putJson(route('recourses.update', $recourse), $recourseUpdate);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

    $this->assertDatabaseHas('recourses', [
      "name" => $recourse->name,
      "source" => $recourse->source,
      "author" => $recourse->author,
      "editorial" => $recourse->editorial,
      "type_id" => $recourse->type_id,
      "total_pages" => $recourse->total_pages,
      "total_chapters" => $recourse->total_chapters,
      "total_videos" => $recourse->total_videos,
      "total_hours" => $recourse->total_hours
    ]);
    $response->assertJsonStructure([
      "status",
      "code",
      "error" => [
        "message",
        "details"=>["name", "source", "type_id"]
      ]
    ]);
  }
}
