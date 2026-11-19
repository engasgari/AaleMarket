<?php

namespace App\Modules\Admin\Livewire;

use App\Models\User;
use App\Modules\Catalog\Models\Product;
use App\Modules\Orders\Models\Order;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('داشبورد')]
class Dashboard extends Component
{
    public int $ordersCount = 0;

    public int $productsCount = 0;

    public int $usersCount = 0;

    public function mount(): void
    {
        if (Schema::hasTable('orders')) {
            $this->ordersCount = Order::query()->count();
        }

        if (Schema::hasTable('products')) {
            $this->productsCount = Product::query()->count();
        }

        if (Schema::hasTable('users')) {
            $this->usersCount = User::query()->count();
        }
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}