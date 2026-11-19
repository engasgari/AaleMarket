<?php

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\InventoryMovement;
use App\Modules\Inventory\Models\InventoryStock;
use App\Modules\Inventory\Models\StockAdjustment;
use App\Modules\Inventory\Models\StockReservation;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InventoryService
{
    public function getOrCreateStock(int $warehouseId, int $productId, ?int $variantId = null): InventoryStock
    {
        $key = InventoryStock::buildStockKey($warehouseId, $productId, $variantId);

        return InventoryStock::query()->firstOrCreate(
            ['stock_key' => $key],
            [
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'quantity' => 0,
                'reserved_quantity' => 0,
                'low_stock_threshold' => config('aale.low_stock_threshold'),
            ]
        );
    }

    public function stockIn(
        int $warehouseId,
        int $productId,
        int $quantity,
        ?int $variantId = null,
        ?string $notes = null,
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): InventoryStock {
        $this->assertPositive($quantity);

        return DB::transaction(function () use ($warehouseId, $productId, $quantity, $variantId, $notes, $userId, $referenceType, $referenceId) {
            $stock = $this->lockStock($warehouseId, $productId, $variantId);
            $before = (int) $stock->quantity;
            $after = $before + $quantity;
            $stock->quantity = $after;
            $stock->save();

            $this->recordMovement($stock, 'in', $quantity, $before, $after, $notes, $userId, $referenceType, $referenceId);

            return $stock->fresh();
        });
    }

    public function stockOut(
        int $warehouseId,
        int $productId,
        int $quantity,
        ?int $variantId = null,
        ?string $notes = null,
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        string $type = 'out',
    ): InventoryStock {
        $this->assertPositive($quantity);

        return DB::transaction(function () use ($warehouseId, $productId, $quantity, $variantId, $notes, $userId, $referenceType, $referenceId, $type) {
            $stock = $this->lockStock($warehouseId, $productId, $variantId);
            $available = (int) $stock->quantity - (int) $stock->reserved_quantity;

            if ($available < $quantity) {
                throw new RuntimeException('موجودی کافی نیست.');
            }

            $before = (int) $stock->quantity;
            $after = $before - $quantity;
            $stock->quantity = $after;
            $stock->save();

            $this->recordMovement($stock, $type, $quantity, $before, $after, $notes, $userId, $referenceType, $referenceId);

            return $stock->fresh();
        });
    }

    public function adjust(
        int $warehouseId,
        int $productId,
        int $newQuantity,
        string $reason,
        ?int $variantId = null,
        ?int $userId = null,
    ): InventoryStock {
        if ($newQuantity < 0) {
            throw new InvalidArgumentException('موجودی نمی‌تواند منفی باشد.');
        }

        return DB::transaction(function () use ($warehouseId, $productId, $newQuantity, $reason, $variantId, $userId) {
            $stock = $this->lockStock($warehouseId, $productId, $variantId);
            $before = (int) $stock->quantity;

            if ($newQuantity < (int) $stock->reserved_quantity) {
                throw new RuntimeException('موجودی جدید کمتر از مقدار رزرو شده است.');
            }

            $stock->quantity = $newQuantity;
            $stock->save();

            StockAdjustment::query()->create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'quantity_before' => $before,
                'quantity_after' => $newQuantity,
                'reason' => $reason,
                'user_id' => $userId,
            ]);

            $this->recordMovement(
                $stock,
                'adjustment',
                $newQuantity - $before,
                $before,
                $newQuantity,
                $reason,
                $userId
            );

            return $stock->fresh();
        });
    }

    public function transfer(
        int $fromWarehouseId,
        int $toWarehouseId,
        int $productId,
        int $quantity,
        ?int $variantId = null,
        ?int $userId = null,
        ?string $notes = null,
    ): void {
        if ($fromWarehouseId === $toWarehouseId) {
            throw new InvalidArgumentException('انبار مبدأ و مقصد یکسان است.');
        }

        $this->assertPositive($quantity);

        DB::transaction(function () use ($fromWarehouseId, $toWarehouseId, $productId, $quantity, $variantId, $userId, $notes) {
            $from = $this->lockStock($fromWarehouseId, $productId, $variantId);
            $available = (int) $from->quantity - (int) $from->reserved_quantity;

            if ($available < $quantity) {
                throw new RuntimeException('موجودی انبار مبدأ کافی نیست.');
            }

            $beforeFrom = (int) $from->quantity;
            $from->quantity = $beforeFrom - $quantity;
            $from->save();
            $this->recordMovement($from, 'transfer_out', $quantity, $beforeFrom, (int) $from->quantity, $notes, $userId);

            $to = $this->lockStock($toWarehouseId, $productId, $variantId);
            $beforeTo = (int) $to->quantity;
            $to->quantity = $beforeTo + $quantity;
            $to->save();
            $this->recordMovement($to, 'transfer_in', $quantity, $beforeTo, (int) $to->quantity, $notes, $userId);
        });
    }

    public function reserve(
        int $warehouseId,
        int $productId,
        int $quantity,
        ?int $variantId = null,
        ?int $cartId = null,
        ?int $orderId = null,
    ): StockReservation {
        $this->assertPositive($quantity);

        return DB::transaction(function () use ($warehouseId, $productId, $quantity, $variantId, $cartId, $orderId) {
            $stock = $this->lockStock($warehouseId, $productId, $variantId);
            $available = (int) $stock->quantity - (int) $stock->reserved_quantity;

            if ($available < $quantity) {
                throw new RuntimeException('موجودی قابل رزرو کافی نیست.');
            }

            $before = (int) $stock->reserved_quantity;
            $stock->reserved_quantity = $before + $quantity;
            $stock->save();

            $this->recordMovement(
                $stock,
                'reserve',
                $quantity,
                (int) $stock->quantity,
                (int) $stock->quantity,
                'رزرو موجودی',
                null,
                $orderId ? 'order' : ($cartId ? 'cart' : null),
                $orderId ?: $cartId
            );

            return StockReservation::query()->create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'cart_id' => $cartId,
                'order_id' => $orderId,
                'quantity' => $quantity,
                'expires_at' => now()->addMinutes((int) config('aale.stock_reservation_minutes', 30)),
                'status' => 'active',
            ]);
        });
    }

    public function releaseReservation(StockReservation $reservation): void
    {
        if ($reservation->status !== 'active') {
            return;
        }

        DB::transaction(function () use ($reservation) {
            /** @var StockReservation $reservation */
            $reservation = StockReservation::query()->whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if ($reservation->status !== 'active') {
                return;
            }

            $stock = $this->lockStock(
                (int) $reservation->warehouse_id,
                (int) $reservation->product_id,
                $reservation->product_variant_id ? (int) $reservation->product_variant_id : null
            );

            $stock->reserved_quantity = max(0, (int) $stock->reserved_quantity - (int) $reservation->quantity);
            $stock->save();

            $reservation->status = 'released';
            $reservation->save();

            $this->recordMovement(
                $stock,
                'release',
                (int) $reservation->quantity,
                (int) $stock->quantity,
                (int) $stock->quantity,
                'آزادسازی رزرو',
                null,
                'stock_reservation',
                (int) $reservation->id
            );
        });
    }

    public function consumeReservation(StockReservation $reservation): void
    {
        if ($reservation->status !== 'active') {
            throw new RuntimeException('رزرو فعال نیست.');
        }

        DB::transaction(function () use ($reservation) {
            /** @var StockReservation $reservation */
            $reservation = StockReservation::query()->whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if ($reservation->status !== 'active') {
                throw new RuntimeException('رزرو فعال نیست.');
            }

            $stock = $this->lockStock(
                (int) $reservation->warehouse_id,
                (int) $reservation->product_id,
                $reservation->product_variant_id ? (int) $reservation->product_variant_id : null
            );

            $qty = (int) $reservation->quantity;

            if ((int) $stock->quantity < $qty || (int) $stock->reserved_quantity < $qty) {
                throw new RuntimeException('موجودی برای تبدیل رزرو به فروش کافی نیست.');
            }

            $before = (int) $stock->quantity;
            $stock->quantity = $before - $qty;
            $stock->reserved_quantity = (int) $stock->reserved_quantity - $qty;
            $stock->save();

            $reservation->status = 'consumed';
            $reservation->save();

            $this->recordMovement(
                $stock,
                'sale',
                $qty,
                $before,
                (int) $stock->quantity,
                'فروش پس از پرداخت',
                null,
                'stock_reservation',
                (int) $reservation->id
            );
        });
    }

    public function releaseExpiredReservations(): int
    {
        $expired = StockReservation::query()
            ->where('status', 'active')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expired as $reservation) {
            DB::transaction(function () use ($reservation) {
                $this->releaseReservation($reservation);
                $reservation->refresh();
                if ($reservation->status === 'released') {
                    $reservation->status = 'expired';
                    $reservation->save();
                }
            });
        }

        return $expired->count();
    }

    public function defaultWarehouse(): Warehouse
    {
        $warehouse = Warehouse::query()->where('is_default', true)->where('is_active', true)->first()
            ?? Warehouse::query()->where('is_active', true)->first();

        if (! $warehouse) {
            throw new RuntimeException('انبار فعالی تعریف نشده است.');
        }

        return $warehouse;
    }

    public function availableQuantity(int $productId, ?int $variantId = null, ?int $warehouseId = null): int
    {
        $query = InventoryStock::query()->where('product_id', $productId);

        if ($variantId) {
            $query->where('product_variant_id', $variantId);
        } else {
            $query->whereNull('product_variant_id');
        }

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return (int) $query->get()->sum(fn (InventoryStock $stock) => $stock->available_quantity);
    }

    private function lockStock(int $warehouseId, int $productId, ?int $variantId): InventoryStock
    {
        $key = InventoryStock::buildStockKey($warehouseId, $productId, $variantId);

        $stock = InventoryStock::query()->where('stock_key', $key)->lockForUpdate()->first();

        if (! $stock) {
            $this->getOrCreateStock($warehouseId, $productId, $variantId);
            $stock = InventoryStock::query()->where('stock_key', $key)->lockForUpdate()->firstOrFail();
        }

        return $stock;
    }

    private function recordMovement(
        InventoryStock $stock,
        string $type,
        int $quantity,
        int $before,
        int $after,
        ?string $notes = null,
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): void {
        InventoryMovement::query()->create([
            'warehouse_id' => $stock->warehouse_id,
            'product_id' => $stock->product_id,
            'product_variant_id' => $stock->product_variant_id,
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'user_id' => $userId,
            'created_at' => now(),
        ]);
    }

    private function assertPositive(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('تعداد باید بزرگ‌تر از صفر باشد.');
        }
    }
}
