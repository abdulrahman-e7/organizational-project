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
        Schema::table('bundle_student', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('class_id')->nullable()->default(null);
            $table->foreign('class_id')->references('id')->on('study_classes')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bundle_student', function (Blueprint $table) {
            //
            $table->dropForeign('bundle_student_class_id_foreign');
            $table->dropColumn('class_id');
        });
    }
};
