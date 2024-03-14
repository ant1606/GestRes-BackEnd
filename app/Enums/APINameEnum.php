<?php

namespace App\Enums;

enum APINameEnum: string{
  case API_YOUTUBE = 'API_YOUTUBE';
}

enum APILimitRateEnum: int{
  case API_YOUTUBE = 10000;

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
