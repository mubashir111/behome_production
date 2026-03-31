@props([
    'searchPlaceholder' => 'Search...',
    'clearUrl' => null,
])

<div class="glass p-4 rounded-2xl mb-6 flex flex-wrap items-center justify-between gap-4">
    <div class="flex-1 min-w-[250px]">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" name="search" value="{{ request('search') }}" 
                   class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl bg-white/50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                   placeholder="{{ $searchPlaceholder }}">
        </div>
    </div>
    <div class="flex items-center space-x-3">
        {{ $filters }}
    </div>
    @if($clearUrl || request()->anyFilled(['search', 'status', 'filter']))
        <a href="{{ $clearUrl ?? request()->url() }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition text-sm font-medium">Clear</a>
    @endif
</div>
