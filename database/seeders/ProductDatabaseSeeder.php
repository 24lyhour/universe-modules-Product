<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Menu\Models\Category;
use Modules\Outlet\Models\Outlet;
use Modules\Product\Models\Product;
use Modules\Product\Models\ProductType;

class ProductDatabaseSeeder extends Seeder
{
    /**
     * Product templates organized by product type slug.
     */
    private array $productTemplates = [
        // ==================== FOOD PRODUCTS ====================
        'food' => [
            [
                'name' => 'Classic Burger',
                'description' => 'Juicy beef patty with lettuce, tomato, onion, and special sauce',
                'sku' => 'FOOD-BURG-001',
                'price' => 12.99,
                'purchase_price' => 5.50,
                'sale_price' => null,
                'stock' => 100,
                'low_stock_threshold' => 10,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800'],
            ],
            [
                'name' => 'Cheese Pizza',
                'description' => 'Traditional pizza with mozzarella cheese and tomato sauce',
                'sku' => 'FOOD-PIZZ-001',
                'price' => 15.99,
                'purchase_price' => 6.00,
                'sale_price' => 13.99,
                'stock' => 50,
                'low_stock_threshold' => 5,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=800'],
            ],
            [
                'name' => 'Caesar Salad',
                'description' => 'Fresh romaine lettuce with Caesar dressing, croutons, and parmesan',
                'sku' => 'FOOD-SALD-001',
                'price' => 9.99,
                'purchase_price' => 3.50,
                'stock' => 75,
                'low_stock_threshold' => 10,
                'is_featured' => false,
                'images' => ['https://images.unsplash.com/photo-1546793665-c74683f339c1?w=800'],
            ],
            [
                'name' => 'Grilled Chicken Sandwich',
                'description' => 'Tender grilled chicken breast with avocado and honey mustard',
                'sku' => 'FOOD-SAND-001',
                'price' => 11.49,
                'purchase_price' => 4.50,
                'stock' => 60,
                'low_stock_threshold' => 8,
                'is_featured' => false,
                'images' => ['https://images.unsplash.com/photo-1553909489-cd47e0907980?w=800'],
            ],
            [
                'name' => 'French Fries',
                'description' => 'Crispy golden fries with sea salt',
                'sku' => 'FOOD-SIDE-001',
                'price' => 4.99,
                'purchase_price' => 1.50,
                'sale_price' => 3.99,
                'stock' => 200,
                'low_stock_threshold' => 20,
                'is_featured' => false,
                'images' => ['https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=800'],
            ],
            [
                'name' => 'BBQ Ribs',
                'description' => 'Slow-cooked pork ribs with tangy BBQ sauce',
                'sku' => 'FOOD-MAIN-001',
                'price' => 22.99,
                'purchase_price' => 10.00,
                'sale_price' => 19.99,
                'stock' => 30,
                'low_stock_threshold' => 5,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1544025162-d76694265947?w=800'],
            ],
            [
                'name' => 'Veggie Wrap',
                'description' => 'Fresh vegetables wrapped in a whole wheat tortilla',
                'sku' => 'FOOD-WRAP-001',
                'price' => 8.99,
                'purchase_price' => 3.00,
                'stock' => 45,
                'low_stock_threshold' => 8,
                'is_featured' => false,
                'images' => ['https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=800'],
            ],
            [
                'name' => 'Fish & Chips',
                'description' => 'Beer-battered fish with crispy chips and tartar sauce',
                'sku' => 'FOOD-MAIN-002',
                'price' => 16.99,
                'purchase_price' => 7.00,
                'stock' => 40,
                'low_stock_threshold' => 8,
                'is_featured' => false,
                'images' => ['https://images.unsplash.com/photo-1579208030886-b1a5ed34c0a3?w=800'],
            ],
            [
                'name' => 'Chicken Wings',
                'description' => 'Crispy wings with your choice of sauce (Buffalo, BBQ, or Garlic Parmesan)',
                'sku' => 'FOOD-APPZ-001',
                'price' => 13.99,
                'purchase_price' => 5.00,
                'stock' => 70,
                'low_stock_threshold' => 10,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1527477396000-e27163b481c2?w=800'],
            ],
            [
                'name' => 'Spaghetti Carbonara',
                'description' => 'Classic Italian pasta with creamy egg sauce and crispy bacon',
                'sku' => 'FOOD-PAST-001',
                'price' => 14.99,
                'purchase_price' => 5.50,
                'stock' => 65,
                'low_stock_threshold' => 10,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1612874742237-6526221588e3?w=800'],
            ],
            [
                'name' => 'Pad Thai',
                'description' => 'Thai stir-fried rice noodles with shrimp, tofu, and peanuts',
                'sku' => 'FOOD-THAI-001',
                'price' => 13.99,
                'purchase_price' => 5.00,
                'stock' => 55,
                'low_stock_threshold' => 8,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1559314809-0d155014e29e?w=800'],
            ],
            [
                'name' => 'Beef Pho',
                'description' => 'Vietnamese beef noodle soup with fresh herbs',
                'sku' => 'FOOD-VIET-001',
                'price' => 12.99,
                'purchase_price' => 4.50,
                'stock' => 60,
                'low_stock_threshold' => 10,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?w=800'],
            ],
        ],

        // ==================== BEVERAGE PRODUCTS ====================
        'beverage' => [
            [
                'name' => 'Chocolate Milkshake',
                'description' => 'Creamy chocolate milkshake topped with whipped cream',
                'sku' => 'BEV-MILK-001',
                'price' => 6.49,
                'purchase_price' => 2.00,
                'stock' => 80,
                'low_stock_threshold' => 10,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=800'],
            ],
            [
                'name' => 'Iced Coffee',
                'description' => 'Refreshing cold brew coffee served over ice',
                'sku' => 'BEV-COFF-001',
                'price' => 4.99,
                'purchase_price' => 1.20,
                'stock' => 150,
                'low_stock_threshold' => 15,
                'is_featured' => false,
                'images' => ['https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=800'],
            ],
            [
                'name' => 'Lemonade',
                'description' => 'Freshly squeezed lemonade',
                'sku' => 'BEV-LEMN-001',
                'price' => 3.99,
                'purchase_price' => 0.80,
                'stock' => 120,
                'low_stock_threshold' => 15,
                'is_featured' => false,
                'images' => ['https://images.unsplash.com/photo-1621263764928-df1444c5e859?w=800'],
            ],
            [
                'name' => 'Green Tea Latte',
                'description' => 'Matcha green tea with steamed milk',
                'sku' => 'BEV-TEA-001',
                'price' => 5.49,
                'purchase_price' => 1.50,
                'stock' => 90,
                'low_stock_threshold' => 12,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1515823064-d6e0c04616a7?w=800'],
            ],
            [
                'name' => 'Mango Smoothie',
                'description' => 'Tropical mango blended with yogurt',
                'sku' => 'BEV-SMTH-001',
                'price' => 6.99,
                'purchase_price' => 2.20,
                'stock' => 70,
                'low_stock_threshold' => 10,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1546173159-315724a31696?w=800'],
            ],
            [
                'name' => 'Espresso',
                'description' => 'Strong Italian espresso shot',
                'sku' => 'BEV-ESP-001',
                'price' => 2.99,
                'purchase_price' => 0.60,
                'stock' => 200,
                'low_stock_threshold' => 20,
                'is_featured' => false,
                'images' => ['https://images.unsplash.com/photo-1510707577719-ae7c14805e3a?w=800'],
            ],
        ],

        // ==================== DESSERT PRODUCTS ====================
        'dessert' => [
            [
                'name' => 'Cheesecake',
                'description' => 'New York style cheesecake with strawberry topping',
                'sku' => 'DSRT-CAKE-001',
                'price' => 7.99,
                'purchase_price' => 2.50,
                'stock' => 25,
                'low_stock_threshold' => 5,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1524351199678-941a58a3df50?w=800'],
            ],
            [
                'name' => 'Ice Cream Sundae',
                'description' => 'Vanilla ice cream with chocolate sauce and sprinkles',
                'sku' => 'DSRT-ICE-001',
                'price' => 6.49,
                'purchase_price' => 2.00,
                'sale_price' => 5.49,
                'stock' => 55,
                'low_stock_threshold' => 10,
                'is_featured' => false,
                'images' => ['https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=800'],
            ],
            [
                'name' => 'Chocolate Brownie',
                'description' => 'Rich chocolate brownie with walnuts',
                'sku' => 'DSRT-BRWN-001',
                'price' => 5.99,
                'purchase_price' => 1.80,
                'stock' => 40,
                'low_stock_threshold' => 8,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1564355808539-22fda35bed7e?w=800'],
            ],
            [
                'name' => 'Tiramisu',
                'description' => 'Classic Italian coffee-flavored dessert',
                'sku' => 'DSRT-TIRA-001',
                'price' => 8.99,
                'purchase_price' => 3.00,
                'stock' => 30,
                'low_stock_threshold' => 5,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=800'],
            ],
        ],

        // ==================== GADGET PRODUCTS ====================
        'gadget' => [
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Apple iPhone 15 Pro with A17 Pro chip and titanium design',
                'sku' => 'GADG-IPH-001',
                'price' => 999.00,
                'purchase_price' => 850.00,
                'stock' => 50,
                'low_stock_threshold' => 10,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=800'],
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'description' => 'Samsung flagship with S Pen and AI features',
                'sku' => 'GADG-SAM-001',
                'price' => 1199.00,
                'purchase_price' => 950.00,
                'sale_price' => 1099.00,
                'stock' => 35,
                'low_stock_threshold' => 5,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=800'],
            ],
            [
                'name' => 'MacBook Pro 14"',
                'description' => 'Apple MacBook Pro with M3 Pro chip',
                'sku' => 'GADG-MBP-001',
                'price' => 1999.00,
                'purchase_price' => 1700.00,
                'stock' => 25,
                'low_stock_threshold' => 5,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800'],
            ],
            [
                'name' => 'AirPods Pro 2',
                'description' => 'Apple wireless earbuds with active noise cancellation',
                'sku' => 'GADG-APP-001',
                'price' => 249.00,
                'purchase_price' => 180.00,
                'sale_price' => 229.00,
                'stock' => 100,
                'low_stock_threshold' => 15,
                'is_featured' => false,
                'images' => ['https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=800'],
            ],
            [
                'name' => 'iPad Air',
                'description' => 'Apple iPad Air with M2 chip and 10.9" display',
                'sku' => 'GADG-IPA-001',
                'price' => 599.00,
                'purchase_price' => 450.00,
                'stock' => 40,
                'low_stock_threshold' => 8,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=800'],
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'description' => 'Premium wireless noise-canceling headphones',
                'sku' => 'GADG-SNY-001',
                'price' => 399.00,
                'purchase_price' => 280.00,
                'sale_price' => 349.00,
                'stock' => 60,
                'low_stock_threshold' => 10,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?w=800'],
            ],
        ],

