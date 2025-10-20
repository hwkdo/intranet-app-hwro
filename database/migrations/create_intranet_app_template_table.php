<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('intranet_app_hwro_vorgangs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vorgangsnummer');
            $table->bigInteger('betriebsnr')->nullable()->default(null);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('intranet_app_hwro_vorgangs');
    }
};
