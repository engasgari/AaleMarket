<div
    id="aale-toast-stack"
    class="pointer-events-none fixed bottom-4 left-4 z-[80] flex max-w-[min(100%-2rem,18rem)] flex-col-reverse gap-2"
    x-data
    x-cloak
>
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div
            class="pointer-events-auto w-fit max-w-full rounded-md border border-white/40 bg-white/55 px-3.5 py-2.5 text-center shadow-md backdrop-blur-md transition-all duration-300"
            :class="toast.type === 'error' ? 'text-danger' : 'text-ink'"
            x-show="toast.visible"
            x-transition:enter="ease-out duration-250"
            x-transition:enter-start="opacity-0 translate-y-3 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="ease-in duration-250"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
        >
            <p class="text-sm font-medium leading-6 whitespace-normal" x-text="toast.message"></p>
        </div>
    </template>
</div>
