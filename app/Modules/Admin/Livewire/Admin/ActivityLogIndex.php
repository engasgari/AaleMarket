<?php

namespace App\Modules\Admin\Livewire\Admin;

use App\Modules\Admin\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
#[Title('گزارش فعالیت')]
class ActivityLogIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('activity_logs.view');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->when($this->search !== '', function ($q) {
                $term = $this->search;
                $q->where('action', 'like', '%'.$term.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$term.'%'));
            })
            ->orderByDesc('created_at')
            ->paginate(30);

        return view('livewire.admin.admin.activity-log-index', [
            'logs' => $logs,
        ]);
    }
}