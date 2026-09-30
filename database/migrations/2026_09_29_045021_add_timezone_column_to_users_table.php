<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $column = config('timezone.column_name', 'timezone');
        if (!Schema::hasColumn('users', $column)) {
            Schema::table('users', function (Blueprint $table) use ($column) {
                $table->string($column)->after('remember_token')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $column = config('timezone.column_name', 'timezone');
        Schema::table('users', function (Blueprint $table) use ($column) {
            $table->dropColumn($column);
        });
    }
};
