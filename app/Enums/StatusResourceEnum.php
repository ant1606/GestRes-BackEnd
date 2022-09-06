<?php

namespace App\Enums;

enum StatusResourceEnum: string
{
  case STATUS_REGISTRADO = 'REGISTRADO';
  case STATUS_POREMPEZAR = 'POR EMPEZAR';
  case STATUS_ENPROCESO = 'EN PROCESO';
  case STATUS_CULMINADO = 'CULMINADO';
  case STATUS_DESCARTADO = 'DESCARTADO';
  case STATUS_DESFASADO = 'DESFASADO';
}
