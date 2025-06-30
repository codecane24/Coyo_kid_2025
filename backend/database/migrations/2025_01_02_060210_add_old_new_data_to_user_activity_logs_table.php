<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('user_activity_logs', function (Blueprint $table) {
            $table->text('old_data')->nullable()->after('payload');
            $table->text('new_data')->nullable()->after('old_data');
        });
    }

    public function down()
    {
        Schema::table('user_activity_logs', function (Blueprint $table) {
            $table->dropColumn(['old_data', 'new_data']);
        });
    }
};
