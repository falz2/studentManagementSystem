<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('users', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }
            if (! Schema::hasColumn('users', 'map_visible')) {
                $table->boolean('map_visible')->default(false);
            }
        });
        if (Schema::hasTable('users') && ! collect(Schema::getIndexes('users'))->contains(fn (array $index): bool => $index['name'] === 'users_map_visible_latitude_longitude_index')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->index(['map_visible', 'latitude', 'longitude']);
            });
        }

        if (! Schema::hasTable('delivery_shares')) {
            Schema::create('delivery_shares', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
                $table->char('token_hash', 64)->unique();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->decimal('accuracy_meters', 8, 2)->nullable();
                $table->timestamp('location_updated_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_shares');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['map_visible', 'latitude', 'longitude']);
            $table->dropColumn(['latitude', 'longitude', 'map_visible']);
        });
    }
};
