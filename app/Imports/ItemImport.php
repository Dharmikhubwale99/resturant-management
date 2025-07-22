<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\Addon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Category;

class ItemImport implements ToCollection
{
    protected $restaurantId;
    protected $component;

    public function __construct($restaurantId, $component = null)
    {
        $this->restaurantId = $restaurantId;
        $this->component = $component;
    }

    public function collection(Collection $rows)
    {
        // Skip header row
        $rows = $rows->skip(1);
        $rowNum = 2; // Excel rows are 1-indexed, and first is header

        foreach ($rows as $row) {
            try {
                // Get or create category by name
                $category_name = trim($row[0]);
                $category = Category::firstOrCreate(
                    ['name' => $category_name, 'restaurant_id' => $this->restaurantId],
                    ['name' => $category_name, 'restaurant_id' => $this->restaurantId]
                );
                $category_id = $category->id;

                $name = $row[1];
                $item_type = $row[2];
                $short_name = $row[3];
                $code = $row[4];
                $description = $row[5];
                $price = $row[6];
                $image_url = $row[7];
                $variant_names = $row[8]; // comma separated
                $variant_prices = $row[9]; // comma separated
                $addon_names = $row[10]; // comma separated
                $addon_prices = $row[11]; // comma separated

                // Check for required columns
                if (empty($category_name) || empty($name) || empty($item_type) || empty($price)) {
                    throw new \Exception('Missing required column(s): category_name, name, item_type, price');
                }

                // Create item
                $item = Item::create([
                    'restaurant_id' => $this->restaurantId,
                    'category_id'   => $category_id,
                    'name'          => $name,
                    'item_type'     => $item_type,
                    'short_name'    => $short_name,
                    'code'          => $code,
                    'description'   => $description,
                    'price'         => $price,
                ]);

                // Handle image upload (from URL)
                if ($image_url) {
                    try {
                        $contents = file_get_contents($image_url);
                        $fileName = uniqid() . '-' . basename($image_url);
                        $folder = 'images/' . Str::slug($item->restaurant->name);
                        $storedPath = $folder . '/' . $fileName;
                        Storage::disk('public')->put($storedPath, $contents);

                        $item->addMedia(storage_path("app/public/{$storedPath}"))
                            ->preservingOriginal()
                            ->usingName(pathinfo($fileName, PATHINFO_FILENAME))
                            ->usingFileName($fileName)
                            ->toMediaCollection('images');
                    } catch (\Exception $e) {
                        // Log or handle image error
                    }
                }

                // Handle variants
                $variantNames = explode(',', $variant_names);
                $variantPrices = explode(',', $variant_prices);
                foreach ($variantNames as $i => $vName) {
                    $vName = trim($vName);
                    $vPrice = isset($variantPrices[$i]) ? trim($variantPrices[$i]) : null;
                    if ($vName && $vPrice) {
                        $item->variants()->create([
                            'name' => $vName,
                            'price' => $vPrice,
                        ]);
                    }
                }

                // Handle addons
                $addonNames = explode(',', $addon_names);
                $addonPrices = explode(',', $addon_prices);
                foreach ($addonNames as $i => $aName) {
                    $aName = trim($aName);
                    $aPrice = isset($addonPrices[$i]) ? trim($addonPrices[$i]) : null;
                    if ($aName && $aPrice) {
                        $item->addons()->create([
                            'name' => $aName,
                            'price' => $aPrice,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                if ($this->component) {
                    $this->component->importErrors[] = [
                        'row' => $rowNum,
                        'error' => $e->getMessage()
                    ];
                }
            }
            $rowNum++;
        }
    }
}
