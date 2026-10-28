<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 3)->unique();
            $table->string('symbol', 10);
            $table->decimal('exchange_rate', 18, 8);
            $table->timestamps();
        });

        DB::table('currencies')->insert([
            [
                'name' => 'USD',
                'symbol' => '$',
                'exchange_rate' => 1.00000000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'EUR',
                'symbol' => '€',
                'exchange_rate' => 1.08000000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
