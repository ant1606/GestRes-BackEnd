<?php

namespace App\Enums;

enum TypeSettingsEnum: string
{
  case SETTINGS_TYPE = TypeRecourseEnum::class;
  case SETTINGS_STATUS = StatusRecourseEnum::class;
}
