<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Categories\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('دسته‌بندی‌ها | آله مارکت')]
class CategoryIndex extends Component
{
    public function render()
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.catalog.category-index', [
            'categories' => $categories,
        ])->layout('layouts.storefront', [
            'title' => 'دسته‌بندی‌ها | آله مارکت',
            'metaDescription' => 'دسته‌بندی محصولات آله مارکت',
        ]);
    }
}