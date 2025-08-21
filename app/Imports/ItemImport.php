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
        set_time_limit(500);
        $rows = $rows->skip(1); // skip header
        $rowNum = 2; // Excel starts at 1, header is first

        foreach ($rows as $row) {
            DB::beginTransaction();
            try {
                if ($this->categoryModule) {
                    $category_name = trim((string)($row[0] ?? ''));
                    if ($category_name === '') {
                        throw new \Exception('Missing required column: category_name');
                    }

                    $category = Category::firstOrCreate(
                        ['name' => $category_name, 'restaurant_id' => $this->restaurantId],
                        ['name' => $category_name, 'restaurant_id' => $this->restaurantId]
                    );
                    $category_id = $category->id;

                    $name          = $row[1] ?? null;
                    $item_type     = $row[2] ?? null;
                    $short_name    = $row[3] ?? null;
                    $code          = $row[4] ?? null;
                    $description   = $row[5] ?? '';
                    $price         = $row[6] ?? null;
                    $image_url     = $row[7] ?? '';
                    $variant_names = $row[8] ?? '';
                    $variant_prices= $row[9] ?? '';
                    $addon_names   = $row[10] ?? '';
                    $addon_prices  = $row[11] ?? '';
                } else {
                    $category_id   = null;
                    $name          = $row[0] ?? null;
                    $item_type     = $row[1] ?? null;
                    $short_name    = $row[2] ?? null;
                    $code          = $row[3] ?? null;
                    $description   = $row[4] ?? '';
                    $price         = $row[5] ?? null;
                    $image_url     = $row[6] ?? '';
                    $variant_names = $row[7] ?? '';
                    $variant_prices= $row[8] ?? '';
                    $addon_names   = $row[9] ?? '';
                    $addon_prices  = $row[10] ?? '';
                }

                if (($name === null || $name === '') ||
                    ($item_type === null || $item_type === '') ||
                    ($price === null || $price === '')) {
                    throw new \Exception('Missing required column(s): name, item_type, price');
                }

                $price = is_numeric($price) ? (float)$price : $price;

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
                $variantNames  = $this->splitCsv($variant_names);   // DO NOT array_filter default
                $variantPrices = $this->splitCsv($variant_prices);

                $max = max(count($variantNames), count($variantPrices));
                for ($i = 0; $i < $max; $i++) {
                    $vName     = isset($variantNames[$i]) ? trim((string)$variantNames[$i]) : '';
                    $vPriceRaw = $variantPrices[$i] ?? null;

                    // treat '' or null as missing, but keep 0 / "0"
                    $vPrice = ($vPriceRaw === null || $vPriceRaw === '')
                        ? null
                        : (is_numeric($vPriceRaw) ? (float)$vPriceRaw : $vPriceRaw);

                    if ($vName !== '') {
                        $item->variants()->create([
                            'name'  => $vName,
                            'price' => $vPrice ?? 0, // default to 0 if empty
                        ]);
                    }
                }
                // Addons
                $addonNames  = $this->splitCsv($addon_names);
                $addonPrices = $this->splitCsv($addon_prices);

                $max = max(count($addonNames), count($addonPrices));
                for ($i = 0; $i < $max; $i++) {
                    $aName     = isset($addonNames[$i]) ? trim((string)$addonNames[$i]) : '';
                    $aPriceRaw = $addonPrices[$i] ?? null;

                    $aPrice = ($aPriceRaw === null || $aPriceRaw === '')
                        ? null
                        : (is_numeric($aPriceRaw) ? (float)$aPriceRaw : $aPriceRaw);

                    if ($aName !== '') {
                        $item->addons()->create([
                            'name'  => $aName,
                            'price' => $aPrice ?? 0, // default to 0 if empty
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

    protected function splitCsv($value): array
    {
        $str = (string)$value;

        // explode without array_filter so "0" stays; also preserve empty slots for alignment
        $parts = explode(',', $str);

        // trim each part, but DO NOT drop "0"
        return array_map(function ($p) {
            // Keep as string to preserve "0"
            return trim((string)$p);
        }, $parts);
    }
}
