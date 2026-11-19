@if(session('status') || session('error'))
    <div
        class="hidden"
        data-aale-flash
        data-status="{{ e(session('status')) }}"
        data-error="{{ e(session('error')) }}"
        x-data
        x-init="
            const status = $el.dataset.status;
            const error = $el.dataset.error;
            if (status) window.AaleToast?.push(status, 'success');
            if (error) window.AaleToast?.push(error, 'error');
        "
    ></div>
@endif
