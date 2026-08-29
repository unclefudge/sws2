<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_report_messages', function (Blueprint $table) {
            $table->text('attachment_capture_error')->nullable()->after('attachments');
        });

        Schema::create('scheduled_report_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduled_report_message_id')->index();
            $table->string('disk', 80);
            $table->string('path', 700)->unique();
            $table->string('original_name');
            $table->string('content_type')->nullable();
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_report_attachments');
        Schema::table('scheduled_report_messages', function (Blueprint $table) {
            $table->dropColumn('attachment_capture_error');
        });
    }
};
