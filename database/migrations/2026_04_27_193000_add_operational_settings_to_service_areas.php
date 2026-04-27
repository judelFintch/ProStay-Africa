<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_areas', function (Blueprint $table) {
            $table->string('manager_name')->nullable()->after('description');
            $table->string('manager_phone')->nullable()->after('manager_name');
            $table->time('opens_at')->nullable()->after('manager_phone');
            $table->time('closes_at')->nullable()->after('opens_at');
            $table->decimal('daily_target_amount', 12, 2)->default(0)->after('closes_at');
            $table->decimal('monthly_budget', 12, 2)->default(0)->after('daily_target_amount');
        });

        DB::table('service_areas')->where('code', 'restaurant')->update([
            'manager_name' => 'Chef de salle',
            'opens_at' => '07:00:00',
            'closes_at' => '23:00:00',
            'daily_target_amount' => 500,
            'monthly_budget' => 12000,
        ]);

        DB::table('service_areas')->where('code', 'bar')->update([
            'manager_name' => 'Responsable Bar',
            'opens_at' => '10:00:00',
            'closes_at' => '23:59:00',
            'daily_target_amount' => 300,
            'monthly_budget' => 7000,
        ]);

        DB::table('service_areas')->where('code', 'terrace')->update([
            'manager_name' => 'Responsable Terrasse',
            'opens_at' => '11:00:00',
            'closes_at' => '22:00:00',
            'daily_target_amount' => 250,
            'monthly_budget' => 6000,
        ]);

        DB::table('service_areas')->where('code', 'accommodation')->update([
            'manager_name' => 'Front Office',
            'opens_at' => '00:00:00',
            'closes_at' => '23:59:00',
            'daily_target_amount' => 0,
            'monthly_budget' => 15000,
        ]);
    }

    public function down(): void
    {
        Schema::table('service_areas', function (Blueprint $table) {
            $table->dropColumn([
                'manager_name',
                'manager_phone',
                'opens_at',
                'closes_at',
                'daily_target_amount',
                'monthly_budget',
            ]);
        });
    }
};