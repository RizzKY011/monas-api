<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('celengans', function (Blueprint $table) {
            $table->string('plan')->default('Harian')->after('target_date');
            $table->integer('nominal_pengisian')->default(0)->after('plan');
            $table->boolean('notif_on')->default(false)->after('nominal_pengisian');
            $table->string('notif_day')->nullable()->after('notif_on');
            $table->string('notif_time')->nullable()->after('notif_day');
            $table->timestamp('completed_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('celengans', function (Blueprint $table) {
            $table->dropColumn([
                'plan',
                'nominal_pengisian',
                'notif_on',
                'notif_day',
                'notif_time',
                'completed_at',
            ]);
        });
    }
};


