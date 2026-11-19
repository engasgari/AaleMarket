<?php

namespace App\Modules\Checkout\Livewire;

use App\Modules\Cart\Services\CartService;
use App\Modules\Customers\Models\CustomerAddress;
use App\Modules\Customers\Services\CustomerAddressService;
use App\Modules\Discounts\Services\CouponService;
use App\Modules\Orders\Services\OrderService;
use App\Modules\Payments\Services\PaymentService;
use App\Modules\Shipping\Services\ShippingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.storefront')]
#[Title('تسویه حساب | آله مارکت')]
class CheckoutPage extends Component
{
    public int $step = 1;

    public ?int $customerAddressId = null;

    public string $recipientName = '';

    public string $recipientMobile = '';

    public string $province = '';

    public string $city = '';

    public string $postalCode = '';

    public string $address = '';

    public bool $useNewAddress = false;

    public bool $saveNewAddress = true;

    public ?int $shippingMethodId = null;

    public string $couponCode = '';

    public ?int $appliedDiscount = null;

    public string $notes = '';

    public string $clientToken;

    public function mount(CartService $cartService): void
    {
        $cart = $cartService->getOrCreateForUser(Auth::user());

        if ($cartService->isEmpty($cart)) {
            session()->flash('error', 'سبد خرید شما خالی است.');
            $this->redirect(route('cart.index'), navigate: true);

            return;
        }

        $this->clientToken = (string) Str::uuid();

        $defaultAddress = CustomerAddress::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->first();

        if ($defaultAddress) {
            $this->customerAddressId = $defaultAddress->id;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > 5) {
            return;
        }

        if ($step > $this->step) {
            $this->validateCurrentStep();
        }

        $this->step = $step;
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        $this->step = min(5, $this->step + 1);
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function applyCoupon(CartService $cartService, CouponService $coupons): void
    {
        $this->validate([
            'couponCode' => ['required', 'string', 'max:50'],
        ], [
            'couponCode.required' => 'کد تخفیف را وارد کنید.',
        ]);

        $cart = $cartService->getOrCreateForUser(Auth::user());
        $cart->load('items.product.categories');
        $cartService->refreshLinePrices($cart);
        $cart->refresh()->load('items');

        $subtotal = $cartService->subtotal($cart);

        $result = $coupons->validateAndCalculate(
            trim($this->couponCode),
            Auth::user(),
            $subtotal,
            $cart->items,
        );

        $this->appliedDiscount = $result['discount_amount'];
        session()->flash('status', 'کد تخفیف اعمال شد.');
    }

    public function removeCoupon(): void
    {
        $this->couponCode = '';
        $this->appliedDiscount = null;
    }

    public function placeOrder(
        CartService $cartService,
        OrderService $orders,
        PaymentService $payments,
        ShippingService $shipping,
        CustomerAddressService $addressBook,
    ) {
        $this->validateCurrentStep();

        $coupon = $this->couponCode !== '' ? trim($this->couponCode) : null;
        $customerAddressId = $this->useNewAddress ? null : $this->customerAddressId;

        if ($this->useNewAddress && $this->saveNewAddress) {
            $saved = $addressBook->create(Auth::user(), [
                'title' => 'آدرس سفارش',
                'recipient_name' => $this->recipientName,
                'recipient_mobile' => $this->recipientMobile,
                'province' => $this->province,
                'city' => $this->city,
                'postal_code' => $this->postalCode,
                'address' => $this->address,
                'is_default' => ! Auth::user()->addresses()->exists(),
            ]);
            $customerAddressId = $saved->id;
            $this->customerAddressId = $saved->id;
            $this->useNewAddress = false;
        }

        try {
            $order = $orders->createFromCart(Auth::user(), [
                'customer_address_id' => $customerAddressId,
                'recipient_name' => $this->recipientName,
                'recipient_mobile' => $this->recipientMobile,
                'province' => $this->province,
                'city' => $this->city,
                'postal_code' => $this->postalCode,
                'address' => $this->address,
                'shipping_method_id' => (int) $this->shippingMethodId,
                'coupon_code' => $coupon,
                'notes' => $this->notes ?: null,
                'client_token' => $this->clientToken,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        }

        $payment = $payments->createPaymentForOrder($order, Auth::user());

        try {
            $redirectUrl = $payments->redirectToGateway($payment);
        } catch (ValidationException $e) {
            throw $e;
        }

        return redirect()->away($redirectUrl);
    }

    public function render(CartService $cartService, ShippingService $shipping)
    {
        $cart = $cartService->getOrCreateForUser(Auth::user());
        $cart->load(['items.product', 'items.productVariant']);
        $cartService->refreshLinePrices($cart);
        $cart->refresh()->load(['items.product', 'items.productVariant']);

        $subtotal = $cartService->subtotal($cart);
        $discount = $this->appliedDiscount ?? 0;
        $afterDiscount = max(0, $subtotal - $discount);

        $shippingPrice = 0;
        $selectedMethod = null;

        if ($this->shippingMethodId) {
            try {
                $quote = $shipping->quote((int) $this->shippingMethodId, $afterDiscount);
                $shippingPrice = $quote['price'];
                $selectedMethod = $quote['method'];
            } catch (ValidationException) {
                // ignore on render
            }
        }

        $total = $afterDiscount + $shippingPrice;

        $addresses = CustomerAddress::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->get();

        return view('livewire.checkout.checkout-page', [
            'cartItems' => $cart->items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shippingPrice' => $shippingPrice,
            'total' => $total,
            'shippingMethods' => $shipping->listActiveMethods(),
            'selectedMethod' => $selectedMethod,
            'addresses' => $addresses,
            'currencyLabel' => config('aale.currency_label', 'ریال'),
        ]);
    }

    private function validateCurrentStep(): void
    {
        if (in_array($this->step, [1, 4, 5], true)) {
            $this->validateAddressStep();
        }

        if (in_array($this->step, [2, 4, 5], true)) {
            $this->validate([
                'shippingMethodId' => ['required', 'integer'],
            ], [
                'shippingMethodId.required' => 'روش ارسال را انتخاب کنید.',
            ]);
        }
    }

    private function validateAddressStep(): void
    {
        if (! $this->useNewAddress && $this->customerAddressId) {
            return;
        }

        if (! $this->useNewAddress && ! $this->customerAddressId) {
            throw ValidationException::withMessages([
                'customerAddressId' => 'یک آدرس انتخاب کنید یا آدرس جدید وارد کنید.',
            ]);
        }

        $this->useNewAddress = true;

        $this->validate([
            'recipientName' => ['required', 'string', 'max:100'],
            'recipientMobile' => ['required', 'regex:/^09\d{9}$/'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'postalCode' => ['required', 'regex:/^\d{10}$/'],
            'address' => ['required', 'string', 'max:1000'],
        ], [
            'recipientName.required' => 'نام گیرنده الزامی است.',
            'recipientMobile.required' => 'شماره موبایل گیرنده الزامی است.',
            'recipientMobile.regex' => 'فرمت شماره موبایل صحیح نیست.',
            'province.required' => 'استان الزامی است.',
            'city.required' => 'شهر الزامی است.',
            'postalCode.required' => 'کد پستی الزامی است.',
            'postalCode.regex' => 'کد پستی باید ۱۰ رقم باشد.',
            'address.required' => 'آدرس کامل الزامی است.',
        ]);
    }
}