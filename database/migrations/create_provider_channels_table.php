<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A provider channel is one payment provider account or channel: the driver that talks to
     * the provider, the provider's own code for the channel, and the driver's settings
     * (credentials, endpoints and the like), stored encrypted.
     */
    public function up(): void
    {
        Schema::create('provider_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('driver')->index();
            $table->string('code')->nullable();
            $table->text('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_channels');
    }
};
