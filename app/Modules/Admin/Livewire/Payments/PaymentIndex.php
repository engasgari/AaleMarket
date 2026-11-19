<?php

namespace App\Modules\Admin\Livewire\Payments;

use App\Modules\Payments\Models\Payment;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('پرداخت‌ها')]
class PaymentIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('payments.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $payments = Payment::query()
            ->with(['order', 'user'])
            ->when($this->search !== '', function ($q) {
                $term = $this->search;
                $q->where(function ($q) use ($term) {
                    $q->where('authority', 'like', '%'.$term.'%')
                        ->orWhere('reference_id', 'like', '%'.$term.'%')
                        ->orWhere('tracking_code', 'like', '%'.$term.'%')
                        ->orWhereHas('order', fn ($o) => $o->where('order_number', 'like', '%'.$term.'%'));
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('livewire.admin.payments.payment-index', [
            'payments' => $payments,
        ]);
    }
}