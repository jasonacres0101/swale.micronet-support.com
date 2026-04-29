<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organisations')) {
            Schema::create('organisations', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('type')->default('other');
                $table->string('contact_name')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sites')) {
            Schema::create('sites', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('address_line_1')->nullable();
                $table->string('address_line_2')->nullable();
                $table->string('town')->nullable();
                $table->string('postcode')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('what3words')->nullable();
                $table->string('permit_to_dig_number')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        $cameraColumns = [];

        if (! Schema::hasColumn('cameras', 'site_id')) {
            $cameraColumns[] = 'site_id';
        }

        if (! Schema::hasColumn('cameras', 'ownership_type')) {
            $cameraColumns[] = 'ownership_type';
        }

        if (! Schema::hasColumn('cameras', 'managed_by_council')) {
            $cameraColumns[] = 'managed_by_council';
        }

        if ($cameraColumns !== []) {
            Schema::table('cameras', function (Blueprint $table) use ($cameraColumns): void {
                if (in_array('site_id', $cameraColumns, true)) {
                    $table->foreignId('site_id')->nullable()->after('site_name')->constrained('sites')->nullOnDelete();
                }

                if (in_array('ownership_type', $cameraColumns, true)) {
                    $table->string('ownership_type')->default('council')->after('site_id');
                }

                if (in_array('managed_by_council', $cameraColumns, true)) {
                    $table->boolean('managed_by_council')->default(true)->after('ownership_type');
                }
            });
        }

        $fallbackOrganisationId = DB::table('organisations')
            ->where('name', 'Unassigned Organisation')
            ->value('id');

        if (! $fallbackOrganisationId) {
            $fallbackOrganisationId = DB::table('organisations')->insertGetId([
                'name' => 'Unassigned Organisation',
                'type' => 'other',
                'notes' => 'Created automatically while backfilling legacy camera records.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $legacySites = DB::table('cameras')
            ->select('site_name')
            ->selectRaw('MAX(latitude) as latitude')
            ->selectRaw('MAX(longitude) as longitude')
            ->selectRaw('MAX(what3words) as what3words')
            ->whereNotNull('site_name')
            ->where(function ($query): void {
                $query->whereNull('site_id')
                    ->orWhere('site_id', 0);
            })
            ->groupBy('site_name')
            ->get();

        foreach ($legacySites as $legacySite) {
            $siteId = DB::table('sites')
                ->where('organisation_id', $fallbackOrganisationId)
                ->where('name', $legacySite->site_name)
                ->value('id');

            if (! $siteId) {
                $siteId = DB::table('sites')->insertGetId([
                    'organisation_id' => $fallbackOrganisationId,
                    'name' => $legacySite->site_name,
                    'latitude' => $legacySite->latitude,
                    'longitude' => $legacySite->longitude,
                    'what3words' => $legacySite->what3words,
                    'notes' => 'Imported from legacy camera site names.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('cameras')
                ->where('site_name', $legacySite->site_name)
                ->where(function ($query): void {
                    $query->whereNull('site_id')
                        ->orWhere('site_id', 0);
                })
                ->update([
                    'site_id' => $siteId,
                    'ownership_type' => DB::raw("COALESCE(ownership_type, 'council')"),
                    'managed_by_council' => DB::raw('COALESCE(managed_by_council, 1)'),
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('cameras', 'site_id') || Schema::hasColumn('cameras', 'ownership_type') || Schema::hasColumn('cameras', 'managed_by_council')) {
            Schema::table('cameras', function (Blueprint $table): void {
                if (Schema::hasColumn('cameras', 'site_id')) {
                    $table->dropConstrainedForeignId('site_id');
                }

                if (Schema::hasColumn('cameras', 'ownership_type')) {
                    $table->dropColumn('ownership_type');
                }

                if (Schema::hasColumn('cameras', 'managed_by_council')) {
                    $table->dropColumn('managed_by_council');
                }
            });
        }

        Schema::dropIfExists('sites');
        Schema::dropIfExists('organisations');
    }
};