        // ==================== CLOTHING PRODUCTS ====================
        'clothing' => [
            [
                'name' => 'Classic Cotton T-Shirt',
                'description' => 'Soft premium cotton t-shirt, available in multiple colors',
                'sku' => 'CLTH-TSH-001',
                'price' => 29.99,
                'purchase_price' => 12.00,
                'stock' => 200,
                'low_stock_threshold' => 30,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800'],
            ],
            [
                'name' => 'Slim Fit Jeans',
                'description' => 'Modern slim fit denim jeans with stretch comfort',
                'sku' => 'CLTH-JNS-001',
                'price' => 59.99,
                'purchase_price' => 25.00,
                'sale_price' => 49.99,
                'stock' => 150,
                'low_stock_threshold' => 20,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1542272604-787c3835535d?w=800'],
            ],
            [
                'name' => 'Hoodie Sweatshirt',
                'description' => 'Comfortable fleece hoodie with kangaroo pocket',
                'sku' => 'CLTH-HOD-001',
                'price' => 49.99,
                'purchase_price' => 22.00,
                'stock' => 120,
                'low_stock_threshold' => 15,
                'is_featured' => false,
                'images' => ['https://images.unsplash.com/photo-1556821840-3a63f95609a7?w=800'],
            ],
            [
                'name' => 'Running Sneakers',
                'description' => 'Lightweight running shoes with cushioned sole',
                'sku' => 'CLTH-SNK-001',
                'price' => 89.99,
                'purchase_price' => 45.00,
                'sale_price' => 79.99,
                'stock' => 80,
                'low_stock_threshold' => 10,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=800'],
            ],
            [
                'name' => 'Leather Jacket',
                'description' => 'Classic black leather jacket with zipper',
                'sku' => 'CLTH-JKT-001',
                'price' => 199.99,
                'purchase_price' => 95.00,
                'stock' => 40,
                'low_stock_threshold' => 5,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1551028719-00167b16eac5?w=800'],
            ],
        ],

