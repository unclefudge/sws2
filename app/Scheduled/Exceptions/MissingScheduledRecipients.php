<?php

namespace App\Scheduled\Exceptions;

use RuntimeException;

/** A scheduled message has no usable To address after applying recipient rules. */
class MissingScheduledRecipients extends RuntimeException
{
    public function __construct(public readonly string $taskKey)
    {
        parent::__construct("Scheduled operation [$taskKey] has no valid automatic or configured To address.");
    }
}
