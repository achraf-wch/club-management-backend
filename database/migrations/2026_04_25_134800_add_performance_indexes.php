<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('club_members', function (Blueprint $table) {
            $table->index(['club_id', 'status']);
            $table->index(['club_id', 'role', 'status']);
            $table->index(['person_id', 'status']);
        });
    }

    public function down()
    {
        Schema::table('club_members', function (Blueprint $table) {
            $table->dropIndex(['club_id', 'status']);
            $table->dropIndex(['club_id', 'role', 'status']);
            $table->dropIndex(['person_id', 'status']);
        });
    }
};