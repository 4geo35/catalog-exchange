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
        Schema::create('cml_file_progress_bars', function (Blueprint $table) {
            $table->uuid('id')->unique();
            $table->uuid("cml_file_id");
            $table->string("type");
            $table->unsignedBigInteger("total")->default(0);
            $table->unsignedBigInteger("pending")->default(0);
            $table->dateTime("completed_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cml_file_progress_bars');
    }
};
