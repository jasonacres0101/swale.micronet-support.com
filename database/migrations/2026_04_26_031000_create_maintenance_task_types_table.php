<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('maintenance_task_types')) {
            Schema::create('maintenance_task_types', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        $types = [
            'scheduled_camera_inspection' => 'Scheduled camera inspection',
            'lens_cleaning' => 'Lens cleaning',
            'firmware_update' => 'Firmware update',
            'router_sim_check' => 'Router/SIM check',
            'annual_service_report' => 'Annual service report',
            'other' => 'Other',
        ];

        foreach ($types as $index => $name) {
            DB::table('maintenance_task_types')->updateOrInsert(
                ['slug' => $index],
                [
                    'name' => $name,
                    'description' => 'Default maintenance task type.',
                    'is_active' => true,
                    'sort_order' => array_search($index, array_keys($types), true) + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_task_types');
    }
};
