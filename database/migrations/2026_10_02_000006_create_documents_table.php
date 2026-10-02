<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['draft_mou','reviewed_mou','signed_mou','supporting_document','correspondence','other'])->default('supporting_document');
            $table->unsignedInteger('version')->default(1);
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('path');
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_final')->default(false);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->index(['agreement_id','type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};