<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'organisation_id')) {
                $table->foreignId('organisation_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('organisations')
                    ->nullOnDelete();
            }
        });

        DB::table('users')->where('role', 'operator')->update(['role' => 'council_operator']);
        DB::table('users')->where('role', 'viewer')->update(['role' => 'auditor']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'council_operator')->update(['role' => 'operator']);
        DB::table('users')->where('role', 'auditor')->update(['role' => 'viewer']);

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'organisation_id')) {
                $table->dropConstrainedForeignId('organisation_id');
            }
        });
    }
};
