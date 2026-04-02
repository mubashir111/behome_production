@extends('layouts.admin')

@section('title', 'Damages')

@section('content')
<div class="max-w-[1200px] mx-auto pb-12">
    <div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h2 class="text-3xl font-bold font-outfit text-slate-900">Damage Records</h2>
            <p class="text-slate-500 mt-1">Track damaged or lost inventory items.</p>
        </div>
    </div>

    @include('admin._alerts')

    <div class="bg-white rounded-[2.5rem] border border-slate-300 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div style="display:flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:7px 12px;max-width:320px;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2" style="flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input id="damages-search" type="text" placeholder="Search by reference..." style="border:none;outline:none;background:transparent;font-size:13px;color:#1e293b;width:100%;min-width:0;" />
            </div>
        </div>
        <div class="overflow-x-auto">
            <table id="damages-table" class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200">
                        <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">#</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Date</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Reference No</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Subtotal</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Total</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Note</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($damages as $damage)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5 font-mono text-xs text-slate-400">{{ $damage->id }}</td>
                            <td class="px-6 py-5 text-sm font-medium text-slate-700">
                                {{ \Carbon\Carbon::parse($damage->date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-5 font-mono font-bold text-slate-900">{{ $damage->reference_no }}</td>
                            <td class="px-6 py-5 text-sm text-slate-600">{{ number_format($damage->subtotal, 2) }}</td>
                            <td class="px-6 py-5 font-bold text-rose-600">{{ number_format($damage->total, 2) }}</td>
                            <td class="px-6 py-5 text-sm text-slate-500 italic">{{ $damage->note ?? '—' }}</td>
                            <td class="px-6 py-5 text-center">
                                <button type="button"
                                    onclick="confirmSubmit('del-damage-{{ $damage->id }}', { title: 'Delete Damage Record', message: 'This will permanently remove the damage record. Continue?', confirmText: 'Yes, Delete', type: 'danger' })"
                                    class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-100 hover:bg-rose-50 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                <form id="del-damage-{{ $damage->id }}" action="{{ route('admin.damages.destroy', $damage->id) }}" method="POST" style="display:none;">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-8 py-20 text-center text-slate-400 font-medium italic">
                                No damage records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($damages->hasPages())
            <div class="px-8 py-6 bg-slate-50 border-t border-slate-200">
                {{ $damages->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    if(typeof adminSearch === 'function') adminSearch('damages-search', 'damages-table');
</script>
@endpush
