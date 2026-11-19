<?php

namespace App\Http\Controllers;

use App\Modules\Payments\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentCallbackController extends Controller
{
    public function zarinpal(Request $request, PaymentService $payments): RedirectResponse
    {
        try {
            $result = $payments->handleCallback($request->query());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('account.orders.index')
                ->with('error', $e->validator->errors()->first() ?? 'خطا در پردازش پرداخت.');
        }

        $payment = $result['payment'];
        $orderId = $payment->order_id;

        if ($result['success']) {
            return redirect()
                ->route('account.orders.show', ['order' => $orderId])
                ->with('status', $result['message'] ?? 'پرداخت با موفقیت انجام شد.');
        }

        return redirect()
            ->route('account.orders.show', ['order' => $orderId])
            ->with('error', $result['message'] ?? 'پرداخت ناموفق بود.');
    }
}