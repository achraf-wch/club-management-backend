<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->string('instagram_url')->nullable()->after('mission');
            $table->string('linkedin_url')->nullable()->after('instagram_url');
            $table->string('facebook_url')->nullable()->after('linkedin_url');
            $table->string('contact_email')->nullable()->after('facebook_url');
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropColumn([
                'instagram_url',
                'linkedin_url',
                'facebook_url',
                'contact_email',
            ]);
        });
    }
};
