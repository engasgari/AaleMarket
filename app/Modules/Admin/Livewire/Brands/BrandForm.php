<?php

namespace App\Modules\Admin\Livewire\Brands;

use App\Modules\Catalog\Models\Brand;
use App\Support\Helpers\PersianSlug;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class BrandForm extends Component
{
    public ?Brand $brand = null;

    public string $name = '';

    public string $slug = '';

    public bool $slugManual = false;

    public ?string $description = null;

    public ?string $website = null;

    public bool $is_active = true;

    public int $sort_order = 0;

    public ?string $seo_title = null;

    public ?string $seo_description = null;

    public function mount(?Brand $brand = null): void
    {
        $this->brand = $brand;

        if ($brand) {
            $this->authorize('brands.update');
            $this->name = $brand->name;
            $this->slug = $brand->slug;
            $this->slugManual = true;
            $this->description = $brand->description;
            $this->website = $brand->website;
            $this->is_active = (bool) $brand->is_active;
            $this->sort_order = (int) $brand->sort_order;
            $this->seo_title = $brand->seo_title;
            $this->seo_description = $brand->seo_description;
        } else {
            $this->authorize('brands.create');
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
        $this->brand ? $this->authorize('brands.update') : $this->authorize('brands.create');

        $brandId = $this->brand?->id;

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'slug')->ignore($brandId),
            ],
            'description' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
        ]);

        $payload = [
            'name' => $this->name,
            'slug' => PersianSlug::make($this->slug),
            'description' => $this->description,
            'website' => $this->website,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
        ];

        if ($this->brand) {
            $this->brand->update($payload);
            session()->flash('status', 'برند به‌روزرسانی شد.');
            $this->redirect(route('admin.brands.edit', $this->brand), navigate: true);

            return;
        }

        $brand = Brand::query()->create($payload);
        session()->flash('status', 'برند ایجاد شد.');
        $this->redirect(route('admin.brands.edit', $brand), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.brands.brand-form')
            ->title($this->brand ? 'ویرایش برند' : 'برند جدید');
    }
}