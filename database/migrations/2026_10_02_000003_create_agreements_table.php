<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->string('title');
            $table->foreignId('partner_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('responsible_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('agreement_type')->default('MoU')->index();
            $table->text('purpose')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable()->index();
            $table->string('status')->default('draft')->index();
            $table->string('approval_stage')->nullable();
            $table->string('renewal_status')->default('not_due')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
