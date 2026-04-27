<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Champs descriptifs supplémentaires sur les chambres ──────────────
        Schema::table('rooms', function (Blueprint $table) {
            $table->text('description')->nullable()->after('status');
            $table->decimal('surface_m2', 6, 1)->nullable()->after('description');
            // single | double | twin | triple | king | queen | suite
            $table->string('bed_type', 30)->nullable()->after('surface_m2');
            // sea | pool | garden | city | street | mountain | courtyard | none
            $table->string('view_type', 30)->nullable()->after('bed_type');
            $table->boolean('smoking')->default(false)->after('view_type');
            // Équipements standards (checkboxes)
            $table->boolean('has_private_bathroom')->default(true)->after('smoking');
            $table->boolean('has_air_conditioning')->default(true)->after('has_private_bathroom');
            $table->boolean('has_wifi')->default(true)->after('has_air_conditioning');
            $table->boolean('has_tv')->default(true)->after('has_wifi');
            $table->boolean('has_balcony')->default(false)->after('has_tv');
            $table->boolean('has_kitchenette')->default(false)->after('has_balcony');
            $table->boolean('has_safe')->default(false)->after('has_kitchenette');
            $table->boolean('has_minibar')->default(false)->after('has_safe');
            $table->boolean('extra_bed_available')->default(false)->after('has_minibar');
            $table->text('internal_notes')->nullable()->after('extra_bed_available');
        });

        // ── Table des prestations/bénéfices ──────────────────────────────────
        // Exemples : Petit-déjeuner, Navette aéroport, Dîner inclus, Welcome drink...
        Schema::create('benefits', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // Libellé affiché (ex: "Petit-déjeuner")
            $table->string('code')->unique(); // Clé technique (ex: "breakfast")
            $table->string('icon', 10)->nullable();  // Emoji ou code icône
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Pivot : prestations incluses par chambre ─────────────────────────
        Schema::create('benefit_room', function (Blueprint $table) {
            $table->foreignId('benefit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            // Nombre d'unités incluses par séjour (ex: 2 petits-déj pour une chambre double)
            $table->unsignedTinyInteger('quantity_per_stay')->default(1);
            $table->primary(['benefit_id', 'room_id']);
        });

        // ── Pivot : prestations rattachées à des plats/menus ─────────────────
        // Un bénéfice peut être réalisé via 1 ou plusieurs plats du menu
        Schema::create('benefit_menu', function (Blueprint $table) {
            $table->foreignId('benefit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->primary(['benefit_id', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benefit_menu');
        Schema::dropIfExists('benefit_room');
        Schema::dropIfExists('benefits');

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'surface_m2', 'bed_type', 'view_type', 'smoking',
                'has_private_bathroom', 'has_air_conditioning', 'has_wifi', 'has_tv',
                'has_balcony', 'has_kitchenette', 'has_safe', 'has_minibar',
                'extra_bed_available', 'internal_notes',
            ]);
        });
    }
};
