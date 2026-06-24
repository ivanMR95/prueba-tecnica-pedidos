<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Bolígrafo azul',
                'price' => 1.50,
                'stock' => 100,
            ],
            [
                'name' => 'Cuaderno A4',
                'price' => 4.95,
                'stock' => 50,
            ],
            [
                'name' => 'Teclado mecánico',
                'price' => 79.90,
                'stock' => 20,
            ],
            [
                'name' => 'Ratón inalámbrico',
                'price' => 29.90,
                'stock' => 35,
            ],
            [
                'name' => 'Monitor 24 pulgadas',
                'price' => 189.99,
                'stock' => 12,
            ],
            [
                'name' => 'Auriculares USB',
                'price' => 39.50,
                'stock' => 25,
            ],
            [
                'name' => 'Memoria USB 64GB',
                'price' => 14.90,
                'stock' => 60,
            ],
            [
                'name' => 'Webcam Full HD',
                'price' => 49.99,
                'stock' => 18,
            ],
            [
                'name' => 'Soporte para portátil',
                'price' => 24.50,
                'stock' => 30,
            ],
            [
                'name' => 'Cable HDMI',
                'price' => 9.95,
                'stock' => 80,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
