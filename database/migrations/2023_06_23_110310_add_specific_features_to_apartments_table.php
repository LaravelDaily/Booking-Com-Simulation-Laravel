<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->boolean('wheelchair_access')->default(false);
            $table->boolean('pets_allowed')->default(false);
            $table->boolean('smoking_allowed')->default(false);
            $table->boolean('free_cancellation')->default(false);
            $table->boolean('all_day_access')->default(false);
        });
    }
};
