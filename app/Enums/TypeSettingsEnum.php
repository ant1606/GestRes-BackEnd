<?php

namespace App\Enums;

enum TypeSettingsEnum: string
{
  case SETTINGS_TYPE = TypeRecourseEnum::class;
  case SETTINGS_STATUS = StatusRecourseEnum::class;
  case SETTINGS_UNIT_MEASURE_PROGRESS = UnitMeasureProgressEnum::class;
}
