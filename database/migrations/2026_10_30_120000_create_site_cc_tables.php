<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_cc', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('site_id');
            $table->unsignedInteger('super_id')->nullable();
            $table->unsignedInteger('supervisor_sign_by')->nullable();
            $table->timestamp('supervisor_sign_at')->nullable();
            $table->unsignedInteger('manager_sign_by')->nullable();
            $table->timestamp('manager_sign_at')->nullable();
            $table->tinyInteger('status')->default(2);
            $table->string('stage')->default('Upcoming');
            $table->dateTime('cc_requested')->nullable();
            $table->date('cc_received')->nullable();
            $table->date('final_photos_rcvd')->nullable();
            $table->boolean('portal_fee_paid')->nullable();
            $table->boolean('wbo_waiting')->nullable();
            foreach (['certifier', 'certifier_phone', 'pca_name', 'pca_email', 'inspections_email', 'notes'] as $field) {
                $table->text($field)->nullable();
            }
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->index(['stage', 'status']);
            $table->index('super_id');
        });

        Schema::create('site_cc_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cc_id');
            $table->text('name')->nullable();
            $table->text('notes')->nullable();
            $table->string('category_id')->nullable();
            $table->unsignedInteger('order')->nullable();
            $table->unsignedInteger('assigned_to')->nullable();
            $table->unsignedInteger('planner_id')->nullable();
            $table->unsignedInteger('sign_by')->nullable();
            $table->timestamp('sign_at')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('cc_id')->references('id')->on('site_cc')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_cc_items');
        Schema::dropIfExists('site_cc');
    }
};
