<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_app_hwro_schlagworts', function (Blueprint $table) {
            $table->string('belegtyp_hr')->nullable()->after('filenames');
        });

        DB::table('intranet_app_hwro_schlagworts')
            ->where('schlagwort', 'Handelsregisterauszug')
            ->update(['belegtyp_hr' => 'Handelsregisterverfahren']);
    }

    public function down(): void
    {
        Schema::table('intranet_app_hwro_schlagworts', function (Blueprint $table) {
            $table->dropColumn('belegtyp_hr');
        });
    }
};
