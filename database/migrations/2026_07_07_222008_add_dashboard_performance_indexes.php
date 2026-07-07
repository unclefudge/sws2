<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDashboardPerformanceIndexes extends Migration
{
    public function up()
    {
        Schema::table('site_attendance', function (Blueprint $table) {
            $table->index(['site_id', 'user_id', 'date'], 'site_attendance_site_user_date_idx');
            $table->index(['user_id', 'date'], 'site_attendance_user_date_idx');
        });

        Schema::table('todo', function (Blueprint $table) {
            $table->index(['type', 'status', 'due_at'], 'todo_type_status_due_at_idx');
        });

        Schema::table('todo_user', function (Blueprint $table) {
            $table->index(['user_id', 'todo_id'], 'todo_user_user_todo_idx');
        });

        Schema::table('permission_user', function (Blueprint $table) {
            $table->index(['user_id', 'company_id', 'permission_id'], 'permission_user_user_company_permission_idx');
        });

        Schema::table('permission_role', function (Blueprint $table) {
            $table->index(['company_id', 'permission_id', 'role_id'], 'permission_role_company_permission_role_idx');
        });

        Schema::table('site_hazards', function (Blueprint $table) {
            $table->index(['status', 'site_id'], 'site_hazards_status_site_idx');
            $table->index(['status', 'created_by'], 'site_hazards_status_created_by_idx');
        });

        Schema::table('site_accidents', function (Blueprint $table) {
            $table->index(['status', 'site_id'], 'site_accidents_status_site_idx');
            $table->index(['status', 'created_by'], 'site_accidents_status_created_by_idx');
        });

        Schema::table('site_incidents', function (Blueprint $table) {
            $table->index(['status', 'site_id'], 'site_incidents_status_site_idx');
            $table->index(['status', 'created_by'], 'site_incidents_status_created_by_idx');
        });

        Schema::table('site_docs', function (Blueprint $table) {
            $table->index(['site_id', 'type', 'status'], 'site_docs_site_type_status_idx');
        });
    }

    public function down()
    {
        Schema::table('site_attendance', function (Blueprint $table) {
            $table->dropIndex('site_attendance_site_user_date_idx');
            $table->dropIndex('site_attendance_user_date_idx');
        });

        Schema::table('todo', function (Blueprint $table) {
            $table->dropIndex('todo_type_status_due_at_idx');
        });

        Schema::table('todo_user', function (Blueprint $table) {
            $table->dropIndex('todo_user_user_todo_idx');
        });

        Schema::table('permission_user', function (Blueprint $table) {
            $table->dropIndex('permission_user_user_company_permission_idx');
        });

        Schema::table('permission_role', function (Blueprint $table) {
            $table->dropIndex('permission_role_company_permission_role_idx');
        });

        Schema::table('site_hazards', function (Blueprint $table) {
            $table->dropIndex('site_hazards_status_site_idx');
            $table->dropIndex('site_hazards_status_created_by_idx');
        });

        Schema::table('site_accidents', function (Blueprint $table) {
            $table->dropIndex('site_accidents_status_site_idx');
            $table->dropIndex('site_accidents_status_created_by_idx');
        });

        Schema::table('site_incidents', function (Blueprint $table) {
            $table->dropIndex('site_incidents_status_site_idx');
            $table->dropIndex('site_incidents_status_created_by_idx');
        });

        Schema::table('site_docs', function (Blueprint $table) {
            $table->dropIndex('site_docs_site_type_status_idx');
        });
    }
}