<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('besoins', function (Blueprint $table) {
            // Stocke les seuils déjà notifiés, ex: "25,50"
            $table->string('seuils_notifies', 50)
                ->nullable()
                ->after('taux_couverture');
        });
    }

    public function down(): void
    {
        Schema::table('besoins', function (Blueprint $table) {
            $table->dropColumn('seuils_notifies');
        });
    }
};