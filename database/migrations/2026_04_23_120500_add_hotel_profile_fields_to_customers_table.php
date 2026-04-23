<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('guest_code')->nullable()->unique()->after('id');
            $table->string('title', 20)->nullable()->after('guest_code');
            $table->string('preferred_name')->nullable()->after('full_name');
            $table->string('gender', 20)->nullable()->after('preferred_name');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('place_of_birth')->nullable()->after('date_of_birth');
            $table->string('nationality')->nullable()->after('place_of_birth');
            $table->string('secondary_phone')->nullable()->after('phone');
            $table->string('profession')->nullable()->after('email');
            $table->string('company_name')->nullable()->after('profession');
            $table->string('preferred_language', 20)->nullable()->after('company_name');
            $table->string('identity_document_type', 50)->nullable()->after('identity_document');
            $table->string('identity_document_issue_place')->nullable()->after('identity_document_type');
            $table->date('identity_document_issued_at')->nullable()->after('identity_document_issue_place');
            $table->date('identity_document_expires_at')->nullable()->after('identity_document_issued_at');
            $table->string('city')->nullable()->after('country');
            $table->string('emergency_contact_name')->nullable()->after('address');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_phone');
            $table->text('guest_preferences')->nullable()->after('emergency_contact_relationship');
            $table->text('internal_notes')->nullable()->after('guest_preferences');
            $table->string('marketing_source')->nullable()->after('internal_notes');
            $table->boolean('vip_status')->default(false)->after('marketing_source');
            $table->boolean('blacklisted')->default(false)->after('vip_status');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'guest_code',
                'title',
                'preferred_name',
                'gender',
                'date_of_birth',
                'place_of_birth',
                'nationality',
                'secondary_phone',
                'profession',
                'company_name',
                'preferred_language',
                'identity_document_type',
                'identity_document_issue_place',
                'identity_document_issued_at',
                'identity_document_expires_at',
                'city',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship',
                'guest_preferences',
                'internal_notes',
                'marketing_source',
                'vip_status',
                'blacklisted',
            ]);
        });
    }
};
