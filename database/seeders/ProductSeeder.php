<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['Cappuccino Rya', 'Caffetteria', 'Cremoso, equilibrato, pensato per la colazione al tavolo.', 2.20],
            ['Espresso miscela bakery', 'Caffetteria', 'Caffe intenso con profilo caldo e persistente.', 1.30],
            ['Cornetto alla crema', 'Dolci', 'Sfoglia dorata con crema pasticcera morbida.', 1.80],
            ['Pain au chocolat', 'Dolci', 'Impasto fragrante con cuore al cioccolato fondente.', 2.10],
            ['Focaccia genovese', 'Salato', 'Classica focaccia ligure, soffice e lucida.', 2.50],
            ['Toast prosciutto e formaggio', 'Salato', 'Toast caldo, essenziale e pronto per la pausa pranzo.', 4.80],
            ['Spremuta d arancia', 'Bevande', 'Arance spremute al momento.', 3.80],
            ['Crostatina ai frutti rossi', 'Dolci', 'Base friabile, confettura e frutta rossa.', 3.20],
        ];

        foreach ($products as [$name, $category, $description, $price]) {
            Product::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'category' => $category,
                    'description' => $description,
                    'price' => $price,
                    'is_available' => true,
                    'is_active' => true,
                ],
            );
        }

        Product::factory(10)->create();
    }
}
