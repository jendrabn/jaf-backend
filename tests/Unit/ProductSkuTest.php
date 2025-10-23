<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSkuTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_sku_when_empty_on_create(): void
    {
        $brand = ProductBrand::factory()->create(['name' => 'Dior']);
        $category = ProductCategory::factory()->create(['name' => 'Parfum']);

        $p1 = Product::create([
            'product_category_id' => $category->id,
            'product_brand_id' => $brand->id,
            'name' => 'Sauvage',
            'slug' => 'sauvage',
            'sku' => null,
            'weight' => 500,
            'price' => 100000,
            'stock' => 10,
            'description' => 'desc',
            'is_publish' => true,
            'sex' => 1,
        ]);

        $this->assertSame('DIO-PAR-SAUV-M-0001', $p1->sku);

        $p2 = Product::create([
            'product_category_id' => $category->id,
            'product_brand_id' => $brand->id,
            'name' => 'Sauvage EDP',
            'slug' => 'sauvage-edp',
            'sku' => null,
            'weight' => 600,
            'price' => 120000,
            'stock' => 5,
            'description' => 'desc2',
            'is_publish' => true,
            'sex' => 1,
        ]);

        $this->assertSame('DIO-PAR-SAUV-M-0002', $p2->sku);
    }

    public function test_defaults_when_missing_brand_category_sex(): void
    {
        $p = Product::create([
            'product_category_id' => null,
            'product_brand_id' => null,
            'name' => 'Generic',
            'slug' => 'generic',
            'sku' => null,
            'weight' => 400,
            'price' => 90000,
            'stock' => 8,
            'description' => 'desc',
            'is_publish' => true,
            'sex' => null,
        ]);

        $this->assertSame('NON-GEN-GENE-U-0001', $p->sku);
    }

    public function test_respects_manual_sku_and_sanitizes(): void
    {
        $brand = ProductBrand::factory()->create(['name' => 'Dior']);
        $category = ProductCategory::factory()->create(['name' => 'Parfum']);

        $p = Product::create([
            'product_category_id' => $category->id,
            'product_brand_id' => $brand->id,
            'name' => 'Any',
            'slug' => 'any',
            'sku' => 'abc 123/<>?*',
            'weight' => 350,
            'price' => 75000,
            'stock' => 12,
            'description' => 'desc',
            'is_publish' => true,
            'sex' => 2,
        ]);

        $this->assertSame('ABC123', $p->sku);
    }

    public function test_sequence_is_per_brand_and_category(): void
    {
        $brandA = ProductBrand::factory()->create(['name' => 'Dior']);
        $brandB = ProductBrand::factory()->create(['name' => 'Dolce']);
        $catParfum = ProductCategory::factory()->create(['name' => 'Parfum']);
        $catSpray = ProductCategory::factory()->create(['name' => 'Spray']);

        $p1 = Product::create([
            'product_category_id' => $catParfum->id,
            'product_brand_id' => $brandA->id,
            'name' => 'Alpha',
            'slug' => 'alpha',
            'sku' => null,
            'weight' => 500,
            'price' => 100000,
            'stock' => 10,
            'description' => 'd',
            'is_publish' => true,
            'sex' => 3,
        ]);
        $this->assertSame('DIO-PAR-ALPH-U-0001', $p1->sku);

        $p2 = Product::create([
            'product_category_id' => $catParfum->id,
            'product_brand_id' => $brandB->id,
            'name' => 'Beta',
            'slug' => 'beta',
            'sku' => null,
            'weight' => 500,
            'price' => 100000,
            'stock' => 10,
            'description' => 'd',
            'is_publish' => true,
            'sex' => 3,
        ]);
        $this->assertSame('DOL-PAR-BETA-U-0001', $p2->sku);

        $p3 = Product::create([
            'product_category_id' => $catSpray->id,
            'product_brand_id' => $brandA->id,
            'name' => 'Gamma',
            'slug' => 'gamma',
            'sku' => null,
            'weight' => 500,
            'price' => 100000,
            'stock' => 10,
            'description' => 'd',
            'is_publish' => true,
            'sex' => 3,
        ]);
        $this->assertSame('DIO-SPR-GAMM-U-0001', $p3->sku);

        $p4 = Product::create([
            'product_category_id' => $catParfum->id,
            'product_brand_id' => null,
            'name' => 'Delta',
            'slug' => 'delta',
            'sku' => null,
            'weight' => 500,
            'price' => 100000,
            'stock' => 10,
            'description' => 'd',
            'is_publish' => true,
            'sex' => 3,
        ]);
        $this->assertSame('NON-PAR-DELT-U-0001', $p4->sku);
    }
}
