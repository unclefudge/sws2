<?php

namespace Tests\Unit;

use App\Scheduled\Exceptions\MissingScheduledRecipients;
use App\Scheduled\Reports\OverdueTodoReport;
use App\Scheduled\ScheduledReportMailer;
use Illuminate\Mail\Mailable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class OverdueTodoRecipientHandlingTest extends TestCase
{
    private function report(array $outcomes): OverdueTodoReport
    {
        $mailer = new class($outcomes) extends ScheduledReportMailer {
            private array $outcomes;
            public function __construct(array $outcomes) { $this->outcomes = $outcomes; }
            public function send(Mailable $mailable, array $dynamicRecipients = []): void
            {
                $outcome = array_shift($this->outcomes);
                if ($outcome instanceof \Throwable) throw $outcome;
            }
        };
        // These tests exercise the report's send boundary without a database,
        // actual mail transport or dynamic resolver dependency.
        $reflection = new ReflectionClass(OverdueTodoReport::class);
        $report = $reflection->newInstanceWithoutConstructor();
        $property = $reflection->getProperty('mailer');
        $property->setAccessible(true);
        $property->setValue($report, $mailer);
        return $report;
    }

    private function send(OverdueTodoReport $report, string $description): bool
    {
        $method = new ReflectionMethod(OverdueTodoReport::class, 'sendNotification');
        $method->setAccessible(true);
        return $method->invoke($report, new class extends Mailable {}, [], $description);
    }

    public function test_missing_recipient_does_not_stop_later_messages_and_is_not_counted_as_sent(): void
    {
        $report = $this->report([null, new MissingScheduledRecipients('nightly.overdue_todos'), null]);
        ob_start();
        try {
            $sent = 0;
            foreach (['ToDo [1]', 'ToDo [2]', 'ToDo [3]'] as $description) {
                if ($this->send($report, $description)) $sent++;
            }
            $output = ob_get_contents();
        } finally { ob_end_clean(); }
        $this->assertSame(2, $sent);
        $this->assertStringContainsString('Skipped ToDo [2]', $output);
        $this->assertStringNotContainsString('Skipped ToDo [3]', $output);
    }

    public function test_missing_management_recipient_is_also_skipped(): void
    {
        $report = $this->report([new MissingScheduledRecipients('nightly.overdue_todos')]);
        ob_start();
        try {
            $sent = $this->send($report, 'management notification for toolbox [4]');
            $output = ob_get_contents();
        } finally { ob_end_clean(); }
        $this->assertFalse($sent);
        $this->assertStringContainsString('toolbox [4]', $output);
    }

    public function test_real_mail_or_configuration_errors_are_not_swallowed(): void
    {
        $report = $this->report([new \RuntimeException('Mail transport failed')]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Mail transport failed');
        $this->send($report, 'ToDo [5]');
    }

    public function test_a_missing_recipient_exception_for_another_operation_is_not_swallowed(): void
    {
        $report = $this->report([new MissingScheduledRecipients('another.operation')]);
        $this->expectException(MissingScheduledRecipients::class);
        $this->send($report, 'ToDo [6]');
    }
    public function test_generic_errors_with_similar_text_are_not_mistaken_for_skippable_recipients(): void
    {
        $report = $this->report([new \RuntimeException('No valid automatic or configured To address')]);
        $this->expectException(\RuntimeException::class);
        $this->send($report, 'ToDo [7]');
    }

    public function test_recipient_context_is_restored_after_a_missing_recipient_exception(): void
    {
        $context = new \App\Scheduled\ScheduledDynamicRecipientContext();
        $context->begin([['key' => 'previous', 'type' => 'to', 'email' => 'previous@example.com']]);
        $before = $context->all();
        try {
            $context->run([['key' => 'missing', 'type' => 'to', 'email' => null]], function () {
                throw new MissingScheduledRecipients('nightly.overdue_todos');
            });
            $this->fail('Expected missing-recipient exception.');
        } catch (MissingScheduledRecipients $exception) {
            $this->assertSame('nightly.overdue_todos', $exception->taskKey);
        }
        $this->assertSame($before, $context->all());
        $this->assertSame('previous@example.com', $context->resolved()[0]['email']);
    }
}
