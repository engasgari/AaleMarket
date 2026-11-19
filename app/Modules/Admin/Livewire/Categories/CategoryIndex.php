<?php

namespace App\Modules\Admin\Livewire\Categories;

use App\Modules\Categories\Models\Category;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('دسته‌بندی‌ها')]
class CategoryIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('categories.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $categories = Category::query()
            ->with('parent:id,name')
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.admin.categories.category-index', [
            'categories' => $categories,
        ]);
    }
}