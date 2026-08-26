<?php

namespace App\Scheduled\Contracts;

/**
 * Implement this contract in app/Scheduled/Operations for a new report or
 * maintenance task. The dashboard discovers the class automatically; its
 * timing, display details and recipients are then managed in the database.
 */
interface ScheduledOperationHandler
{
    /**
     * Return key, name, category, description, schedule and recipient summary.
     * The metadata supplies safe first-use defaults, not permanent scheduling.
     */
    public static function scheduledOperation(): array;

    public function handle(): mixed;
}
