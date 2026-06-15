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
        Schema::table('product_variations', function (Blueprint $table) {
            $table->uuid("exchange_uuid")
                ->nullable()
                ->after("id");

            $table->uuid("cml_id")
                ->nullable()
                ->after("exchange_uuid");

            $table->uuid("exchange_product")
                ->nullable()
                ->after("cml_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            //
        });
    }
};
