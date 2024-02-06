<?php

  namespace App\Http\Services;

  use App\Models\Recourse;
  use App\Models\StatusHistory;
  use Exception;
  use Symfony\Component\HttpFoundation\Response;

  class StatusHistoryService
  {
    /**
     * @throws Exception
     */
    public function save_status(Recourse $recourse, array $statusRequest)
    {
      /*TODO ver si la validacion de la fecha se puede realizar en un formRequest
        ver esta documentacion https://stackoverflow.com/questions/20953525/laravel-4-validation-afterdate-get-date-from-database
         */
      $lastStatus = $recourse->status->last();

      if ($statusRequest["date"] < $lastStatus->date)
        throw new Exception("La fecha ingresada no es correcta", Response::HTTP_UNPROCESSABLE_ENTITY);

      return StatusHistory::create([
        'recourse_id' => $recourse->id,
        'status_id' => $statusRequest['status_id'],
        'date' => $statusRequest['date'],
        'comment' => $statusRequest['comment']
      ]);
    }

    /**
     * @throws Exception
     */
    public function delete_status(StatusHistory $statusHistory): StatusHistory
    {
      $recourse = $statusHistory->recourse;

      if ($statusHistory->comment === "REGISTRO INICIAL GENERADO AUTOMATICAMENTE POR EL SISTEMA" && $statusHistory->id === $recourse->status->first()->id) {
        throw new Exception("Acción prohibida, No esta permitido eliminar el registro generado por el sistema", Response::HTTP_UNPROCESSABLE_ENTITY);
      }

      if ($statusHistory->id !== $recourse->status->last()->id) {
        throw new Exception("Acción prohibida, sólo puede eliminarse el último registro de estados del recurso", Response::HTTP_UNPROCESSABLE_ENTITY);
      }

      $statusHistory->delete();
      return $statusHistory;
    }
  }
