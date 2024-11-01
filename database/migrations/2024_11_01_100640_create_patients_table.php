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
            $table->date('visitdate');
            $table->string('name');
            $table->enum('lang',['th','en']);
            $table->string('hn');
            $table->string('vn')->nullable();
            $table->string('pre_vn')->nullable();
            $table->boolean('finish')->default(false);
            $table->json('logs');
            $table->timestamps();
        });

        Schema::create('patienttasks', function (Blueprint $table) {
            $table->id();
            $table->integer('patient_id');
            $table->string('code');
            $table->dateTime('call');
            $table->dateTime('success');
            $table->text('memo1')->nullable();
            $table->text('memo2')->nullable();
            $table->text('memo3')->nullable();
            $table->text('memo4')->nullable();
            $table->text('memo5')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
        Schema::dropIfExists('patienttasks');
    }
};
