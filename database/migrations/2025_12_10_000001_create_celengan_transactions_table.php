<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('celengan_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('celengan_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['deposit', 'withdraw']);
            $table->integer('amount');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->foreign('celengan_id')->references('id')->on('celengans')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('celengan_transactions');
    }
};


