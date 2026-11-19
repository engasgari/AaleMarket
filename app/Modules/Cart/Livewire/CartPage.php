<?php

namespace App\Modules\Cart\Livewire;

use App\Modules\Cart\Services\CartService;
use App\Modules\Inventory\Services\InventoryService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('سبد خرید | آله مارکت')]
class CartPage extends Component
{
    public function updateQuantity(int $cartItemId, $quantity, CartService $cart): void
    {
        $quantity = max(1, (int) $quantity);

        try {
            $cart->updateItem($cartItemId, $quantity);
            $this->dispatch('toast', message: 'سبد خرید به‌روزرسانی شد.', type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function remove(int $cartItemId, CartService $cart): void
    {
        $cart->removeItem($cartItemId);
        $this->dispatch('toast', message: 'آیتم از سبد خرید حذف شد.', type: 'success');
    }

    public function render(CartService $cart, InventoryService $inventory)
    {
        $items = $cart->items();
        $stockErrors = $cart->validateStock();

        return view('livewire.cart.cart-page', [
            'items' => $items,
            'subtotal' => $cart->subtotal(),
            'stockErrors' => $stockErrors,
        ])->layout('layouts.storefront', [
            'title' => 'سبد خرید | آله مارکت',
            'metaDescription' => 'سبد خرید آله مارکت',
        ]);
    }
}