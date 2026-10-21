<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planner_closures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('type', 32)->default('shutdown');
            $table->dateTime('date');
            $table->date('end_date')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
        });

        // If any planner closures were saved with the first package, physically
        // move them out of statutory holiday data. Public holiday rows stay intact.
        DB::transaction(function () {
            $entries = DB::table('public_holidays')->whereIn('type', ['shutdown', 'other'])->lockForUpdate()->get();
            foreach ($entries as $entry) {
                DB::table('planner_closures')->insert([
                    'name' => $entry->name, 'type' => $entry->type, 'date' => $entry->date,
                    'end_date' => $entry->end_date, 'status' => $entry->status,
                    'created_by' => $entry->created_by, 'updated_by' => $entry->updated_by,
                    'created_at' => $entry->created_at, 'updated_at' => $entry->updated_at,
                ]);
                DB::table('public_holidays')->where('id', $entry->id)->delete();
            }
        });
    }

    public function down(): void
    {
        if (DB::table('planner_closures')->exists()) {
            throw new \RuntimeException('Planner closures exist. Export or remove them before rollback; they must not be copied into statutory public holidays.');
        }
        Schema::dropIfExists('planner_closures');
    }
};
