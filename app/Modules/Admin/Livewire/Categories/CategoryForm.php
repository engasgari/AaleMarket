<?php

namespace App\Modules\Admin\Livewire\Categories;

use App\Modules\Categories\Models\Category;
use App\Support\Helpers\PersianSlug;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class CategoryForm extends Component
{
    public ?Category $category = null;

    public ?int $parent_id = null;

    public string $name = '';

    public string $slug = '';

    public bool $slugManual = false;

    public ?string $description = null;

    public bool $is_active = true;

    public int $sort_order = 0;

    public ?string $seo_title = null;

    public ?string $seo_description = null;

    public function mount(?Category $category = null): void
    {
        $this->category = $category;

        if ($category) {
            $this->authorize('categories.update');
            $this->name = $category->name;
            $this->slug = $category->slug;
            $this->slugManual = true;
            $this->parent_id = $category->parent_id;
            $this->description = $category->description;
            $this->is_active = (bool) $category->is_active;
            $this->sort_order = (int) $category->sort_order;
            $this->seo_title = $category->seo_title;
            $this->seo_description = $category->seo_description;
        } else {
            $this->authorize('categories.create');
        }
    }

    public function updatedName(string $value): void
    {
        if (! $this->slugManual) {
            $this->slug = PersianSlug::make($value);
        }
    }

    public function save(): void
    {
        $this->category ? $this->authorize('categories.update') : $this->authorize('categories.create');

        $categoryId = $this->category?->id;

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
                Rule::notIn(array_filter([$categoryId])),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
        ]);

        $payload = [
            'parent_id' => $this->parent_id ?: null,
            'name' => $this->name,
            'slug' => PersianSlug::make($this->slug),
            'description' => $this->description,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
        ];

        if ($this->category) {
            $this->category->update($payload);
            session()->flash('status', 'دسته‌بندی به‌روزرسانی شد.');
            $this->redirect(route('admin.categories.edit', $this->category), navigate: true);

            return;
        }

        $category = Category::query()->create($payload);
        session()->flash('status', 'دسته‌بندی ایجاد شد.');
        $this->redirect(route('admin.categories.edit', $category), navigate: true);
    }

    public function render(): View
    {
        $parents = Category::query()
            ->when($this->category, fn ($q) => $q->where('id', '!=', $this->category->id))
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        return view('livewire.admin.categories.category-form', [
            'parents' => $parents,
        ])->title($this->category ? 'ویرایش دسته‌بندی' : 'دسته‌بندی جدید');
    }
}