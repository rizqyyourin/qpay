<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    @foreach ($metrics as $metric)
        <article class="glass-panel p-5 rounded-2xl border border-base-200">
            <p class="text-xs uppercase tracking-[0.4em] text-base-content/50">{{ $metric['label'] }}</p>
            <p class="mt-3 text-3xl font-semibold text-slate-900">{{ $metric['value'] }}</p>
            <p class="text-sm text-base-content/70">{{ $metric['meta'] }}</p>
        </article>
    @endforeach
</div>