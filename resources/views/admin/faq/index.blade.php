@extends('layouts.admin')
@section('title', 'FAQ Items')
@section('content')

<div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">FAQ Items</h2>
        <p class="text-slate-500 mt-1 text-sm">Manage frequently asked questions shown on the FAQ page.</p>
    </div>
    <a href="{{ route('admin.faq.create') }}" class="admin-btn-primary">+ Add Question</a>
</div>

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm font-medium">
        {{ session('success') }}
    </div>
@endif

<div class="glass p-4 rounded-2xl mb-6 flex items-center gap-3">
    <span class="text-slate-400 flex-shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
    </span>
    <input id="faq-search" type="text" placeholder="Search faqs…" class="flex-1 bg-transparent border-none outline-none focus:outline-none text-slate-900" />
</div>

{{-- Group by category --}}
@foreach($categories as $key => $label)
    @php $items = $faqs->where('category', $key); @endphp
    @if($items->count())
    <div class="admin-table-card mb-6">
        <div class="data-card-header">
            <span class="data-card-title">{{ $label }}</span>
            <span class="text-xs text-slate-400">{{ $items->count() }} item(s)</span>
        </div>
        <table class="admin-table">
            <thead class="admin-table-head">
                <tr>
                    <th class="admin-table-head-cell w-10">#</th>
                    <th class="admin-table-head-cell">Question</th>
                    <th class="admin-table-head-cell w-20">Order</th>
                    <th class="admin-table-head-cell w-20">Status</th>
                    <th class="admin-table-head-cell text-right w-28">Actions</th>
                </tr>
            </thead>
            <tbody class="admin-table-body">
                @foreach($items as $faq)
                <tr class="admin-table-row">
                    <td class="admin-table-cell text-slate-400 text-sm">{{ $faq->id }}</td>
                    <td class="admin-table-cell">
                        <p class="text-sm font-semibold text-slate-900">{{ Str::limit($faq->question, 80) }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ Str::limit($faq->answer, 80) }}</p>
                    </td>
                    <td class="admin-table-cell text-sm text-slate-600">{{ $faq->sort_order }}</td>
                    <td class="admin-table-cell">
                        <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full {{ $faq->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $faq->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="admin-table-actions">
                        <a href="{{ route('admin.faq.edit', $faq) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg mr-1 transition-colors">Edit</a>
                        <button type="button" onclick="confirmSubmit('del-faq-{{ $faq->id }}', { title: 'Delete FAQ Item', message: 'Are you sure you want to delete this FAQ item? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors">Delete</button>
                        <form id="del-faq-{{ $faq->id }}" method="POST" action="{{ route('admin.faq.destroy', $faq) }}" style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
@endforeach

@if($faqs->isEmpty())
    <div class="text-center py-16 bg-white rounded-2xl border border-slate-200">
        <div class="w-14 h-14 rounded-full bg-slate-50 flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-slate-500 text-sm">No FAQ items yet. <a href="{{ route('admin.faq.create') }}" class="text-indigo-600 font-semibold">Add your first question</a>.</p>
    </div>
@endif

@endsection

@push('scripts')
<script>
document.getElementById('faq-search').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.admin-table tbody tr').forEach(function(tr) {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
@endpush
