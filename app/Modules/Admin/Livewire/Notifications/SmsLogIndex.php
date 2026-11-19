<?php

namespace App\Modules\Admin\Livewire\Notifications;

use App\Modules\Notifications\Models\SmsLog;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('پیامک‌ها')]
class SmsLogIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('sms.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $logs = SmsLog::query()
            ->when($this->search !== '', function ($q) {
                $term = $this->search;
                $q->where('mobile', 'like', '%'.$term.'%')
                    ->orWhere('template', 'like', '%'.$term.'%');
            })
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('livewire.admin.notifications.sms-log-index', [
            'logs' => $logs,
        ]);
    }
}