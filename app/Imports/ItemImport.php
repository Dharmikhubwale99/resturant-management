<?php

namespace App\Imports;

use App\Models\Item;
use App\Models\Addon;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;

class ItemImport implements ToCollection
{
    protected $restaurantId;
    protected $component;
    protected $categoryModule;

    public function __construct($restaurantId, $component = null, $categoryModule = true)
    {
        $this->restaurantId = $restaurantId;
        $this->component = $component;
        $this->categoryModule = $categoryModule;
    }

    public function collection(Collection $rows)
    {
        $rows = $rows->skip(1); // skip header
        $rowNum = 2; // Excel starts at 1, header is first

        foreach ($rows as $row) {
            DB::beginTransaction();
            try {
                if ($this->categoryModule) {
                    $category_name = trim($row[0]);
                    if (empty($category_name)) {
                        throw new \Exception('Missing required column: category_name');
                    }
                    $category = Category::firstOrCreate(
                        ['name' => $category_name, 'restaurant_id' => $this->restaurantId],
                        ['name' => $category_name, 'restaurant_id' => $this->restaurantId]
                    );
                    $category_id = $category->id;

                    $name         = $row[1] ?? null;
                    $item_type    = $row[2] ?? null;
                    $short_name   = $row[3] ?? null;
                    $code         = $row[4] ?? null;
                    $description  = $row[5] ?? '';
                    $price        = $row[6] ?? null;
                    $image_url    = $row[7] ?? '';
                    $variant_names  = $row[8] ?? '';
                    $variant_prices = $row[9] ?? '';
                    $addon_names    = $row[10] ?? '';
                    $addon_prices   = $row[11] ?? '';
                } else {
                    $category_id   = null;
                    $name          = $row[0] ?? null;
                    $item_type     = $row[1] ?? null;
                    $short_name    = $row[2] ?? null;
                    $code          = $row[3] ?? null;
                    $description   = $row[4] ?? '';
                    $price         = $row[5] ?? null;
                    $image_url     = $row[6] ?? '';
                    $variant_names   = $row[7] ?? '';
                    $variant_prices  = $row[8] ?? '';
                    $addon_names     = $row[9] ?? '';
                    $addon_prices    = $row[10] ?? '';
                }

                if (empty($name) || empty($item_type) || empty($price)) {
                    throw new \Exception('Missing required column(s): name, item_type, price');
                }

                $isExists = Item::where([
                    'restaurant_id' => $this->restaurantId,
                    'category_id'   => $category_id,
                    'name'          => $name,
                ])->exists();

                if ($isExists) {
                    throw new \Exception("Item '{$name}' already exists in this category.");
                }

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
                Log::info($item);

                // Image upload
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
                        // Optional: log image fetch errors
                    }
                }

                // Variants
                $variantNames = array_filter(array_map('trim', explode(',', (string) $variant_names)));
                $variantPrices = array_filter(array_map('trim', explode(',', (string) $variant_prices)));

                foreach ($variantNames as $i => $vName) {
                    $vPrice = $variantPrices[$i] ?? null;
                    if (!empty($vName) && $vPrice !== null) {
                        $item->variants()->create([
                            'name' => $vName,
                            'price' => $vPrice,
                        ]);
                    }
                }

                // Addons
                $addonNames = array_filter(array_map('trim', explode(',', (string) $addon_names)));
                $addonPrices = array_filter(array_map('trim', explode(',', (string) $addon_prices)));

                foreach ($addonNames as $i => $aName) {
                    $aPrice = $addonPrices[$i] ?? null;
                    if (!empty($aName) && $aPrice !== null) {
                        $item->addons()->create([
                            'name' => $aName,
                            'price' => $aPrice,
                        ]);
                    }
                }

                Log::info("Importing row {$rowNum}: " . json_encode([
                    'name' => $name,
                    'item_type' => $item_type,
                    'price' => $price,
                    'image_url' => $image_url,
                ]));

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                if ($this->component) {
                    $this->component->importErrors[] = [
                        'row' => $rowNum,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $rowNum++;
        }
    }
}
