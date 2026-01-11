<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('smart_print_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('url');
            $table->string('type')->default('auto'); // pdf, html, image, raw, auto
            $table->string('printer')->nullable();
            $table->integer('copies')->default(1);
            $table->string('status'); // processing, success, failed
            $table->string('method_used')->nullable(); // qz, browser, download
            $table->text('error_message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('method_used');
            $table->index('created_at');

            if (Schema::hasTable('users')) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('smart_print_logs');
    }
};
