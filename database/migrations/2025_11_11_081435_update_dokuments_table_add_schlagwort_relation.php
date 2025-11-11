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
        Schema::table('intranet_app_hwro_dokuments', function (Blueprint $table) {
            $table->dropColumn('schlagwort');
            $table->foreignId('schlagwort_id')->nullable()->constrained('intranet_app_hwro_schlagworts')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intranet_app_hwro_dokuments', function (Blueprint $table) {
            $table->dropForeign(['schlagwort_id']);
            $table->dropColumn('schlagwort_id');
            $table->string('schlagwort');
        });
    }
};
