<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cameras', function (Blueprint $table): void {
            if (! Schema::hasColumn('cameras', 'serial_number')) {
                $table->string('serial_number')->nullable()->after('mac_address_normalized');
            }

            if (! Schema::hasColumn('cameras', 'serial_number_normalized')) {
                $table->string('serial_number_normalized')->nullable()->after('serial_number');
                $table->unique('serial_number_normalized');
            }
        });

        if (! Schema::hasTable('camera_email_snapshots')) {
            Schema::create('camera_email_snapshots', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('camera_id')->nullable()->constrained()->nullOnDelete();
                $table->string('message_uid')->unique();
                $table->string('serial_number')->nullable()->index();
                $table->string('from_email')->nullable();
                $table->string('subject')->nullable();
                $table->string('attachment_path')->nullable();
                $table->string('attachment_name')->nullable();
                $table->string('attachment_mime')->nullable();
                $table->unsignedInteger('attachment_size')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamp('imported_at');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('camera_email_snapshots');

        Schema::table('cameras', function (Blueprint $table): void {
            if (Schema::hasColumn('cameras', 'serial_number_normalized')) {
                $table->dropUnique(['serial_number_normalized']);
                $table->dropColumn('serial_number_normalized');
            }

            if (Schema::hasColumn('cameras', 'serial_number')) {
                $table->dropColumn('serial_number');
            }
        });
    }
};
