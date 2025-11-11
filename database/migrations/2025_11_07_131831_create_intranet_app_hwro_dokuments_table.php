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
        Schema::create('intranet_app_hwro_dokuments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vorgang_id')->constrained('intranet_app_hwro_vorgangs')->cascadeOnDelete();
            $table->string('schlagwort');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intranet_app_hwro_dokuments');
    }
};
