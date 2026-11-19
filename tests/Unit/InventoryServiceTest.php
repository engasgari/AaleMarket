<?php

namespace Tests\Unit;

use App\Modules\Catalog\Models\Product;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $inventory;

    private Warehouse $warehouse;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inventory = app(InventoryService::class);

        $this->warehouse = Warehouse::query()->create([
            'name' => 'انبار تست',
            'code' => 'TEST',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->product = Product::query()->create([
            'name' => 'محصول تست',
            'slug' => 'product-test',
            'sku' => 'SKU-TEST-1',
            'selling_price' => 100000,
            'status' => 'published',
        ]);
    }

    public function test_stock_in_and_out(): void
    {
        $this->inventory->stockIn($this->warehouse->id, $this->product->id, 10);
        $this->assertSame(10, $this->inventory->availableQuantity($this->product->id));

        $this->inventory->stockOut($this->warehouse->id, $this->product->id, 3);
        $this->assertSame(7, $this->inventory->availableQuantity($this->product->id));
    }

    public function test_reservation_prevents_oversell(): void
    {
        $this->inventory->stockIn($this->warehouse->id, $this->product->id, 5);
        $this->inventory->reserve($this->warehouse->id, $this->product->id, 4);

        $this->assertSame(1, $this->inventory->availableQuantity($this->product->id));

        $this->expectException(RuntimeException::class);
        $this->inventory->reserve($this->warehouse->id, $this->product->id, 2);
    }

    public function test_consume_reservation_reduces_stock(): void
    {
        $this->inventory->stockIn($this->warehouse->id, $this->product->id, 5);
        $reservation = $this->inventory->reserve($this->warehouse->id, $this->product->id, 2);
        $this->inventory->consumeReservation($reservation);

        $this->assertSame(3, $this->inventory->availableQuantity($this->product->id));
        $this->assertSame('consumed', $reservation->fresh()->status);
    }
}
