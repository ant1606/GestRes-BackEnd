<?php

  namespace App\Helpers;

  use App\Enums\TagStyleEnum;

  class TagHelper
  {
    public static function getRandomTagStyle(): int|array|string
    {
      return array_rand([
        TagStyleEnum::TAG_STYLE_BLUE->value => 1,
        TagStyleEnum::TAG_STYLE_EMERALD->value => 1,
        TagStyleEnum::TAG_STYLE_GREEN->value => 1,
        TagStyleEnum::TAG_STYLE_INDIGO->value => 1,
        TagStyleEnum::TAG_STYLE_LIME->value => 1,
        TagStyleEnum::TAG_STYLE_ORANGE->value => 1,
        TagStyleEnum::TAG_STYLE_PINK->value => 1,
        TagStyleEnum::TAG_STYLE_PURPLE->value => 1,
        TagStyleEnum::TAG_STYLE_RED->value => 1,
        TagStyleEnum::TAG_STYLE_ROSE->value => 1,
        TagStyleEnum::TAG_STYLE_SKY->value => 1,
        TagStyleEnum::TAG_STYLE_TEAL->value => 1,
        TagStyleEnum::TAG_STYLE_YELLOW->value => 1,
        TagStyleEnum::TAG_STYLE_GRAY->value => 1
      ]);
    }
  }
