<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'currency')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('currency', 3)->default('USD')->after('total');
                $table->index('currency');
            });
        }

        if (! Schema::hasColumn('invoices', 'currency')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('currency', 3)->default('USD')->after('balance');
                $table->index('currency');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'currency')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex(['currency']);
                $table->dropColumn('currency');
            });
        }

        if (Schema::hasColumn('invoices', 'currency')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex(['currency']);
                $table->dropColumn('currency');
            });
        }
    }
};
