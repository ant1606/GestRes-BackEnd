<?php
  namespace App\DTOs\ProgressHistory;

  use App\Models\ProgressHistory;

  class ProgressSaveResult{
    public function __construct(
      public bool $success,
      public ?ProgressHistory $progress,
      public ?string $errorMessage = null
    ){}

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
      return $this->success;
    }

    /**
     * @return ProgressHistory|null
     */
    public function getProgress(): ?ProgressHistory
    {
      return $this->progress;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
      return $this->errorMessage;
    }

  }
