<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('request_input')->nullable();
            $table->date('date');
            $table->string('hn');
            $table->string('name');
            $table->enum('lang',['th','en']);
            $table->string('app')->nullable();
            $table->time('app_time')->nullable();
            $table->string('pre_vn')->nullable();
            $table->boolean('pre_vn_finish')->default(false);
            $table->string('vn')->nullable();
            $table->timestamps();
        });

        Schema::create('patienttasks', function (Blueprint $table) {
            $table->id();
            $table->integer('patient_id');
            $table->date('date');
            $table->string('hn');
            $table->string('vn');
            $table->string('code');
            $table->enum('type',['process','wait','work','success'])->default('process');
            $table->dateTime('assign')->nullable();
            $table->dateTime('call')->nullable();
            $table->integer('call_time')->default(0);
            $table->dateTime('success')->nullable();
            $table->string('memo1')->nullable();
            $table->string('memo2')->nullable();
            $table->string('memo3')->nullable();
            $table->text('memo4')->nullable();
            $table->integer('memo5')->nullable();
            $table->timestamps();
        });

        Schema::create('patientlogs', function (Blueprint $table) {
            $table->id();
            $table->integer('patient_id');
            $table->date('date');
            $table->string('hn');
            $table->string('text');
            $table->string('user');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
        Schema::dropIfExists('patienttasks');
        Schema::dropIfExists('patientlogs');
    }
};
