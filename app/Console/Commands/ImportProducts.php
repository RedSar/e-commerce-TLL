<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-products {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from a JSON file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the path to the JSON file from the command argument
        $path = $this->argument('path');


        // Load the products from the JSON file
        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return 1; // Exit with error
        }

        $productsJson = file_get_contents($path);
        $products = json_decode($productsJson, true);

        
            foreach ($products as $key => $product) {
                // Find the category ID based on the product's category name
                $lowercaseValues = array_map('strtolower', $product['tags'] );

                $categoryId = DB::table('categories')
                    ->whereRaw('LOWER(slug) IN (' . implode(',', array_fill(0, count($lowercaseValues), '?')) . ')', $lowercaseValues)
                    ->value('id');
                $cat_id = 13;
                if($categoryId){
                    continue;
                }
                // Find the brand ID based on the product's brand name
                $brandId = DB::table('brands')
                    ->where('name', $product['brand'])
                    ->value('id');

                if ($brandId === null) {
                    continue;
                }

                $filename =  $product['id']. '__'. $product['slug'] . '.png';

                $image = "products/" . $filename;
    
                // Prepare the data for insertion
                $productData = [
                    'category_id' => $cat_id,
                    'brand_id' => $brandId,
                    'name' => $product['name'],
                    'slug' => $product['slug'].'-'. $product['id']. '-'. $key,
                    'images' => json_encode([$image]), // Convert to JSON array if needed
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
    
                // Insert the product into the products table
                DB::table('products')->insert($productData);
            }
    
            $this->info("Products have been successfully inserted.");
            return 0; // Exit with success
    
    }
}
