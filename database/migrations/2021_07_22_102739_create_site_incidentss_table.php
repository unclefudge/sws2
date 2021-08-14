<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteIncidentssTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        // Site Incidents
        //
        Schema::create('site_incidents', function (Blueprint $table) {

            $table->increments('id');

            // Notification details
            $table->integer('site_id')->unsigned()->nullable();
            $table->string('site_name', 266)->nullable();
            $table->string('site_supervisor', 100)->nullable();

            $table->dateTime('date')->nullable();
            //$table->string('type', 255)->nullable();
            $table->text('location')->nullable();
            $table->text('describe')->nullable();
            $table->text('actions_taken')->nullable();


            // Details of Incident completed by WHS
            $table->tinyInteger('risk_potential')->nullable();
            $table->tinyInteger('risk_actual')->nullable();
            $table->text('exec_summary')->nullable();
            $table->text('exec_describe')->nullable();
            $table->text('exec_actions')->nullable();
            $table->text('exec_notes')->nullable();
            $table->tinyInteger('notifiable')->nullable();
            $table->text('notifiable_reason')->nullable();
            $table->string('regulator', 255)->nullable();
            $table->string('regulator_ref', 50)->nullable();
            $table->dateTime('regulator_date')->nullable();
            $table->string('inspector', 255)->nullable();


            // Details of Injury
            //$table->string('treatment', 50)->nullable();
            //$table->string('treatment_other', 255)->nullable();
            //$table->string('injured_part')->nullable();
            //$table->string('injured_part_other')->nullable();
            //$table->string('injured_nature')->nullable();
            //$table->string('injured_mechanism')->nullable();
            //$table->string('injured_agency')->nullable();

            // Details of Damage
            $table->text('damage')->nullable();
            $table->string('damage_cost', 50)->nullable();
            $table->text('damage_repair')->nullable();

            // Investigation
            //$table->string('conditions')->nullable();
            //$table->string('con_weather')->nullable();
            //$table->string('con_environment')->nullable();
            //$table->string('con_equipment')->nullable();
            //$table->string('con_testing')->nullable();
            //$table->string('con_risk')->nullable();
            //$table->string('con_physiology')->nullable();
            //$table->string('con_training')->nullable();
            //$table->string('con_supervision')->nullable();
            //$table->string('con_communication')->nullable();
            //$table->string('con_procedures')->nullable();
            //$table->string('con_previous')->nullable();

            // Contributing factors
            //$table->string('factors_absent')->nullable();
            //$table->string('factors_actions')->nullable();
            //$table->string('factors_workplace')->nullable();
            //$table->string('factors_human')->nullable();
            //$table->string('root_cause')->nullable();

            // Review
            $table->tinyInteger('risk_register')->nullable();

            $table->text('notes')->nullable();
            $table->integer('company_id')->unsigned()->nullable();
            $table->tinyInteger('step')->default(2);
            $table->tinyInteger('status')->default(1);
            $table->dateTime('resolved_at')->nullable();

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        //
        // Attachments
        //
        Schema::create('site_incidents_docs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('incident_id')->unsigned()->nullable();
            $table->string('type', 50)->nullable();
            $table->string('category', 50)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('attachment', 255)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            $table->foreign('incident_id')->references('id')->on('site_incidents')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        //
        // Persons Involved
        //
        Schema::create('site_incidents_people', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('incident_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('type')->unsigned()->nullable();
            $table->string('type_other', 255)->nullable();

            $table->string('name', 100)->nullable();
            $table->string('address')->nullable();
            $table->string('contact', 100)->nullable();
            $table->dateTime('dob')->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('engagement', 150)->nullable();
            $table->string('employer', 150)->nullable();
            $table->string('supervisor', 150)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            $table->foreign('incident_id')->references('id')->on('site_incidents')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        //
        // Witness Statements
        //
        Schema::create('site_incidents_witness', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('incident_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('name', 100)->nullable();
            $table->text('event')->nullable();
            $table->text('event_before')->nullable();
            $table->text('event_after')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            $table->foreign('incident_id')->references('id')->on('site_incidents')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        //
        // Record of Conversation
        //
        Schema::create('site_incidents_conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('incident_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('name', 255)->nullable();
            $table->string('witness', 255)->nullable();
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
            $table->text('details')->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            $table->foreign('incident_id')->references('id')->on('site_incidents')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        //
        // Investigation Actions
        //
        Schema::create('site_incidents_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('incident_id')->unsigned()->nullable();
            $table->integer('cause_id')->unsigned()->nullable();
            $table->text('action')->nullable();
            $table->integer('assigned_to')->unsigned()->nullable();
            $table->integer('completed_by')->unsigned()->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            $table->foreign('incident_id')->references('id')->on('site_incidents')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });

        //
        // Incident Review Actions
        //
        Schema::create('site_incidents_review', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('incident_id')->unsigned()->nullable();
            $table->string('role', 100)->nullable();
            $table->text('comments')->nullable();
            $table->integer('assigned_to')->unsigned()->nullable();
            $table->integer('sign_by')->unsigned()->nullable();
            $table->dateTime('sign_at')->nullable();
            $table->tinyInteger('status')->default(1);

            // Foreign keys
            $table->foreign('incident_id')->references('id')->on('site_incidents')->onDelete('cascade');

            // Modify info
            $table->integer('created_by')->unsigned();
            $table->integer('updated_by')->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('site_incidents_review');
        Schema::dropIfExists('site_incidents_actions');
        Schema::dropIfExists('site_incidents_conversations');
        Schema::dropIfExists('site_incidents_witness');
        Schema::dropIfExists('site_incidents_people');
        Schema::dropIfExists('site_incidents_docs');
        Schema::dropIfExists('site_incidents');
    }
}
