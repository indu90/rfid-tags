<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rfid_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->string('epc')->index();
            $table->unsignedBigInteger('asset_id')->nullable()->index();
            $table->string('reader_id', 100)->default('unknown')->index();
            $table->string('reader_name')->nullable();
            $table->unsignedTinyInteger('antenna_no')->default(1);
            $table->decimal('rssi', 8, 2)->nullable();
            $table->enum('status', ['IN', 'OUT'])->nullable();
            $table->unsignedBigInteger('location_id')->nullable()->index();
            $table->json('raw_payload')->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_scan_logs');
    }
};
