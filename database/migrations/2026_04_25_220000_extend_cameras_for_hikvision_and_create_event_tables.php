<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cameras', function (Blueprint $table): void {
            if (! Schema::hasColumn('cameras', 'mac_address')) {
                $table->string('mac_address')->nullable()->after('ip_address');
            }

            if (! Schema::hasColumn('cameras', 'mac_address_normalized')) {
                $table->string('mac_address_normalized')->nullable()->after('mac_address');
                $table->index('mac_address_normalized');
            }

            if (! Schema::hasColumn('cameras', 'status')) {
                $table->string('status')->default('unknown')->after('longitude');
            }

            if (! Schema::hasColumn('cameras', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('cameras', 'last_event_at')) {
                $table->timestamp('last_event_at')->nullable()->after('last_seen_at');
            }
        });

        if (! Schema::hasTable('hikvision_events')) {
            Schema::create('hikvision_events', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('camera_id')->nullable()->constrained()->nullOnDelete();
                $table->string('source_ip')->nullable();
                $table->string('event_type')->nullable();
                $table->string('event_state')->nullable();
                $table->text('event_description')->nullable();
                $table->timestamp('event_time')->nullable();
                $table->string('mac_address')->nullable();
                $table->string('ip_address')->nullable();
                $table->longText('raw_payload');
                $table->json('parsed_payload')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('camera_status_logs')) {
            Schema::create('camera_status_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('camera_id')->constrained()->cascadeOnDelete();
                $table->string('old_status')->nullable();
                $table->string('new_status');
                $table->string('reason')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('camera_status_logs');
        Schema::dropIfExists('hikvision_events');

        Schema::table('cameras', function (Blueprint $table): void {
            if (Schema::hasColumn('cameras', 'mac_address_normalized')) {
                $table->dropIndex(['mac_address_normalized']);
                $table->dropColumn('mac_address_normalized');
            }

            if (Schema::hasColumn('cameras', 'mac_address')) {
                $table->dropColumn('mac_address');
            }

            if (Schema::hasColumn('cameras', 'status')) {
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('cameras', 'last_event_at')) {
                $table->dropColumn('last_event_at');
            }
        });
    }
};
