<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Optional overrides live separately from the code registry. This means a
        // deploy can add new jobs without inserting seed data into production.
        Schema::create('scheduled_task_settings', function (Blueprint $table) {
            $table->id();
            $table->string('task_key')->unique();
            $table->boolean('enabled')->default(true);
            $table->json('schedule_override')->nullable();
            $table->json('recipient_override')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('scheduled_dispatch_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->string('mode', 20)->unique();
            $table->dateTime('last_checked_at')->nullable();
            $table->dateTime('last_success_at')->nullable();
            $table->timestamps();
        });

        Schema::create('scheduled_run_groups', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('trigger', 30)->index();
            $table->string('mode', 20);
            $table->string('status', 30)->index();
            $table->dateTime('scheduled_for')->index();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('expected_count')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->unsignedInteger('skip_count')->default(0);
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->dateTime('alert_sent_at')->nullable();
            $table->text('alert_error')->nullable();
            $table->timestamps();
        });

        Schema::create('scheduled_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduled_run_group_id')->nullable()->index();
            $table->unsignedBigInteger('retry_of_id')->nullable()->index();
            $table->string('task_key')->index();
            $table->string('task_name');
            $table->string('category', 40)->index();
            $table->string('trigger', 30)->index();
            $table->string('status', 30)->index();
            $table->dateTime('scheduled_for')->index();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedSmallInteger('attempt')->default(0);
            $table->longText('output')->nullable();
            $table->string('exception_class')->nullable();
            $table->text('exception_message')->nullable();
            $table->string('exception_file')->nullable();
            $table->unsignedInteger('exception_line')->nullable();
            $table->longText('exception_trace')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->string('lock_key')->nullable()->unique();
            $table->dateTime('failure_notified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('scheduled_report_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduled_run_id')->index();
            $table->uuid('uuid')->unique();
            $table->string('status', 30)->index();
            $table->string('subject')->nullable();
            $table->longText('html_body')->nullable();
            $table->longText('text_body')->nullable();
            $table->json('attachments')->nullable();
            $table->string('provider_message_id')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });

        Schema::create('scheduled_report_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduled_report_message_id')->index();
            $table->string('type', 10);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email')->index();
            $table->string('name')->nullable();
            $table->string('source')->nullable();
            $table->string('status', 30)->default('resolved');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_report_recipients');
        Schema::dropIfExists('scheduled_report_messages');
        Schema::dropIfExists('scheduled_runs');
        Schema::dropIfExists('scheduled_run_groups');
        Schema::dropIfExists('scheduled_dispatch_heartbeats');
        Schema::dropIfExists('scheduled_task_settings');
    }
};
