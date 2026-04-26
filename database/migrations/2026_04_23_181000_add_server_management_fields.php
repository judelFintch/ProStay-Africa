<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_server')->default(false)->after('password');
            $table->boolean('server_active')->default(true)->after('is_server');
            $table->string('server_alias')->nullable()->after('server_active');

            $table->index(['is_server', 'server_active']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('served_by')
                ->nullable()
                ->after('created_by')
                ->constrained('users')
                ->nullOnDelete();

            $table->index('served_by');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('served_by');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_server', 'server_active']);
            $table->dropColumn(['is_server', 'server_active', 'server_alias']);
        });
    }
};
