<?php

namespace App\Modules\Admin\Livewire\Catalog;

use App\Modules\Catalog\Models\Brand;
use App\Modules\Catalog\Models\Product;
use App\Modules\Catalog\Services\ProductService;
use App\Modules\Categories\Models\Category;
use App\Support\Helpers\PersianSlug;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class ProductForm extends Component
{
    use WithFileUploads;

    public ?Product $product = null;

    public string $name = '';

    public string $slug = '';

    public bool $slugManual = false;

    public string $sku = '';

    public ?string $product_code = null;

    public ?int $brand_id = null;

    /** @var list<int> */
    public array $category_ids = [];

    public ?string $short_description = null;

    public ?string $description = null;

    public int $purchase_price = 0;

    public int $selling_price = 0;

    public ?int $discount_price = null;

    public int $min_order_qty = 1;

    public string $status = 'draft';

    public bool $is_featured = false;

    public ?string $seo_title = null;

    public ?string $seo_description = null;

    public $main_image;

    public ?string $existing_main_image = null;

    public function mount(?Product $product = null): void
    {
        $this->product = $product;

        if ($product) {
            $this->authorize('products.update');
            $this->fillFromProduct($product);
        } else {
            $this->authorize('products.create');
        }
    }

    public function getTitle(): string
    {
        return $this->product ? 'ویرایش محصول' : 'محصول جدید';
    }

    public function updatedName(string $value): void
    {
        if (! $this->slugManual) {
            $this->slug = PersianSlug::make($value);
        }
    }

    protected function fillFromProduct(Product $product): void
    {
        $product->loadMissing('categories');

        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->slugManual = true;
        $this->sku = $product->sku;
        $this->product_code = $product->product_code;
        $this->brand_id = $product->brand_id;
        $this->category_ids = $product->categories->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->short_description = $product->short_description;
        $this->description = $product->description;
        $this->purchase_price = (int) $product->purchase_price;
        $this->selling_price = (int) $product->selling_price;
        $this->discount_price = $product->discount_price !== null ? (int) $product->discount_price : null;
        $this->min_order_qty = (int) $product->min_order_qty;
        $this->status = $product->status;
        $this->is_featured = (bool) $product->is_featured;
        $this->seo_title = $product->seo_title;
        $this->seo_description = $product->seo_description;
        $this->existing_main_image = $product->main_image_path;
    }

    public function save(ProductService $productService): void
    {
        if ($this->product) {
            $this->authorize('products.update');
        } else {
            $this->authorize('products.create');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255'],
            'product_code' => ['nullable', 'string', 'max:255'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'category_ids' => ['array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'purchase_price' => ['required', 'integer', 'min:0'],
            'selling_price' => ['required', 'integer', 'min:0'],
            'discount_price' => ['nullable', 'integer', 'min:0'],
            'min_order_qty' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published,archived'],
            'is_featured' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string'],
        ];

        if ($this->main_image) {
            $rules['main_image'] = ['image', 'max:2048'];
        }

        if (! $this->product) {
            $rules['sku'][] = 'unique:products,sku';
        }

        $this->validate($rules, [], [
            'name' => 'نام',
            'slug' => 'نامک',
            'sku' => 'SKU',
        ]);

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'product_code' => $this->product_code,
            'brand_id' => $this->brand_id ?: null,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,
            'discount_price' => $this->discount_price,
            'min_order_qty' => $this->min_order_qty,
            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
        ];

        if ($this->product) {
            $productService->update($this->product, $data, $this->category_ids, $this->main_image);
            session()->flash('status', 'محصول به‌روزرسانی شد.');
            $this->redirect(route('admin.products.edit', $this->product), navigate: true);

            return;
        }

        $product = $productService->create($data, $this->category_ids, $this->main_image);
        session()->flash('status', 'محصول ایجاد شد.');
        $this->redirect(route('admin.products.edit', $product), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.admin.catalog.product-form', [
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name', 'parent_id']),
            'mainImageUrl' => $this->existing_main_image ? Storage::disk('public')->url($this->existing_main_image) : null,
        ])->title($this->getTitle());
    }
}