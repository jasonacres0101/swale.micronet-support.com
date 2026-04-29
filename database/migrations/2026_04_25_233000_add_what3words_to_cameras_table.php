<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cameras', function (Blueprint $table): void {
            if (! Schema::hasColumn('cameras', 'what3words')) {
                $table->string('what3words')->nullable()->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cameras', function (Blueprint $table): void {
            if (Schema::hasColumn('cameras', 'what3words')) {
                $table->dropColumn('what3words');
            }
        });
    }
};
