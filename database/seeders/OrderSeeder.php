<?php

namespace Database\Seeders;

use App\Models\GlobalProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Seed the orders table with orders in every possible status.
     *
     * Statuses: pending, confirmed_by_seller, preparing, ready_for_pickup,
     *           assigned_to_rider, picked_up, delivered, cancelled, rejected, completed
     */
    public function run(): void
    {
        // ── Resolve demo records created by other seeders ────────────
        $user   = User::where('email', 'user@bakalaexpress.com')->first();
        $seller = Seller::where('email', 'seller@bakalaexpress.com')->first();
        $rider  = Rider::where('email', 'rider@bakalaexpress.com')->first();

        if (!$user || !$seller) {
            $this->command->warn('OrderSeeder skipped – Demo user or seller not found. Run UserSeeder & SellerSeeder first.');
            return;
        }

        // Grab the seller's shop products so order items can reference them
        $shopProducts = ShopProduct::where('seller_id', $seller->id)
            ->with('globalProduct')
            ->get();

        if ($shopProducts->isEmpty()) {
            $this->command->warn('OrderSeeder skipped – No shop products found for the demo seller. Run GlobalProductSeeder first.');
            return;
        }

        // ── Define one order per status ──────────────────────────────
        $statuses = [
            [
                'status'  => 'pending',
                'note'    => 'Just placed, waiting for seller to accept.',
                'rider'   => false,
                'days_ago' => 0,
            ],
            [
                'status'  => 'confirmed_by_seller',
                'note'    => 'Seller has confirmed this order.',
                'rider'   => false,
                'days_ago' => 1,
            ],
            [
                'status'  => 'preparing',
                'note'    => 'Seller is currently preparing the order.',
                'rider'   => false,
                'days_ago' => 2,
            ],
            [
                'status'  => 'ready_for_pickup',
                'note'    => 'Order is packed and waiting for rider.',
                'rider'   => false,
                'days_ago' => 3,
            ],
            [
                'status'  => 'assigned_to_rider',
                'note'    => 'Rider has been assigned to pick up the order.',
                'rider'   => true,
                'days_ago' => 4,
            ],
            [
                'status'  => 'picked_up',
                'note'    => 'Rider has picked up the order from the seller.',
                'rider'   => true,
                'days_ago' => 5,
            ],
            [
                'status'  => 'delivered',
                'note'    => 'Rider has delivered the order to the customer.',
                'rider'   => true,
                'days_ago' => 7,
            ],
            [
                'status'  => 'completed',
                'note'    => 'Order has been completed and finalized.',
                'rider'   => true,
                'days_ago' => 10,
            ],
            [
                'status'  => 'cancelled',
                'note'    => 'Customer cancelled this order.',
                'rider'   => false,
                'days_ago' => 6,
            ],
            [
                'status'  => 'rejected',
                'note'    => 'Seller rejected this order.',
                'rider'   => false,
                'days_ago' => 8,
            ],
        ];

        $deliveryCharges = 50.00;

        foreach ($statuses as $index => $entry) {
            // Pick 2–3 random shop products for this order
            $itemCount    = min($shopProducts->count(), rand(2, 3));
            $pickedItems  = $shopProducts->random($itemCount);

            // Calculate item totals
            $itemsTotal = 0;
            $itemsData  = [];

            foreach ($pickedItems as $shopProduct) {
                $qty   = rand(1, 4);
                $price = $shopProduct->effective_price;

                $itemsTotal += $price * $qty;

                $itemsData[] = [
                    'shop_product_id'   => $shopProduct->id,
                    'global_product_id' => $shopProduct->global_product_id,
                    'product_id'        => null,
                    'item_name'         => $shopProduct->globalProduct->name ?? 'Product',
                    'unit_type'         => $shopProduct->globalProduct->unit_type ?? 'Piece',
                    'item_image'        => $shopProduct->globalProduct->default_image ?? null,
                    'quantity'          => $qty,
                    'price'             => $price,
                ];
            }

            $totalAmount = $itemsTotal + $deliveryCharges;

            // Build order attributes
            $orderData = [
                'user_id'          => $user->id,
                'seller_id'        => $seller->id,
                'rider_id'         => ($entry['rider'] && $rider) ? $rider->id : null,
                'address'          => $user->address ?? 'House #12, Sector 4B, Baldia Town',
                'phone'            => $user->mobile ?? '03007654321',
                'status'           => $entry['status'],
                'total_amount'     => $totalAmount,
                'delivery_charges' => $deliveryCharges,
                'transaction_id'   => 'TXN-SEED-' . strtoupper($entry['status']) . '-' . ($index + 1),
                'created_at'       => now()->subDays($entry['days_ago']),
                'updated_at'       => now()->subDays($entry['days_ago']),
            ];

            // Add extra fields for specific statuses
            if ($entry['status'] === 'cancelled') {
                $orderData['cancelled_at']        = now()->subDays($entry['days_ago']);
                $orderData['cancellation_reason']  = 'Customer changed their mind.';
            }

            if (in_array($entry['status'], ['delivered', 'completed'])) {
                $orderData['estimated_delivery_at'] = now()->subDays($entry['days_ago'])->addHours(2);
            }

            if (in_array($entry['status'], ['preparing', 'ready_for_pickup', 'assigned_to_rider', 'picked_up', 'delivered', 'completed'])) {
                $orderData['inventory_reserved_at'] = now()->subDays($entry['days_ago'])->addMinutes(5);
            }

            // Use updateOrCreate keyed on transaction_id so the seeder is idempotent
            $order = Order::updateOrCreate(
                ['transaction_id' => $orderData['transaction_id']],
                $orderData
            );

            // Seed order items (clear existing first for idempotency)
            $order->items()->delete();

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }
        }

        $this->command->info('OrderSeeder: 10 orders created (one per status) with order items.');
    }
}
