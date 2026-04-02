@extends('layouts.admin')

@section('title', 'Barcodes')

@section('content')
<div class="max-w-[900px] mx-auto pb-12">
    <div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h2 class="text-3xl font-bold font-outfit text-slate-900">Barcode Types</h2>
            <p class="text-slate-500 mt-1">Manage barcode formats used across your products.</p>
        </div>
    </div>

    @include('admin._alerts')

    <!-- Add New Barcode -->
    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm p-6 mb-6">
        <h3 class="text-base font-bold text-slate-700 mb-4">Add New Barcode Type</h3>
        <form method="POST" action="{{ route('admin.barcodes.store') }}" class="flex items-center gap-3">
            @csrf
            <input type="text" name="name" placeholder="e.g. EAN-13, QR Code, UPC-A…"
                class="flex-1 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
                value="{{ old('name') }}" required />
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all text-sm">
                + Add
            </button>
        </form>
        @error('name')
            <p class="text-rose-500 text-xs mt-2">{{ $message }}</p>
        @enderror
    </div>

    <!-- Barcodes List -->
    <div class="bg-white rounded-[2.5rem] border border-slate-300 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200">
                        <th class="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">#</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Barcode Name</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Created</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($barcodes as $barcode)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-8 py-5 font-mono text-xs text-slate-400">{{ $barcode->id }}</td>
                            <td class="px-6 py-5 font-bold text-slate-800">{{ $barcode->name }}</td>
                            <td class="px-6 py-5 text-sm text-slate-500">{{ $barcode->created_at?->format('M d, Y') }}</td>
                            <td class="px-6 py-5 text-center">
                                <button type="button"
                                    onclick="confirmSubmit('del-barcode-{{ $barcode->id }}', { title: 'Delete Barcode Type', message: 'Remove this barcode type?', confirmText: 'Yes, Delete', type: 'danger' })"
                                    class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-100 hover:bg-rose-50 transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                                <form id="del-barcode-{{ $barcode->id }}" action="{{ route('admin.barcodes.destroy', $barcode->id) }}" method="POST" style="display:none;">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-8 py-20 text-center text-slate-400 font-medium italic">
                                No barcode types found. Add one above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($barcodes->hasPages())
            <div class="px-8 py-6 bg-slate-50 border-t border-slate-200">
                {{ $barcodes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
