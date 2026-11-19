<div>
    <div class="mb-6"><h1 class="text-xl font-semibold text-ink">گزارش پیامک</h1></div>
    <div class="mb-4 max-w-sm">
        <input type="search" wire:model.live.debounce.400ms="search" class="input" placeholder="موبایل یا قالب...">
    </div>
    <div class="card-surface overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b border-line bg-surface-secondary text-ink-muted">
                <tr>
                    <th class="px-4 py-3 text-right font-medium">موبایل</th>
                    <th class="px-4 py-3 text-right font-medium">درایور</th>
                    <th class="px-4 py-3 text-right font-medium">قالب</th>
                    <th class="px-4 py-3 text-right font-medium">وضعیت</th>
                    <th class="px-4 py-3 text-right font-medium">زمان</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="border-b border-line">
                        <td class="px-4 py-3" dir="ltr">{{ $log->mobile }}</td>
                        <td class="px-4 py-3">{{ $log->driver }}</td>
                        <td class="px-4 py-3">{{ $log->template }}</td>
                        <td class="px-4 py-3">{{ $log->status }}</td>
                        <td class="px-4 py-3 text-xs">{{ \App\Support\Helpers\JalaliDate::format($log->created_at) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-ink-muted">پیامکی ثبت نشده است.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</div>
