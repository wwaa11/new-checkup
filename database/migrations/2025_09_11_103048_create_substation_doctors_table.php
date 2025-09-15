<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('substation_doctors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("substation_id");
            $table->string("doctor_code");
            $table->string("doctor_name");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('substation_doctors');
    }
};
