<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('maintenance_tasks')) {
            Schema::create('maintenance_tasks', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('organisation_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('camera_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('task_type');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('status')->default('scheduled');
                $table->string('priority')->default('normal');
                $table->date('scheduled_for')->nullable();
                $table->dateTime('due_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->string('recurrence_type')->nullable();
                $table->unsignedInteger('recurrence_interval')->nullable();
                $table->dateTime('next_due_at')->nullable();
                $table->text('notes')->nullable();
                $table->text('engineer_recommendations')->nullable();
                $table->text('completion_notes')->nullable();
                $table->foreignId('recurring_source_id')->nullable()->constrained('maintenance_tasks')->nullOnDelete();
                $table->timestamp('recurrence_generated_at')->nullable();
                $table->timestamps();

                $table->index(['status', 'due_at']);
                $table->index(['task_type', 'due_at']);
            });
        }

        if (! Schema::hasTable('maintenance_task_attachments')) {
            Schema::create('maintenance_task_attachments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('maintenance_task_id')->constrained()->cascadeOnDelete();
                $table->string('filename');
                $table->string('path');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_task_attachments');
        Schema::dropIfExists('maintenance_tasks');
    }
};
