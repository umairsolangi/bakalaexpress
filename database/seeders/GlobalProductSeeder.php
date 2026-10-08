<?php

namespace Database\Seeders;

use App\Models\CatalogCategory;
use App\Models\GlobalProduct;
use App\Models\Seller;
use Illuminate\Database\Seeder;

class GlobalProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryProducts = [
            'Grocery & General' => [
                ['name' => 'Olpers Milk 1L', 'description' => 'Fresh milk carton for daily home use.', 'base_price' => 280, 'unit_type' => 'Carton', 'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Dawn Bread Large', 'description' => 'Fresh bread loaf for breakfast and snacks.', 'base_price' => 150, 'unit_type' => 'Loaf', 'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Farm Fresh Eggs', 'description' => 'A dozen fresh eggs.', 'base_price' => 400, 'unit_type' => 'Dozen', 'image' => 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Lays Masala', 'description' => 'Popular snack packet.', 'base_price' => 80, 'unit_type' => 'Packet', 'image' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Coca Cola 1.5L', 'description' => 'Soft drink bottle.', 'base_price' => 180, 'unit_type' => 'Bottle', 'image' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Shan Biryani Masala', 'description' => 'Authentic recipe spice mix (50g).', 'base_price' => 120, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'National Red Chili Powder (200g)', 'description' => 'Pure ground red chili powder.', 'base_price' => 240, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Habib Banaspati Cooking Oil (1L)', 'description' => 'Premium refined cooking oil.', 'base_price' => 520, 'unit_type' => 'Pouch', 'image' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Guard Basmati Rice (1kg)', 'description' => 'Long grain aromatic basmati rice.', 'base_price' => 380, 'unit_type' => 'Kg', 'image' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Refined White Sugar (1kg)', 'description' => 'Pure fine crystal sugar.', 'base_price' => 160, 'unit_type' => 'Kg', 'image' => 'https://images.unsplash.com/photo-1622484210800-2d88049f7b19?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Tapal Danedar Tea (450g)', 'description' => 'Strong black tea blend.', 'base_price' => 680, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Surf Excel Washing Powder (1kg)', 'description' => 'Stain removal detergent powder.', 'base_price' => 490, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1585421514284-efb74c2b69ba?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Dettol Soap (Pack of 3)', 'description' => 'Germ protection bar soap.', 'base_price' => 320, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1607006482142-2d10459c5d12?q=80&w=500&auto=format&fit=crop'],
            ],
            'General Store' => [
                ['name' => 'Olpers Milk 1L', 'description' => 'Fresh milk carton for daily home use.', 'base_price' => 280, 'unit_type' => 'Carton', 'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Dawn Bread Large', 'description' => 'Fresh bread loaf for breakfast and snacks.', 'base_price' => 150, 'unit_type' => 'Loaf', 'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Farm Fresh Eggs', 'description' => 'A dozen fresh eggs.', 'base_price' => 400, 'unit_type' => 'Dozen', 'image' => 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Tapal Danedar Tea (450g)', 'description' => 'Strong black tea blend.', 'base_price' => 680, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?q=80&w=500&auto=format&fit=crop'],
            ],
            'Prepared Foods' => [
                ['name' => 'Potato Samoosa', 'description' => 'Live fried crispy potato samosa.', 'base_price' => 40, 'unit_type' => 'Piece', 'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Mix Pakora (250g)', 'description' => 'Hot crispy vegetable pakoras.', 'base_price' => 120, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Mirchi Pakora (250g)', 'description' => 'Spicy fried chili pakoras.', 'base_price' => 140, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1626777552726-4a6b54c97e46?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Aloo Pakora (250g)', 'description' => 'Crispy potato slice pakoras.', 'base_price' => 130, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Fresh Hot Jalebi (250g)', 'description' => 'Traditional sweet crispy jalebi.', 'base_price' => 150, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1589301760014-d929f3979dbc?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Crispy Zinger Burger', 'description' => 'Fried chicken zinger with mayo sauce.', 'base_price' => 320, 'unit_type' => 'Burger', 'image' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Special Anda Kabab Burger', 'description' => 'Street style egg & shami kabab burger.', 'base_price' => 180, 'unit_type' => 'Burger', 'image' => 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Chicken Paratha Roll', 'description' => 'Fresh grilled chicken mayo roll.', 'base_price' => 220, 'unit_type' => 'Roll', 'image' => 'https://images.unsplash.com/photo-1561758033-d8f587294801?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Beef Kabab Paratha Roll', 'description' => 'Juicy grilled beef kabab roll.', 'base_price' => 240, 'unit_type' => 'Roll', 'image' => 'https://images.unsplash.com/photo-1626777552726-4a6b54c97e46?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Halwa Puri Breakfast Deal', 'description' => '2 Puris with Halwa and Chana Curry.', 'base_price' => 250, 'unit_type' => 'Deal', 'image' => 'https://images.unsplash.com/photo-1626777552726-4a6b54c97e46?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Special Spicy Chana Salan', 'description' => 'Traditional breakfast chickpeas curry.', 'base_price' => 120, 'unit_type' => 'Plate', 'image' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Doodh Patti Chaye Cup', 'description' => 'Hot rich Karak tea.', 'base_price' => 70, 'unit_type' => 'Cup', 'image' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?q=80&w=500&auto=format&fit=crop'],
            ],
            'Dairy & Bakery' => [
                ['name' => 'Fresh Buffalo Milk (1L)', 'description' => 'Pure fresh unpasteurized dairy milk.', 'base_price' => 220, 'unit_type' => 'Liter', 'image' => 'https://images.unsplash.com/photo-1563636619-e9143da7973b?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Fresh Dahi / Yogurt (500g)', 'description' => 'Creamy thick fresh yogurt.', 'base_price' => 140, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Chilled Meetha Lassi (Glass)', 'description' => 'Traditional chilled sweet lassi.', 'base_price' => 150, 'unit_type' => 'Glass', 'image' => 'https://images.unsplash.com/photo-1553787499-6f9133860278?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Chilled Flavored Milk Bottle (250ml)', 'description' => 'Flavored cold milk.', 'base_price' => 90, 'unit_type' => 'Bottle', 'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Crispy Cake Rusk (250g)', 'description' => 'Fresh bakery crispy cake rusk.', 'base_price' => 180, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Bakery Cream Roll (Pack of 2)', 'description' => 'Sweet cream filled rolls.', 'base_price' => 100, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Plain Bakery Fruit Cake', 'description' => 'Fresh baked soft fruit cake loaf.', 'base_price' => 250, 'unit_type' => 'Loaf', 'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=500&auto=format&fit=crop'],
            ],
            'Hardware & Tools' => [
                ['name' => 'LED Bulb 12W', 'description' => 'Energy saving bright white LED bulb.', 'base_price' => 250, 'unit_type' => 'Piece', 'image' => 'https://images.unsplash.com/photo-1550524514-e2670530f252?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Electrical Insulation PVC Tape', 'description' => 'Heavy duty electrical tape roll.', 'base_price' => 60, 'unit_type' => 'Roll', 'image' => 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Multi Screwdriver Tool Kit', 'description' => 'Handy home repair screwdriver set.', 'base_price' => 450, 'unit_type' => 'Set', 'image' => 'https://images.unsplash.com/photo-1581147036324-c17ac41dfa6c?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Steel Claw Hammer', 'description' => 'Durable rubber handle claw hammer.', 'base_price' => 550, 'unit_type' => 'Piece', 'image' => 'https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Adjustable Pipe Wrench 10 Inch', 'description' => 'Plumbing steel pipe wrench.', 'base_price' => 750, 'unit_type' => 'Piece', 'image' => 'https://images.unsplash.com/photo-1617788138017-80ad40651399?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Sanitary Connection Flexible Hose', 'description' => 'Stainless steel braided hose 18 inch.', 'base_price' => 320, 'unit_type' => 'Piece', 'image' => 'https://images.unsplash.com/photo-1585704032915-c3400ca199e7?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Teflon Pipe Tape Roll', 'description' => 'Plumbing thread seal tape.', 'base_price' => 40, 'unit_type' => 'Roll', 'image' => 'https://images.unsplash.com/photo-1584992236310-6edddc08acff?q=80&w=500&auto=format&fit=crop'],
            ],
            'Daily Essentials' => [
                ['name' => 'Fresh Tandoori Roti', 'description' => 'Hot fresh tandoori roti.', 'base_price' => 25, 'unit_type' => 'Piece', 'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Special Roghani Naan', 'description' => 'Fluffy sesame seed butter naan.', 'base_price' => 50, 'unit_type' => 'Piece', 'image' => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Fresh Tandoori Naan', 'description' => 'Hot plain tandoori naan.', 'base_price' => 40, 'unit_type' => 'Piece', 'image' => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Fresh Homemade Chapati', 'description' => 'Soft wheat chapati.', 'base_price' => 20, 'unit_type' => 'Piece', 'image' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Consumable Ice Block (5kg)', 'description' => 'Purified commercial ice block.', 'base_price' => 150, 'unit_type' => 'Block', 'image' => 'https://images.unsplash.com/photo-1517840901100-8179e982acb7?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Pure Mineral Water Bottle (1.5L)', 'description' => 'Purified drinking water.', 'base_price' => 90, 'unit_type' => 'Bottle', 'image' => 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?q=80&w=500&auto=format&fit=crop'],
            ],
            'Wholesale & Bakery' => [
                ['name' => 'Wholesale Egg Crate (30 Eggs)', 'description' => 'Full crate of fresh poultry farm eggs.', 'base_price' => 950, 'unit_type' => 'Crate', 'image' => 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Retail Poultry Eggs (1 Dozen)', 'description' => 'Fresh farm eggs dozen.', 'base_price' => 390, 'unit_type' => 'Dozen', 'image' => 'https://images.unsplash.com/photo-1516467508483-a7212febe31a?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Bakery Biscuits Wholesale Box (2kg)', 'description' => 'Assorted bakery biscuits bulk box.', 'base_price' => 850, 'unit_type' => 'Box', 'image' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Commercial Baking Flour Bag (10kg)', 'description' => 'Fine bakery grade flour bag.', 'base_price' => 1350, 'unit_type' => 'Bag', 'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?q=80&w=500&auto=format&fit=crop'],
            ],
            'Fresh Produce' => [
                ['name' => 'Fresh Farm Bananas (Dozen)', 'description' => 'Sweet ripe seasonal bananas.', 'base_price' => 180, 'unit_type' => 'Dozen', 'image' => 'https://images.unsplash.com/photo-1603833665858-e61d17a86224?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Red Apples (1kg)', 'description' => 'Fresh crispy juicy red apples.', 'base_price' => 350, 'unit_type' => 'Kg', 'image' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Fresh Red Onions (1kg)', 'description' => 'Essential kitchen red onions.', 'base_price' => 120, 'unit_type' => 'Kg', 'image' => 'https://images.unsplash.com/photo-1618512496248-a07fe83aa8cf?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Farm Potatoes (1kg)', 'description' => 'Clean fresh farm potatoes.', 'base_price' => 90, 'unit_type' => 'Kg', 'image' => 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Fresh Red Tomatoes (1kg)', 'description' => 'Ripe cooking tomatoes.', 'base_price' => 140, 'unit_type' => 'Kg', 'image' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Fresh Green Chilies (250g)', 'description' => 'Spicy green chilies.', 'base_price' => 50, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1588879460618-9249e7d947d1?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Fresh Garlic & Ginger Mix (250g)', 'description' => 'Raw garlic and ginger root.', 'base_price' => 180, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?q=80&w=500&auto=format&fit=crop'],
            ],
            'Wholesale & Snacks' => [
                ['name' => 'Mix Savory Nimco Wholesale Pack (1kg)', 'description' => 'Crispy spicy savory nimco mix.', 'base_price' => 650, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Assorted Sweet Candies Box', 'description' => 'Bulk sweet fruit candies box.', 'base_price' => 500, 'unit_type' => 'Box', 'image' => 'https://images.unsplash.com/photo-1582058091505-f87a2e55a40f?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Chocolate Bars Wholesale Carton', 'description' => 'Pack of 12 chocolate bars.', 'base_price' => 720, 'unit_type' => 'Carton', 'image' => 'https://images.unsplash.com/photo-1511381939415-e44015466834?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Potato Chips Snacks Bundle', 'description' => 'Packaged crispy potato chips bundle.', 'base_price' => 360, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?q=80&w=500&auto=format&fit=crop'],
            ],
            'Novelties & Gifts' => [
                ['name' => 'Gift Wrapping Paper & Ribbon Set', 'description' => 'Decorative gift wrapping rolls & ribbons.', 'base_price' => 150, 'unit_type' => 'Set', 'image' => 'https://images.unsplash.com/photo-1513885535751-8b9238bd345a?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Children Toy Vehicle', 'description' => 'Fun mini friction toy car.', 'base_price' => 350, 'unit_type' => 'Piece', 'image' => 'https://images.unsplash.com/photo-1596461404969-9ae70f2830c1?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Plush Teddy Bear (10 Inch)', 'description' => 'Soft stuffed plush toy.', 'base_price' => 650, 'unit_type' => 'Piece', 'image' => 'https://images.unsplash.com/photo-1559454403-b8fb88521f11?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Happy Birthday Gift Box Set', 'description' => 'Decorative surprise gift box.', 'base_price' => 280, 'unit_type' => 'Set', 'image' => 'https://images.unsplash.com/photo-1549465220-1a8b9238bd34?q=80&w=500&auto=format&fit=crop'],
            ],
            'Refreshments' => [
                ['name' => 'Special Meetha Paan', 'description' => 'Traditional sweet betel leaf with condiments.', 'base_price' => 80, 'unit_type' => 'Piece', 'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Kashmiri Saada Paan', 'description' => 'Aromatic traditional betel leaf.', 'base_price' => 60, 'unit_type' => 'Piece', 'image' => 'https://images.unsplash.com/photo-1551024709-8f23befc6f87?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Rainbow Gola Ganda Dessert', 'description' => 'Chilled crushed ice with sweet syrup toppings.', 'base_price' => 150, 'unit_type' => 'Bowl', 'image' => 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Chilled Soft Drink Bottle (500ml)', 'description' => 'Chilled carbonated soft drink.', 'base_price' => 90, 'unit_type' => 'Bottle', 'image' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Mint Mouth Freshener Pack', 'description' => 'Refreshing oral mints.', 'base_price' => 50, 'unit_type' => 'Pack', 'image' => 'https://images.unsplash.com/photo-1582058091505-f87a2e55a40f?q=80&w=500&auto=format&fit=crop'],
            ],
            'Health & Pharmacy' => [
                ['name' => 'OTC Panadol Extra (10 Tablets Strip)', 'description' => 'Paracetamol & caffeine pain reliever strip.', 'base_price' => 50, 'unit_type' => 'Strip', 'image' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Disprin Effervescent Tablets (Strip)', 'description' => 'Fast pain relief aspirin tablets.', 'base_price' => 40, 'unit_type' => 'Strip', 'image' => 'https://images.unsplash.com/photo-1585435557343-3b092031a831?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Antiseptic Hand Sanitizer (100ml)', 'description' => '70% alcohol rinse-free hand sanitizer.', 'base_price' => 180, 'unit_type' => 'Bottle', 'image' => 'https://images.unsplash.com/photo-1584483766114-2cea6facdf57?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'First Aid Bandage Box (20 Strips)', 'description' => 'Sterile adhesive wound bandages.', 'base_price' => 120, 'unit_type' => 'Box', 'image' => 'https://images.unsplash.com/photo-1603398938378-e54eab446dde?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Surgical Protective Face Masks (Box of 50)', 'description' => '3-Ply protective surgical masks.', 'base_price' => 350, 'unit_type' => 'Box', 'image' => 'https://images.unsplash.com/photo-1586942593568-29364ef8858b?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Dettol Antiseptic Liquid (100ml)', 'description' => 'Disinfectant first aid liquid.', 'base_price' => 220, 'unit_type' => 'Bottle', 'image' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Cough & Throat Relief Syrup (120ml)', 'description' => 'Soothes dry cough and sore throat.', 'base_price' => 160, 'unit_type' => 'Bottle', 'image' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?q=80&w=500&auto=format&fit=crop'],
            ],
            'Fresh Meat' => [
                ['name' => 'Fresh Whole Chicken Cleaned (1kg)', 'description' => 'Freshly slaughtered & cut chicken meat.', 'base_price' => 620, 'unit_type' => 'Kg', 'image' => 'https://images.unsplash.com/photo-1587593810167-a84920ea0781?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Boneless Chicken Breast (1kg)', 'description' => 'Fresh skinless boneless chicken fillets.', 'base_price' => 950, 'unit_type' => 'Kg', 'image' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Fresh Chicken Drumsticks (1kg)', 'description' => 'Juicy fresh chicken leg drumsticks.', 'base_price' => 680, 'unit_type' => 'Kg', 'image' => 'https://images.unsplash.com/photo-1587593810167-a84920ea0781?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Fresh Chicken Wings (1kg)', 'description' => 'Cleaned chicken wings.', 'base_price' => 520, 'unit_type' => 'Kg', 'image' => 'https://images.unsplash.com/photo-1527477396000-e27163b481c2?q=80&w=500&auto=format&fit=crop'],
                ['name' => 'Minced Chicken Keema (1kg)', 'description' => 'Freshly ground chicken mince.', 'base_price' => 880, 'unit_type' => 'Kg', 'image' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?q=80&w=500&auto=format&fit=crop'],
            ],
        ];

        foreach ($categoryProducts as $catName => $products) {
            $category = CatalogCategory::where('name', $catName)->first();
            if (!$category) {
                continue;
            }

            foreach ($products as $p) {
                GlobalProduct::updateOrCreate(
                    [
                        'catalog_category_id' => $category->id,
                        'name' => $p['name'],
                    ],
                    [
                        'description' => $p['description'],
                        'base_price' => $p['base_price'],
                        'unit_type' => $p['unit_type'],
                        'default_image' => $p['image'] ?? null,
                        'is_active' => true,
                    ]
                );
            }
        }

        // Attach catalog products to all sellers according to their catalog_category_id
        $sellers = Seller::where('is_deleted', false)->get();

        foreach ($sellers as $seller) {
            $catId = $seller->catalog_category_id;
            if (!$catId) {
                $genCat = CatalogCategory::where('slug', 'grocery-general')->first();
                $catId = $genCat?->id;
                if ($catId) {
                    $seller->forceFill(['catalog_category_id' => $catId])->save();
                }
            }

            if (!$catId) {
                continue;
            }

            // Get global products in this category (or general store as fallback)
            $globalProductIds = GlobalProduct::where('catalog_category_id', $catId)
                ->where('is_active', true)
                ->pluck('id');

            if ($globalProductIds->isEmpty()) {
                $genCat = CatalogCategory::where('slug', 'grocery-general')->first();
                if ($genCat) {
                    $globalProductIds = GlobalProduct::where('catalog_category_id', $genCat->id)
                        ->where('is_active', true)
                        ->pluck('id');
                }
            }

            $attachData = $globalProductIds->mapWithKeys(function ($productId) {
                return [
                    $productId => [
                        'custom_price' => null,
                        'stock_quantity' => rand(15, 60),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ];
            })->all();

            $seller->catalogProducts()->sync($attachData);
        }
    }
}
