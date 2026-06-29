<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('website_form_submissions', function (Blueprint $table) {
            $table->id();

            // Public-safe reference used in the hidden form field.
            $table->uuid('uuid')->unique();

            // Allows this table to support many different website/contact forms.
            $table->string('form_key')->index(); // request_designer_visit

            // step1_complete, rejected, submitted_before_zoho, zoho_created, zoho_failed
            $table->string('status')->default('started')->index();

            $table->unsignedTinyInteger('step')->default(1);

            // Main searchable fields.
            $table->string('email')->nullable()->index();
            $table->string('full_name')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('suburb')->nullable()->index();
            $table->string('postcode', 10)->nullable()->index();
            $table->string('state', 10)->nullable();

            // Why they did not qualify, or why something failed.
            $table->string('rejection_reason')->nullable();

            // Zoho tracking.
            $table->string('zoho_lead_id')->nullable()->index();
            $table->string('zoho_status')->nullable();

            // Store the full form data without needing a database column for every field.
            $table->json('payload')->nullable();
            $table->json('zoho_response')->nullable();

            // Useful for admin/audit/debugging.
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_form_submissions');
    }
};