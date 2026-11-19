<?php

namespace App\Modules\Customers\Livewire;

use App\Modules\Customers\Models\CustomerAddress;
use App\Modules\Customers\Services\CustomerAddressService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Layout('layouts.storefront')]
#[Title('مدیریت آدرس‌ها | آله مارکت')]
class AddressBookPage extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $recipientName = '';

    public string $recipientMobile = '';

    public string $province = '';

    public string $city = '';

    public string $postalCode = '';

    public string $address = '';

    public bool $isDefault = false;

    public function startCreate(): void
    {
        $this->resetForm();
        $user = Auth::user();
        $this->recipientName = (string) ($user->name ?? '');
        $this->recipientMobile = (string) ($user->mobile ?? '');
        $this->isDefault = ! $user->addresses()->exists();
        $this->showForm = true;
    }

    public function startEdit(int $addressId): void
    {
        $address = CustomerAddress::query()
            ->where('user_id', Auth::id())
            ->whereKey($addressId)
            ->firstOrFail();

        $this->editingId = $address->id;
        $this->title = (string) ($address->title ?? '');
        $this->recipientName = (string) $address->recipient_name;
        $this->recipientMobile = (string) $address->recipient_mobile;
        $this->province = (string) $address->province;
        $this->city = (string) $address->city;
        $this->postalCode = (string) $address->postal_code;
        $this->address = (string) $address->address;
        $this->isDefault = (bool) $address->is_default;
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function save(CustomerAddressService $addresses): void
    {
        $this->validate([
            'title' => ['nullable', 'string', 'max:80'],
            'recipientName' => ['required', 'string', 'min:2', 'max:120'],
            'recipientMobile' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:80'],
            'city' => ['required', 'string', 'max:80'],
            'postalCode' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'min:8', 'max:500'],
            'isDefault' => ['boolean'],
        ], [
            'recipientName.required' => 'نام گیرنده الزامی است.',
            'recipientMobile.required' => 'موبایل گیرنده الزامی است.',
            'province.required' => 'استان الزامی است.',
            'city.required' => 'شهر الزامی است.',
            'postalCode.required' => 'کد پستی الزامی است.',
            'address.required' => 'آدرس کامل الزامی است.',
            'address.min' => 'آدرس کامل خیلی کوتاه است.',
        ]);

        $payload = [
            'title' => $this->title,
            'recipient_name' => $this->recipientName,
            'recipient_mobile' => $this->recipientMobile,
            'province' => $this->province,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'address' => $this->address,
            'is_default' => $this->isDefault,
        ];

        try {
            if ($this->editingId) {
                $address = CustomerAddress::query()
                    ->where('user_id', Auth::id())
                    ->whereKey($this->editingId)
                    ->firstOrFail();
                $addresses->update(Auth::user(), $address, $payload);
                $message = 'آدرس به‌روزرسانی شد.';
            } else {
                $addresses->create(Auth::user(), $payload);
                $message = 'آدرس جدید ذخیره شد.';
            }
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $map = [
                    'recipient_mobile' => 'recipientMobile',
                    'postal_code' => 'postalCode',
                    'recipient_name' => 'recipientName',
                ];
                $this->addError($map[$field] ?? $field, $messages[0] ?? 'خطا');
            }

            return;
        }

        $this->resetForm();
        $this->showForm = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function deleteAddress(int $addressId, CustomerAddressService $addresses): void
    {
        $address = CustomerAddress::query()
            ->where('user_id', Auth::id())
            ->whereKey($addressId)
            ->firstOrFail();

        try {
            $addresses->delete(Auth::user(), $address);
            if ($this->editingId === $addressId) {
                $this->cancelForm();
            }
            $this->dispatch('toast', message: 'آدرس حذف شد.', type: 'success');
        } catch (Throwable $e) {
            $this->dispatch('toast', message: $e->getMessage() ?: 'حذف آدرس ممکن نیست.', type: 'error');
        }
    }

    public function makeDefault(int $addressId, CustomerAddressService $addresses): void
    {
        $address = CustomerAddress::query()
            ->where('user_id', Auth::id())
            ->whereKey($addressId)
            ->firstOrFail();

        $addresses->makeDefault(Auth::user(), $address);
        $this->dispatch('toast', message: 'آدرس پیش‌فرض تنظیم شد.', type: 'success');
    }

    public function render()
    {
        $addresses = CustomerAddress::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return view('livewire.customers.address-book-page', [
            'addresses' => $addresses,
        ]);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->recipientName = '';
        $this->recipientMobile = '';
        $this->province = '';
        $this->city = '';
        $this->postalCode = '';
        $this->address = '';
        $this->isDefault = false;
        $this->resetValidation();
    }
}
