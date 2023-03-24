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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('guests_adults');
            $table->unsignedInteger('guests_children');
            $table->unsignedInteger('total_price');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
