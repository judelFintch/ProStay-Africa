<?php

use App\Enums\ReservationStatus;
use App\Enums\RoomStatus;
use App\Enums\StayStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->nullable();
            $table->string('phone')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('identity_document')->nullable();
            $table->string('country')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_identified')->default(true);
            $table->timestamps();
        });

        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->decimal('base_price', 12, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->restrictOnDelete();
            $table->string('number')->unique();
            $table->string('floor')->nullable();
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->decimal('price', 12, 2);
            $table->string('status')->default(RoomStatus::Available->value);
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->string('status')->default(ReservationStatus::Pending->value);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'check_in_date']);
        });

        Schema::create('stays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('check_in_at');
            $table->dateTime('expected_check_out_at')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->string('status')->default(StayStatus::Active->value);
            $table->decimal('nightly_rate', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'check_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stays');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('customers');
    }
};
