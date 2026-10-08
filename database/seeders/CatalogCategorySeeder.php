<?php

namespace Database\Seeders;

use App\Models\CatalogCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'General Store' => 'Daily essentials, pantry staples, snacks, and household items.',
            'Grocery & General' => 'FMCG, pantry supplies, packaged retail goods, and household consumables.',
            'Prepared Foods' => 'Ready-to-eat meals, street foods, fast foods, halwa puri, and snacks.',
            'Dairy & Bakery' => 'Fresh milk, yogurt, eggs, bread, and bakery goods.',
            'Hardware & Tools' => 'Domestic repair tools, fasteners, electrical, plumbing, and sanitary essentials.',
            'Daily Essentials' => 'Tandoori roti, naan, flatbreads, ice, and daily staple provisions.',
            'Wholesale & Bakery' => 'Wholesale poultry eggs, commercial baking goods, and bulk inventory.',
            'Fresh Produce' => 'Fresh fruits, farm-fresh vegetables, leafy greens, and seasonal items.',
            'Wholesale & Snacks' => 'Bulk sweet items, candies, and packaged wholesale snacks.',
            'Novelties & Gifts' => 'Non-perishable general gifts, toy SKUs, and packaging materials.',
            'Refreshments' => 'Traditional Paan variants, cold beverages, ice desserts, and oral refreshments.',
            'Health & Pharmacy' => 'OTC pharmaceuticals, medical goods, health supplies, and beverages.',
            'Fresh Meat' => 'Freshly processed poultry, raw chicken meat, and custom cuts.',
        ];

        foreach ($categories as $name => $description) {
            CatalogCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => $description,
                    'is_active' => true,
                ]
            );
        }
    }
}
