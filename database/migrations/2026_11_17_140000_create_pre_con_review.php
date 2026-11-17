<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->string('plans_by_others')->nullable();
            $table->string('ausgrid_quote_required')->nullable();
            $table->text('project_notes')->nullable();
            $table->string('fj_draftsperson')->nullable();
            $table->string('concrete_engineer')->nullable();
            $table->date('fj_start_date')->nullable();
            $table->date('fj_received_date')->nullable();
            $table->date('fj_scheduled_received_date')->nullable();
            $table->date('concrete_sent_date')->nullable();
            $table->date('concrete_received_date')->nullable();
            $table->date('concrete_scheduled_received_date')->nullable();
        });
        Schema::create('site_pre_con', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('site_id')->unique();
            $table->unsignedInteger('super_id')->nullable();
            $table->unsignedInteger('supervisor_sign_by')->nullable();
            $table->timestamp('supervisor_sign_at')->nullable();
            $table->unsignedInteger('manager_sign_by')->nullable();
            $table->timestamp('manager_sign_at')->nullable();
            $table->tinyInteger('status')->default(2);
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by');
            $table->timestamps();
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
            $table->index('status');
            $table->index('super_id');
        });

        Schema::create('site_pre_con_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('review_id');
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
            $table->foreign('review_id')->references('id')->on('site_pre_con')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_pre_con_items');
        Schema::dropIfExists('site_pre_con');
        Schema::table('sites', fn (Blueprint $table) => $table->dropColumn(['plans_by_others', 'ausgrid_quote_required', 'project_notes', 'fj_draftsperson', 'concrete_engineer', 'fj_start_date', 'fj_received_date', 'fj_scheduled_received_date', 'concrete_sent_date', 'concrete_received_date', 'concrete_scheduled_received_date']));
    }
};
