<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed an admin account plus sample categories and products.
     * Sample images are simple SVG placeholders; replace them from /admin.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@pachwomen.com')],
            [
                'name' => 'PACH Admin',
                'password' => env('ADMIN_PASSWORD') ?: Str::password(16),
                'is_admin' => true,
            ],
        );

        $catalog = [
            'Kurtis' => [
                ['Ivory Chikankari Straight Kurti', 1899, 1499, '#efe6da', 'Cotton'],
                ['Mustard Block Print A-Line Kurti', 1299, null, '#e8c26a', 'Cotton'],
                ['Bottle Green Embroidered Kurti', 2199, null, '#2f5d50', 'Rayon'],
                ['Dusty Rose Anarkali Kurti', 2499, 1999, '#d9a5a0', 'Georgette'],
            ],
            'Tunics' => [
                ['Indigo Short Tunic', 999, null, '#3b4e7a', 'Cotton'],
                ['Peach Tiered Tunic Top', 1199, 899, '#f2c3a7', 'Rayon'],
                ['Black Pintuck Tunic', 1099, null, '#222222', 'Linen blend'],
            ],
            'Ponchos' => [
                ['Beige Crochet Poncho', 1599, null, '#d8c8ae', 'Acrylic wool'],
                ['Maroon Tassel Poncho', 1799, 1399, '#7a2e3a', 'Wool blend'],
            ],
            'Shawls' => [
                ['Pashmina Feel Paisley Shawl', 1499, null, '#8c6a4f', 'Viscose'],
                ['Kashmiri Embroidered Stole', 1999, null, '#b9a27f', 'Wool'],
            ],
            'Western' => [
                ['Sage Linen Co-ord Set', 2499, null, '#a7b59a', 'Linen'],
                ['White Puff Sleeve Top', 899, 699, '#f6f4f0', 'Cotton'],
                ['Denim Shirt Dress', 1999, null, '#6c86a8', 'Denim'],
            ],
            'Festive' => [
                ['Red Banarasi Kurta Set', 3499, 2999, '#a8262c', 'Banarasi silk'],
                ['Gold Tissue Sharara Set', 3999, null, '#c9a24a', 'Tissue'],
            ],
        ];

        $sort = 0;
        foreach ($catalog as $categoryName => $products) {
            $slug = Str::slug($categoryName);
            $category = Category::updateOrCreate(['slug' => $slug], [
                'name' => $categoryName,
                'description' => "Our {$categoryName} collection, picked for comfort and everyday style.",
                'image' => $this->placeholder("categories/{$slug}.svg", $products[0][3], $categoryName),
                'sort_order' => $sort++,
                'is_active' => true,
            ]);

            foreach ($products as $i => [$name, $price, $salePrice, $color, $fabric]) {
                $productSlug = Str::slug($name);
                $freeSize = in_array($categoryName, ['Ponchos', 'Shawls']);

                Product::updateOrCreate(['slug' => $productSlug], [
                    'category_id' => $category->id,
                    'name' => $name,
                    'sku' => 'PW-'.strtoupper(Str::substr($slug, 0, 3)).'-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                    'description' => "A {$fabric} piece from PACH WOMEN. Soft, breathable and easy to style for daily wear or special days.\n\nGentle hand wash or dry clean. Colours may vary slightly due to lighting.",
                    'fabric' => $fabric,
                    'color' => null,
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'images' => [
                        $this->placeholder("products/{$productSlug}-1.svg", $color, $name),
                        $this->placeholder("products/{$productSlug}-2.svg", $this->shade($color, -25), $name),
                    ],
                    'sizes' => $categoryName === 'Shawls' ? [] : ($freeSize ? ['Free Size'] : ['S', 'M', 'L', 'XL', 'XXL']),
                    'stock' => $i === 2 ? 0 : random_int(3, 25),
                    'is_featured' => $i === 0,
                    'is_active' => true,
                ]);
            }
        }

        $banners = [
            ['Grace in every thread', 'Kurtis, tunics, ponchos and festive wear, picked for comfort and made to be noticed.', '#d8c8ae', 'Shop the collection', null],
            ['The festive edit', 'Banarasi, tissue and hand-worked sets for weddings, Eid and every celebration.', '#a8262c', 'Shop festive', '/category/festive'],
            ['Winter layers', 'Crochet ponchos and soft shawls to wrap up in, from ₹1,399.', '#7a2e3a', 'Shop ponchos', '/category/ponchos'],
        ];

        $this->call(ContentPageSeeder::class);

        foreach ($banners as $sort => [$title, $subtitle, $color, $button, $link]) {
            Banner::updateOrCreate(['title' => $title], [
                'subtitle' => $subtitle,
                'image' => $this->placeholder('banners/hero-'.($sort + 1).'.svg', $color, $title),
                'button_text' => $button,
                'link' => $link,
                'sort_order' => $sort,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Write a simple garment-silhouette SVG to the public disk and return its path.
     */
    private function placeholder(string $path, string $color, string $label): string
    {
        $bg = $this->shade($color, 60);
        $dark = $this->shade($color, -40);
        $text = e(Str::limit($label, 28));

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 800">
  <rect width="600" height="800" fill="{$bg}"/>
  <path d="M230 130 L270 110 Q300 150 330 110 L370 130 L470 200 L430 300 L390 270 L410 640 Q300 670 190 640 L210 270 L170 300 L130 200 Z" fill="{$color}" stroke="{$dark}" stroke-width="3"/>
  <path d="M270 110 Q300 190 330 110" fill="none" stroke="{$dark}" stroke-width="3"/>
  <text x="300" y="730" text-anchor="middle" font-family="Georgia, serif" font-size="26" fill="{$dark}" letter-spacing="2">{$text}</text>
</svg>
SVG;

        Storage::disk('public')->put($path, $svg);

        return $path;
    }

    /**
     * Lighten (positive) or darken (negative) a hex colour by a percentage.
     */
    private function shade(string $hex, int $percent): string
    {
        $rgb = array_map('hexdec', str_split(ltrim($hex, '#'), 2));

        $rgb = array_map(fn (int $c) => (int) round($percent >= 0
            ? $c + (255 - $c) * $percent / 100
            : $c * (100 + $percent) / 100), $rgb);

        return sprintf('#%02x%02x%02x', ...$rgb);
    }
}
