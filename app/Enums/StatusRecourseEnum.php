<?php

namespace App\Enums;

enum StatusRecourseEnum: string
{
  case STATUS_REGISTRADO = 'REGISTRADO';
  case STATUS_POREMPEZAR = 'POR EMPEZAR';
  case STATUS_ENPROCESO = 'EN PROCESO';
  case STATUS_CULMINADO = 'CULMINADO';
  case STATUS_DESCARTADO = 'DESCARTADO';
  case STATUS_DESFASADO = 'DESFASADO';
}

enum StatusRecourseStyleEnum: string
{
  case STATUS_REGISTRADO = 'bg-gray-900 text-gray-50';
  case STATUS_POREMPEZAR = 'bg-yellow-400 text-gray-900';
  case STATUS_ENPROCESO = 'bg-blue-500 text-gray-50';
  case STATUS_CULMINADO = 'bg-green-800 text-gray-50';
  case STATUS_DESCARTADO = 'bg-red-700 text-gray-50';
  case STATUS_DESFASADO = 'bg-gray-300 text-gray-900';

  public static function fromName(string $name): string
  {
    foreach (self::cases() as $status) {
      if ($name === $status->name) {
        return $status->value;
      }
    }
    throw new \ValueError("$name is not a valid backing value for enum " . self::class);
  }
}
