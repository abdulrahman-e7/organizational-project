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
        Schema::table('certificates_templates', function (Blueprint $table) {
            //
            $table->date('graduation_date')->nullable()->after('font_size_course');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificates_templates', function (Blueprint $table) {
            //
            $table->dropColumn('graduation_date');
        });
    }
};
