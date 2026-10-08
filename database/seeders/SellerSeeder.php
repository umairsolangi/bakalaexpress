<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seller;
use App\Models\CatalogCategory;

class SellerSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Maintain demo seller for existing system functionality
        $generalStore = CatalogCategory::where('slug', 'general-store')->first()
            ?? CatalogCategory::where('slug', 'grocery-general')->first();

        Seller::updateOrCreate(
            ['email' => 'seller@bakalaexpress.com'],
            [
                'name' => 'Demo Seller',
                'password' => 'seller123',
                'profile_image' => null,
                'city' => 'Karachi',
                'area' => 'Baldia Town',
                'sector' => '4B',
                'near_areas' => ['Ali Chowk', 'Lahori Hotel'],
                'full_address' => 'Shop #5, Sector 4B, near Lahori Hotel, Baldia Town',
                'opens_at' => '07:00:00',
                'closes_at' => '23:00:00',
                'is_open' => true,
                'catalog_category_id' => $generalStore?->id,
                'accountIsApproved' => 1,
                'is_deleted' => false,
            ]
        );

        // 2. Q3 Onboarded Vendor Directory (Sector 4B)
        $vendors = [
            [
                'vendor_id' => '4B-001',
                'name' => 'Saqib General Store',
                'category' => 'Grocery & General',
                'opens_at' => '07:00:00',
                'closes_at' => '01:00:00',
                'sku_scope' => 'FMCG, packaged goods, staples, daily household essentials',
            ],
            [
                'vendor_id' => '4B-002',
                'name' => 'Siddique Bhai General Store',
                'category' => 'Grocery & General',
                'opens_at' => '10:00:00',
                'closes_at' => '01:00:00',
                'sku_scope' => 'FMCG, pantry supplies, packaged retail goods',
            ],
            [
                'vendor_id' => '4B-003',
                'name' => 'Paracha General Store',
                'category' => 'Grocery & General',
                'opens_at' => '07:00:00',
                'closes_at' => '03:00:00',
                'sku_scope' => 'High-availability groceries, late-night essentials',
            ],
            [
                'vendor_id' => '4B-004',
                'name' => 'Ishfaq General Store',
                'category' => 'Grocery & General',
                'opens_at' => '07:00:00',
                'closes_at' => '22:00:00',
                'sku_scope' => 'Standard household consumables, daily dry groceries',
            ],
            [
                'vendor_id' => '4B-005',
                'name' => 'Jawed Fried Items',
                'category' => 'Prepared Foods',
                'opens_at' => '09:00:00',
                'closes_at' => '20:00:00',
                'sku_scope' => 'Live frying station items: Samoosa, Pakoora, Mirchi, Aloo Pakoora, Jalebi',
            ],
            [
                'vendor_id' => '4B-006',
                'name' => 'Manzoor Bakery',
                'category' => 'Dairy & Bakery',
                'opens_at' => '06:00:00',
                'closes_at' => '00:00:00',
                'sku_scope' => 'Fresh milk (Doodh), Yogurt (Dahi), cold drinks, cold milk, biscuits, bakery goods',
            ],
            [
                'vendor_id' => '4B-007',
                'name' => 'Memon Hardwares',
                'category' => 'Hardware & Tools',
                'opens_at' => '08:00:00',
                'closes_at' => '19:00:00',
                'sku_scope' => 'Domestic repair tools, fasteners, electrical and plumbing essentials',
            ],
            [
                'vendor_id' => '4B-008',
                'name' => 'Al Madina Tandoor',
                'category' => 'Daily Essentials',
                'opens_at' => '10:00:00',
                'closes_at' => '00:00:00',
                'sku_scope' => 'Fresh Tandoori Roti, Naan, and traditional flatbreads',
            ],
            [
                'vendor_id' => '4B-009',
                'name' => 'Raheem General Store',
                'category' => 'Grocery & General',
                'opens_at' => '07:00:00',
                'closes_at' => '00:00:00',
                'sku_scope' => 'Standard packaged food items, kitchen provisions, personal care',
            ],
            [
                'vendor_id' => '4B-010',
                'name' => 'Attack Wholesale Egg Shop',
                'category' => 'Wholesale & Bakery',
                'opens_at' => '07:00:00',
                'closes_at' => '22:00:00',
                'sku_scope' => 'Wholesale and retail poultry eggs, commercial baking goods',
            ],
            [
                'vendor_id' => '4B-011',
                'name' => 'Rahil Fruits Shops',
                'category' => 'Fresh Produce',
                'opens_at' => '08:00:00',
                'closes_at' => '22:00:00',
                'sku_scope' => 'Highly perishable inventory management: Daily seasonal fresh fruits',
            ],
            [
                'vendor_id' => '4B-012',
                'name' => 'Lala Tandoor Shop',
                'category' => 'Daily Essentials',
                'opens_at' => '07:00:00',
                'closes_at' => '22:00:00',
                'sku_scope' => 'High-turnover fresh Roti production tracking',
            ],
            [
                'vendor_id' => '4B-013',
                'name' => 'Rehan Hardware',
                'category' => 'Hardware & Tools',
                'opens_at' => '08:00:00',
                'closes_at' => '19:00:00',
                'sku_scope' => 'General construction fixtures, sanitary wares, home hardware accessories',
            ],
            [
                'vendor_id' => '4B-014',
                'name' => 'Mazhar Bakery and Milk Shop',
                'category' => 'Dairy & Bakery',
                'opens_at' => '06:00:00',
                'closes_at' => '00:00:00',
                'sku_scope' => 'Dairy supply chain: Fresh Milk, Yogurt, beverages, shelf-stable bakery items',
            ],
            [
                'vendor_id' => '4B-015',
                'name' => 'Classic Junk Foods',
                'category' => 'Prepared Foods',
                'opens_at' => '16:00:00',
                'closes_at' => '00:00:00',
                'sku_scope' => 'Made-to-order menu items: Burgers, Fries, Zinger, Shawarma',
            ],
            [
                'vendor_id' => '4B-016',
                'name' => 'Mama Bhatti Roti House',
                'category' => 'Daily Essentials',
                'opens_at' => '12:00:00',
                'closes_at' => '00:00:00',
                'sku_scope' => 'Prepared lunch/dinner flatbreads: Roti and Chapati',
            ],
            [
                'vendor_id' => '4B-017',
                'name' => 'Tariq Halwa Puri',
                'category' => 'Prepared Foods',
                'opens_at' => '06:00:00',
                'closes_at' => '13:00:00',
                'sku_scope' => 'High-density breakfast window execution: Halwa Puri, Chana',
            ],
            [
                'vendor_id' => '4B-018',
                'name' => 'Rehan Confectionery Store',
                'category' => 'Wholesale & Snacks',
                'opens_at' => '07:00:00',
                'closes_at' => '21:00:00',
                'sku_scope' => 'Bulk sweet items, candies, packaged snacks at wholesale rates',
            ],
            [
                'vendor_id' => '4B-019',
                'name' => 'Abdullah Vegetables Shop',
                'category' => 'Fresh Produce',
                'opens_at' => '07:00:00',
                'closes_at' => '19:00:00',
                'sku_scope' => 'Daily fresh farm vegetables, leafy greens, root vegetables',
            ],
            [
                'vendor_id' => '4B-020',
                'name' => 'Al Madina Fast Foods',
                'category' => 'Prepared Foods',
                'opens_at' => '16:00:00',
                'closes_at' => '00:00:00',
                'sku_scope' => 'Fast-casual evening food items, snacks, quick-service sides',
            ],
            [
                'vendor_id' => '4B-021',
                'name' => 'Gujjar Milk Shop',
                'category' => 'Dairy & Bakery',
                'opens_at' => '06:00:00',
                'closes_at' => '22:00:00',
                'sku_scope' => 'Raw milk, pasteurized products, yogurts, packaged snacks',
            ],
            [
                'vendor_id' => '4B-022',
                'name' => 'Zaheer General Store',
                'category' => 'Grocery & General',
                'opens_at' => '07:00:00',
                'closes_at' => '22:00:00',
                'sku_scope' => 'Domestic provisioning items, hygiene products, kitchen staples',
            ],
            [
                'vendor_id' => '4B-023',
                'name' => 'Memon Samoosa Corner',
                'category' => 'Prepared Foods',
                'opens_at' => '15:00:00',
                'closes_at' => '19:00:00',
                'sku_scope' => 'Targeted afternoon snack window: Samosa, Pakoora, fried side items',
            ],
            [
                'vendor_id' => '4B-024',
                'name' => 'Ali Gift Shop',
                'category' => 'Novelties & Gifts',
                'opens_at' => '10:00:00',
                'closes_at' => '20:00:00',
                'sku_scope' => 'Non-perishable general gifts, toy SKUs, packaging materials',
            ],
            [
                'vendor_id' => '4B-025',
                'name' => 'Al Qadir Paan Shop',
                'category' => 'Refreshments',
                'opens_at' => '08:00:00',
                'closes_at' => '20:00:00',
                'sku_scope' => 'Prepared traditional Paan variants, oral refreshments, confectionery',
            ],
            [
                'vendor_id' => '4B-026',
                'name' => 'Quetta Ice Depot',
                'category' => 'Daily Essentials',
                'opens_at' => '08:00:00',
                'closes_at' => '20:00:00',
                'sku_scope' => 'Critical cold-chain support: Commercially processed consumable Ice',
            ],
            [
                'vendor_id' => '4B-027',
                'name' => 'Quetta Hotel',
                'category' => 'Prepared Foods',
                'opens_at' => '06:00:00',
                'closes_at' => '00:00:00',
                'sku_scope' => 'Multi-SKU Breakfast & Tea: Paratha variants, eggs/omelettes, tea/coffee',
            ],
            [
                'vendor_id' => '4B-028',
                'name' => 'Raheel Milk Shop',
                'category' => 'Dairy & Bakery',
                'opens_at' => '06:00:00',
                'closes_at' => '22:00:00',
                'sku_scope' => 'Fresh dairy tracking, processed milk variants, fresh Lassi',
            ],
            [
                'vendor_id' => '4B-029',
                'name' => 'Kathiawari Roll Point',
                'category' => 'Prepared Foods',
                'opens_at' => '15:00:00',
                'closes_at' => '22:00:00',
                'sku_scope' => 'Quick-service paratha rolls, kabab rolls, chicken rolls',
            ],
            [
                'vendor_id' => '4B-030',
                'name' => 'Ali Spices',
                'category' => 'Grocery & General',
                'opens_at' => '08:00:00',
                'closes_at' => '22:00:00',
                'sku_scope' => 'Specialized bulk raw spices, packaged spice blends, condiments',
            ],
            [
                'vendor_id' => '4B-031',
                'name' => 'Amjad Gola Ganda',
                'category' => 'Refreshments',
                'opens_at' => '08:00:00',
                'closes_at' => '20:00:00',
                'sku_scope' => 'Seasonal hot-weather items, traditional ice desserts',
            ],
            [
                'vendor_id' => '4B-032',
                'name' => 'Razzaq Pan Shop',
                'category' => 'Refreshments',
                'opens_at' => '08:00:00',
                'closes_at' => '22:00:00',
                'sku_scope' => 'Traditional local refreshments and chewing items',
            ],
            [
                'vendor_id' => '4B-033',
                'name' => 'Zahid Medical Store',
                'category' => 'Health & Pharmacy',
                'opens_at' => '10:00:00',
                'closes_at' => '02:00:00',
                'sku_scope' => 'Highly regulated SKUs: OTC pharmaceuticals, cold drinks, ice creams',
            ],
            [
                'vendor_id' => '4B-034',
                'name' => 'Waqar Chicken Shop',
                'category' => 'Fresh Meat',
                'opens_at' => '08:00:00',
                'closes_at' => '20:00:00',
                'sku_scope' => 'Specialized cold-chain logistics: Raw, freshly processed chicken meat',
            ],
            [
                'vendor_id' => '4B-035',
                'name' => 'Asad Ullah Vegetables',
                'category' => 'Fresh Produce',
                'opens_at' => '07:00:00',
                'closes_at' => '19:00:00',
                'sku_scope' => 'Raw farm produce, green groceries, daily kitchen vegetables',
            ],
            [
                'vendor_id' => '4B-036',
                'name' => 'Mashallah Burger Point',
                'category' => 'Prepared Foods',
                'opens_at' => '16:00:00',
                'closes_at' => '00:00:00',
                'sku_scope' => 'Standardized street-food options: Kabab burgers and Egg (Anda) burgers',
            ],
            [
                'vendor_id' => '4B-037',
                'name' => 'Hafiz Medical Store',
                'category' => 'Health & Pharmacy',
                'opens_at' => '10:00:00',
                'closes_at' => '00:00:00',
                'sku_scope' => 'Critical OTC medicine supplies, medical goods, basic confectionery',
            ],
            [
                'vendor_id' => '4B-038',
                'name' => 'Noor Chicken Shop',
                'category' => 'Fresh Meat',
                'opens_at' => '10:00:00',
                'closes_at' => '20:00:00',
                'sku_scope' => 'Local meat supply: Raw poultry items, custom cuts',
            ],
            [
                'vendor_id' => '4B-039',
                'name' => 'Sharjeel General Store',
                'category' => 'Grocery & General',
                'opens_at' => '08:00:00',
                'closes_at' => '00:00:00',
                'sku_scope' => 'Neighborhood retail goods, cleaning agents, breakfast dry products',
            ],
            [
                'vendor_id' => '4B-040',
                'name' => 'Mazhar Milk Shop',
                'category' => 'Dairy & Bakery',
                'opens_at' => '06:00:00',
                'closes_at' => '22:00:00',
                'sku_scope' => 'Fresh local milk sourcing, yogurts, packaged bread items',
            ],
            [
                'vendor_id' => '4B-041',
                'name' => 'Paracha General Store (B2)',
                'category' => 'Grocery & General',
                'opens_at' => '06:00:00',
                'closes_at' => '03:00:00',
                'sku_scope' => 'Late-night high-turnover grocery lines and rapid fulfillment SKUs',
            ],
        ];

        // Fetch categories cache
        $categoriesMap = CatalogCategory::all()->pluck('id', 'name');

        foreach ($vendors as $v) {
            $email = 'vendor.' . strtolower(str_replace('-', '', $v['vendor_id'])) . '@bakalaexpress.com';
            $categoryId = $categoriesMap[$v['category']] ?? $generalStore?->id;

            Seller::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $v['name'],
                    'password' => 'vendor123',
                    'profile_image' => null,
                    'city' => 'Karachi',
                    'area' => 'Baldia Town',
                    'sector' => '4B',
                    'near_areas' => ['Sector 4B', 'New Saeedabad', 'Baldia Town'],
                    'full_address' => 'Shop Vendor ' . $v['vendor_id'] . ', Sector 4 Block B, New Saeedabad, Baldia Town, Karachi',
                    'opens_at' => $v['opens_at'],
                    'closes_at' => $v['closes_at'],
                    'is_open' => true,
                    'catalog_category_id' => $categoryId,
                    'accountIsApproved' => 1,
                    'is_deleted' => false,
                ]
            );
        }
    }
}