<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfid_tags', function (Blueprint $table) {
            $table->id();
            $table->string('reader_id')->index();
            $table->integer('antenna_no')->default(1);
            $table->string('epc')->index(); // Electronic Product Code
            $table->integer('rssi')->nullable(); // Signal strength
            $table->json('raw_payload')->nullable();
            $table->timestamp('read_at')->useCurrent()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_tags');
    }
};
