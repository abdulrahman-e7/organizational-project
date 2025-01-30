<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webinar_translations', function (Blueprint $table) {
            //
            $table->longText('requirements')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webinar_translations', function (Blueprint $table) {
            //
            $table->dropColumn('requirements');
        });
    }
};
