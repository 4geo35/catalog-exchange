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
        Schema::create('cml_files', function (Blueprint $table) {
            $table->uuid("id")
                ->unique();
            $table->uuid("cml_id");
            $table->unsignedBigInteger("file_id")->nullable();
            $table->string("original_name");

            $table->dateTime("started_at")->nullable();
            $table->dateTime("launched_at")->nullable();
            $table->dateTime("completed_at")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cml_files');
    }
};
