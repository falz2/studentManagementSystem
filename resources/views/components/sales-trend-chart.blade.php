@php
    $dayCount = max(1, $chartDays->count() - 1);
    $points = $chartDays->values()->map(function (array $day, int $index) use ($maxRevenue, $dayCount): string {
        $x = 15 + ($index * 570 / $dayCount);
        $y = 155 - ($day['amount'] / $maxRevenue * 130);

        return number_format($x, 2, '.', '').','.number_format($y, 2, '.', '');
    })->implode(' ');
@endphp
<svg viewBox="0 0 600 180" preserveAspectRatio="none" role="img" aria-label="Daily completed sales revenue over the last {{ $chartDays->count() }} days" class="h-36 w-full">
    <path d="M15 25H585M15 90H585M15 155H585" stroke="#e2e8f0" stroke-width="1"/>
    <polyline points="{{ $points }}" fill="none" stroke="#4A7C59" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
