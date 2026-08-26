<?php

namespace App\Scheduled;

/**
 * Carries the current run id while legacy report code sends mail. Mail event
 * listeners use it to attach each generated email to the correct run history.
 */
class ScheduledRunContext
{
    private ?int $runId = null;

    public function begin(int $runId): void
    {
        $this->runId = $runId;
    }

    public function end(): void
    {
        $this->runId = null;
    }

    public function runId(): ?int
    {
        return $this->runId;
    }
}
