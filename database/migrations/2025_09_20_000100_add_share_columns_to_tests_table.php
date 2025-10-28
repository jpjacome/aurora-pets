<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShareColumnsToTestsTable extends Migration
{
    public function up()
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->string('share_token', 64)->nullable()->unique()->after('id');
            $table->string('og_image')->nullable()->after('metadata');
            $table->boolean('og_ready')->default(false)->after('og_image');
            $table->index('share_token');
        });
    }

    public function down()
    {
        Schema::table('tests', function (Blueprint $table) {
            $table->dropIndex(['share_token']);
            $table->dropColumn(['share_token','og_image','og_ready']);
        });
    }
}
