<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCelengansTable extends Migration
{
    public function up()
    {
        Schema::create('celengans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('nama');
            $table->string('currency');                   // contoh: Indonesia Rupiah (Rp)
            $table->integer('target');                    // target nominal
            $table->integer('nominal_terkumpul')->default(0);
            $table->string('image_path')->nullable();     // path gambar
            $table->date('target_date');                  // format yyyy-mm-dd
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('celengans');
    }
}
