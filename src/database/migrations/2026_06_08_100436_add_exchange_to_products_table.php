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
        Schema::table('products', function (Blueprint $table) {
            $table->uuid("exchange_uuid")
                ->nullable()
                ->after("id");

            $table->uuid("cml_id")
                ->nullable()
                ->after("exchange_uuid");

            $table->uuid("exchange_category")
                ->nullable()
                ->after("cml_id");

            $table->json("exchange_variation")
                ->nullable()
                ->after("exchange_category");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn("exchange_uuid");
            $table->dropColumn("cml_id");
            $table->dropColumn("exchange_category");
            $table->dropColumn("exchange_variation");
        });
    }
};
