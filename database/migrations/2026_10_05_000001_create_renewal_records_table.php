<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renewal_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained()->cascadeOnDelete();
            $table->date('previous_expiry_date')->nullable();
            $table->date('new_expiry_date')->nullable();
            $table->string('decision')->index();
            $table->date('decision_date');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['agreement_id', 'decision_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renewal_records');
    }
};