        // ==================== BOOK PRODUCTS ====================
        'book' => [
            [
                'name' => 'The Great Gatsby',
                'description' => 'Classic novel by F. Scott Fitzgerald',
                'sku' => 'BOOK-FIC-001',
                'price' => 14.99,
                'purchase_price' => 5.00,
                'stock' => 100,
                'low_stock_threshold' => 15,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=800'],
            ],
            [
                'name' => 'Clean Code',
                'description' => 'A Handbook of Agile Software Craftsmanship by Robert C. Martin',
                'sku' => 'BOOK-TECH-001',
                'price' => 39.99,
                'purchase_price' => 18.00,
                'sale_price' => 34.99,
                'stock' => 60,
                'low_stock_threshold' => 10,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1532012197267-da84d127e765?w=800'],
            ],
            [
                'name' => 'Flutter in Action',
                'description' => 'Build cross-platform mobile apps with Flutter',
                'sku' => 'BOOK-TECH-002',
                'price' => 44.99,
                'purchase_price' => 22.00,
                'stock' => 45,
                'low_stock_threshold' => 8,
                'is_featured' => false,
                'images' => ['https://images.unsplash.com/photo-1589998059171-988d887df646?w=800'],
            ],
            [
                'name' => 'Laravel Up & Running',
                'description' => 'A Framework for Building Modern PHP Apps',
                'sku' => 'BOOK-TECH-003',
                'price' => 49.99,
                'purchase_price' => 25.00,
                'stock' => 40,
                'low_stock_threshold' => 8,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=800'],
            ],
            [
                'name' => 'Atomic Habits',
                'description' => 'An Easy & Proven Way to Build Good Habits by James Clear',
                'sku' => 'BOOK-SELF-001',
                'price' => 19.99,
                'purchase_price' => 8.00,
                'stock' => 80,
                'low_stock_threshold' => 12,
                'is_featured' => true,
                'images' => ['https://images.unsplash.com/photo-1512820790803-83ca734da794?w=800'],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all outlets
        $outlets = Outlet::with('typeOutlet')->get();

        if ($outlets->isEmpty()) {
            $this->command->warn('No outlets found. Please run OutletSeeder first.');
            return;
        }

        // Food & Beverage outlet types
        $foodBeverageOutletTypes = ['Restaurant', 'Cafe', 'Fast Food', 'Bar', 'Bakery', 'Food Truck'];

        // Retail outlet types
        $retailOutletTypes = ['Store', 'Warehouse', 'Kiosk'];

        $createdCount = 0;

        foreach ($outlets as $outlet) {
            $typeName = $outlet->typeOutlet?->name_type ?? 'Restaurant';

            // Determine which product types to seed based on outlet type
            if (in_array($typeName, $foodBeverageOutletTypes)) {
                $productTypeSlugs = ['food', 'beverage', 'dessert'];
            } elseif (in_array($typeName, $retailOutletTypes)) {
                $productTypeSlugs = ['gadget', 'clothing', 'book'];
            } else {
                // Default - seed all types
                $productTypeSlugs = array_keys($this->productTemplates);
            }

            // Get product types for this outlet
            $productTypes = ProductType::where('outlet_id', $outlet->id)
                ->whereIn('slug', $productTypeSlugs)
                ->get()
                ->keyBy('slug');

            foreach ($productTypeSlugs as $typeSlug) {
                if (!isset($this->productTemplates[$typeSlug])) {
                    continue;
                }

                $productType = $productTypes->get($typeSlug);

                foreach ($this->productTemplates[$typeSlug] as $productData) {
                    $sku = $productData['sku'] . '-' . $outlet->id;

                    Product::firstOrCreate(
                        ['sku' => $sku],
                        [
                            'uuid' => Str::uuid(),
                            'outlet_id' => $outlet->id,
                            'product_type_id' => $productType?->id,
                            'product_type' => $typeSlug, // Legacy enum field
                            'name' => $productData['name'],
                            'slug' => Str::slug($productData['name'] . '-' . $outlet->id),
                            'description' => $productData['description'],
                            'sku' => $sku,
                            'price' => $productData['price'],
                            'purchase_price' => $productData['purchase_price'],
                            'sale_price' => $productData['sale_price'] ?? null,
                            'stock' => $productData['stock'],
                            'low_stock_threshold' => $productData['low_stock_threshold'],
                            'status' => 'active',
                            'is_featured' => $productData['is_featured'],
                            'pre_order' => false,
                            'images' => $productData['images'] ?? [],
                        ]
                    );

                    $createdCount++;
                }
            }
        }

        $this->command->info("Products seeded successfully. Created {$createdCount} products for {$outlets->count()} outlets.");
    }
}
