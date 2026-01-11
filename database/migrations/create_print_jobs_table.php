<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tenant_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('printer'); // Printer name (references qz_printers.name)
            $table->string('type'); // raw, html, pdf, image, zpl, escpos
            $table->json('data'); // Print data/content
            $table->json('options')->nullable(); // Print options
            $table->string('status')->default('queued'); // queued, processing, completed, failed, cancelled
            $table->integer('copies')->default(1);
            $table->string('paper_size')->nullable(); // A4, Letter, Receipt, etc.
            $table->string('orientation')->nullable(); // portrait, landscape
            $table->text('error_message')->nullable();
            $table->string('job_id')->unique()->nullable(); // QZ Tray job ID
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('queued_at')->useCurrent();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('printer');
            $table->index('type');
            $table->index('status');
            $table->index('job_id');
            $table->index('queued_at');
            $table->index(['status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('print_jobs');
    }
};
