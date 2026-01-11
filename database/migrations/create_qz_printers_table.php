<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('qz_printers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tenant_id')->nullable();
            $table->string('name')->unique();
            $table->string('type')->nullable(); // label, receipt, laser, inkjet, virtual, etc.
            $table->string('connection_type')->nullable(); // usb, network, bluetooth, virtual, shared
            $table->string('driver')->nullable();
            $table->string('port')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('status')->default('offline'); // online, offline, error, maintenance
            $table->boolean('is_default')->default(false);
            $table->json('capabilities')->nullable(); // JSON array of printer capabilities
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('last_used')->nullable();
            $table->integer('error_count')->default(0);
            $table->json('metadata')->nullable(); // Additional printer metadata
            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('connection_type');
            $table->index('status');
            $table->index('is_default');
            $table->index('last_seen');
        });
    }

    public function down()
    {
        Schema::dropIfExists('qz_printers');
    }
};
