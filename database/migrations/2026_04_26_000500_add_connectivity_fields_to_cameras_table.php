<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cameras', function (Blueprint $table): void {
            $columns = [
                'connectivity_type' => fn () => $table->string('connectivity_type')->default('unknown')->after('what3words'),
                'connectivity_provider' => fn () => $table->string('connectivity_provider')->nullable()->after('connectivity_type'),
                'sim_number' => fn () => $table->string('sim_number')->nullable()->after('connectivity_provider'),
                'sim_iccid' => fn () => $table->string('sim_iccid')->nullable()->after('sim_number'),
                'sim_static_ip' => fn () => $table->string('sim_static_ip')->nullable()->after('sim_iccid'),
                'apn_name' => fn () => $table->string('apn_name')->nullable()->after('sim_static_ip'),
                'router_model' => fn () => $table->string('router_model')->nullable()->after('apn_name'),
                'router_serial' => fn () => $table->string('router_serial')->nullable()->after('router_model'),
                'router_ip_address' => fn () => $table->string('router_ip_address')->nullable()->after('router_serial'),
                'wan_ip_address' => fn () => $table->string('wan_ip_address')->nullable()->after('router_ip_address'),
                'private_apn' => fn () => $table->boolean('private_apn')->default(false)->after('wan_ip_address'),
                'connectivity_notes' => fn () => $table->text('connectivity_notes')->nullable()->after('private_apn'),
            ];

            foreach ($columns as $name => $callback) {
                if (! Schema::hasColumn('cameras', $name)) {
                    $callback();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('cameras', function (Blueprint $table): void {
            $columns = [
                'connectivity_notes',
                'private_apn',
                'wan_ip_address',
                'router_ip_address',
                'router_serial',
                'router_model',
                'apn_name',
                'sim_static_ip',
                'sim_iccid',
                'sim_number',
                'connectivity_provider',
                'connectivity_type',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('cameras', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
