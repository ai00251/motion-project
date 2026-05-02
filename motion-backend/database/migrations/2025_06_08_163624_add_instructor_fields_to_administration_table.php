<?php
// database/migrations/xxxx_add_instructor_fields_to_administration_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInstructorFieldsToAdministrationTable extends Migration
{
    public function up()
    {
        Schema::table('administration', function (Blueprint $table) {
            $table->string('photo_url')->nullable()->after('position_type');
            $table->text('bio_description')->nullable()->after('photo_url');
        });
    }

    public function down()
    {
        Schema::table('administration', function (Blueprint $table) {
            $table->dropColumn(['photo_url', 'bio_description']);
        });
    }
}
