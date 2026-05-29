<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Str; 
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Admin
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.com',
            'password' => Hash::make('password'),
            'phone'    => '0600000000',
            'role'     => 'admin',
        ]);

        // ✅ Vendeur
        $vendeur = User::create([
            'name'      => 'Mohamed Vendeur',
            'email'     => 'vendeur@test.com',
            'password'  => Hash::make('password'),
            'phone'     => '0611111111',
            'role'      => 'vendeur',
            'shop_name' => 'Boutique Mohamed',
        ]);

        // ✅ Client
        $client = User::create([
            'name'     => 'Ali Client',
            'email'    => 'client@test.com',
            'password' => Hash::make('password'),
            'phone'    => '0622222222',
            'role'     => 'client',
            'address'  => '12 Rue Hassan II, Rabat',
        ]);
        
        Cart::create(['user_id' => $client->id]);  // ✅ Correction: user_id au lieu de client_id

        // ✅ Livreur
        User::create([
            'name'         => 'Youssef Livreur',
            'email'        => 'livreur@test.com',
            'password'     => Hash::make('password'),
            'phone'        => '0633333333',
            'role'         => 'livreur',
            'vehicle_type' => 'moto',
            'is_available' => true,
        ]);

        // ✅ Catégories (Nouvelles catégories pour repas sains)
        $cat1 = Category::create(['name' => 'Salades & Bowls',    'icon' => '🥗']);
        $cat2 = Category::create(['name' => 'Protéines & Grillés', 'icon' => '🍗']);
        $cat3 = Category::create(['name' => 'Soupes & Bouillons',  'icon' => '🍲']);
        $cat4 = Category::create(['name' => 'Smoothies & Jus',     'icon' => '🥤']);
        $cat5 = Category::create(['name' => 'Snacks Sains',        'icon' => '🥜']);

        // ✅ Produits avec calories, temps de préparation et allergènes
        $products = [
            // Salades & Bowls
            ['name' => 'Bowl César au Poulet Grillé',    'price' => 65,  'stock' => 20, 'cat' => $cat1->id, 'calories' => 520, 'prep_time' => 15, 'allergens' => 'Gluten, Lait', 'desc' => 'Laitue romaine, poulet grillé, parmesan, croûtons au four, sauce césar légère'],
            ['name' => 'Salade Quinoa & Avocat',          'price' => 70,  'stock' => 15, 'cat' => $cat1->id, 'calories' => 480, 'prep_time' => 12, 'allergens' => 'Sans allergènes majeurs', 'desc' => 'Quinoa bio, avocat frais, tomates cerises, feta, vinaigrette citron-menthe'],
            ['name' => 'Bowl Buddha Végétarien',          'price' => 60,  'stock' => 18, 'cat' => $cat1->id, 'calories' => 450, 'prep_time' => 14, 'allergens' => 'Sans allergènes majeurs', 'desc' => 'Riz brun, pois chiches rôtis, légumes de saison, sauce tahini'],
            
            // Protéines & Grillés
            ['name' => 'Filet de Saumon Vapeur',          'price' => 95,  'stock' => 10, 'cat' => $cat2->id, 'calories' => 580, 'prep_time' => 20, 'allergens' => 'Poisson', 'desc' => 'Saumon atlantique, légumes vapeur, sauce yaourt-aneth, riz complet'],
            ['name' => 'Poulet Mariné aux Herbes',        'price' => 75,  'stock' => 12, 'cat' => $cat2->id, 'calories' => 490, 'prep_time' => 18, 'allergens' => 'Sans allergènes majeurs', 'desc' => 'Blanc de poulet, herbes fraîches, légumes grillés, purée de patate douce'],
            
            // Soupes & Bouillons
            ['name' => 'Soupe Lentilles Corail',          'price' => 45,  'stock' => 25, 'cat' => $cat3->id, 'calories' => 320, 'prep_time' => 10, 'allergens' => 'Sans allergènes majeurs', 'desc' => 'Lentilles corail, cumin, curcuma, carottes, coriandre fraîche'],
            ['name' => 'Velouté Butternut & Gingembre',   'price' => 50,  'stock' => 20, 'cat' => $cat3->id, 'calories' => 280, 'prep_time' => 12, 'allergens' => 'Sans allergènes majeurs', 'desc' => 'Courge butternut, gingembre frais, lait de coco, graines de courge'],
            
            // Smoothies & Jus
            ['name' => 'Smoothie Vert Détox',             'price' => 35,  'stock' => 30, 'cat' => $cat4->id, 'calories' => 180, 'prep_time' => 5,  'allergens' => 'Sans allergènes majeurs', 'desc' => 'Épinards, banane, pomme verte, gingembre, eau de coco'],
            ['name' => 'Jus Immunité Orange-Gingembre',   'price' => 30,  'stock' => 35, 'cat' => $cat4->id, 'calories' => 120, 'prep_time' => 5,  'allergens' => 'Sans allergènes majeurs', 'desc' => 'Orange fraîche, gingembre, curcuma, poivre noir, citron'],
            
            // Snacks Sains
            ['name' => 'Mix Noix & Fruits Secs Bio',      'price' => 40,  'stock' => 40, 'cat' => $cat5->id, 'calories' => 350, 'prep_time' => 2,  'allergens' => 'Fruits à coque', 'desc' => 'Amandes, noix, noisettes, abricots secs, canneberges, sans sucre ajouté'],
            ['name' => 'Energy Balls Dattes-Cacao',       'price' => 45,  'stock' => 25, 'cat' => $cat5->id, 'calories' => 220, 'prep_time' => 3,  'allergens' => 'Fruits à coque', 'desc' => 'Dattes medjool, cacao cru, amandes, noix de coco râpée, chia'],
        ];

        foreach ($products as $p) {
            Product::create([
                'vendeur_id'   => $vendeur->id,
                'category_id'  => $p['cat'],
                'name'         => $p['name'],
                'slug'         => Str::slug($p['name']),
                'price'        => $p['price'],
                'stock'        => $p['stock'],
                'description'  => $p['desc'],
                'calories'     => $p['calories'],
                'prep_time'    => $p['prep_time'],
                'allergens'    => $p['allergens'],
                'is_active'    => true,
                'is_featured'  => in_array($p['name'], ['Bowl César au Poulet Grillé', 'Filet de Saumon Vapeur']),
            ]);
        }
    }
}