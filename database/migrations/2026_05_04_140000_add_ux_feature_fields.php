<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            if (!Schema::hasColumn('sellers', 'opens_at')) {
                $table->time('opens_at')->nullable()->after('full_address');
            }
            if (!Schema::hasColumn('sellers', 'closes_at')) {
                $table->time('closes_at')->nullable()->after('opens_at');
            }
            if (!Schema::hasColumn('sellers', 'is_open')) {
                $table->boolean('is_open')->default(true)->after('closes_at');
            }
        });

        Schema::table('feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('feedback', 'rating')) {
                $table->unsignedTinyInteger('rating')->default(5)->after('feedback');
            }
            if (!Schema::hasColumn('feedback', 'status')) {
                $table->string('status')->default('pending')->after('rating');
            }
            if (!Schema::hasColumn('feedback', 'moderated_at')) {
                $table->timestamp('moderated_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('feedback', 'moderated_by')) {
                $table->foreignId('moderated_by')->nullable()->after('moderated_at')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'estimated_delivery_at')) {
                $table->timestamp('estimated_delivery_at')->nullable()->after('inventory_reserved_at');
            }
            if (!Schema::hasColumn('orders', 'delivery_proof_image')) {
                $table->string('delivery_proof_image')->nullable()->after('estimated_delivery_at');
            }
            if (!Schema::hasColumn('orders', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('delivery_proof_image');
            }
            if (!Schema::hasColumn('orders', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('cancelled_at');
            }
            if (!Schema::hasColumn('orders', 'promo_code_id')) {
                $table->unsignedBigInteger('promo_code_id')->nullable()->after('transaction_id');
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('promo_code_id');
            }
        });

        if (!Schema::hasTable('favorites')) {
            Schema::create('favorites', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('seller_id')->nullable()->constrained('sellers')->cascadeOnDelete();
                $table->foreignId('shop_product_id')->nullable()->constrained('shop_product')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['user_id', 'seller_id']);
                $table->unique(['user_id', 'shop_product_id']);
            });
        }

        if (!Schema::hasTable('promo_codes')) {
            Schema::create('promo_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('discount_type')->default('fixed');
                $table->decimal('discount_value', 10, 2);
                $table->decimal('minimum_order_amount', 10, 2)->default(0);
                $table->decimal('maximum_discount_amount', 10, 2)->nullable();
                $table->unsignedInteger('usage_limit')->nullable();
                $table->unsignedInteger('used_count')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('promo_codes');
    }
};
