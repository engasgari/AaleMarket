@props([
    'amount' => 0,
    'compare' => null,
    'withLabel' => true,
])

@php
    use App\Support\Helpers\Money;
    $amount = (int) $amount;
    $compare = $compare !== null ? (int) $compare : null;
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-baseline gap-2']) }}>
    <span class="text-base font-semibold text-ink">{{ Money::format($amount, $withLabel) }}</span>
    @if($compare !== null && $compare > $amount)
        <span class="text-sm text-ink-muted line-through">{{ Money::format($compare, $withLabel) }}</span>
    @endif
</div>