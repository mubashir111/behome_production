@extends('layouts.admin')

@section('title', 'Delivery Areas')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Delivery Areas</h2>
            <p class="admin-page-subtitle">Define shipping costs by country, state, or city. Leave state/city blank for a country-wide rate.</p>
        </div>
        <a href="{{ route('admin.settings.shipping') }}" class="admin-btn-secondary">Back to Shipping Settings</a>
    </div>

    @include('admin._alerts')

    <div class="admin-card">
        <h3 class="text-xl font-semibold text-slate-900 mb-4">Add New Area</h3>
        <form action="{{ route('admin.shipping.order-areas.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @csrf
            <div class="admin-form-field">
                <label class="admin-form-label">Country <span class="text-rose-500">*</span></label>
                <input type="text" name="country" value="{{ old('country') }}" class="admin-form-input @error('country') border-rose-400 @enderror" placeholder="e.g. Bangladesh" required>
                @error('country')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="admin-form-field">
                <label class="admin-form-label">State / Region <span class="text-slate-400 text-xs">(optional)</span></label>
                <input type="text" name="state" value="{{ old('state') }}" class="admin-form-input" placeholder="e.g. Dhaka Division">
            </div>
            <div class="admin-form-field">
                <label class="admin-form-label">City <span class="text-slate-400 text-xs">(optional)</span></label>
                <input type="text" name="city" value="{{ old('city') }}" class="admin-form-input" placeholder="e.g. Dhaka">
            </div>
            <div class="admin-form-field">
                <label class="admin-form-label">Shipping Cost ({{ $currencySymbol }}) <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 text-sm pointer-events-none">{{ $currencySymbol }}</span>
                    <input type="number" step="0.01" min="0" name="shipping_cost" value="{{ old('shipping_cost') }}"
                        class="admin-form-input pl-7 @error('shipping_cost') border-rose-400 @enderror" placeholder="0.00" required>
                </div>
                @error('shipping_cost')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="admin-form-field">
                <label class="admin-form-label">Status <span class="text-rose-500">*</span></label>
                <select name="status" class="admin-form-input" required>
                    <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="admin-form-field flex items-end">
                <button type="submit" class="admin-btn-primary w-full">Add Area</button>
            </div>
        </form>
    </div>

    <div class="admin-table-card">
        <div class="admin-card-header px-5 md:px-6 pt-5 md:pt-6">
            <h3 class="text-xl font-semibold text-slate-900">Configured Areas</h3>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead class="admin-table-head">
                    <tr>
                        <th class="admin-table-head-cell">Country</th>
                        <th class="admin-table-head-cell">State</th>
                        <th class="admin-table-head-cell">City</th>
                        <th class="admin-table-head-cell">Shipping Cost</th>
                        <th class="admin-table-head-cell">Status</th>
                        <th class="admin-table-head-cell text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    @forelse($areas as $area)
                    {{-- View Row --}}
                    <tr class="admin-table-row" id="row-{{ $area->id }}">
                        <td class="admin-table-cell text-sm text-slate-700">{{ $area->country }}</td>
                        <td class="admin-table-cell text-sm text-slate-500">{{ $area->state ?: '—' }}</td>
                        <td class="admin-table-cell text-sm text-slate-500">{{ $area->city ?: '—' }}</td>
                        <td class="admin-table-cell text-sm font-medium text-slate-800">{{ $currencySymbol }}{{ number_format($area->shipping_cost, 2) }}</td>
                        <td class="admin-table-cell">
                            @if($area->status == 1)
                                <span class="px-2 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold text-rose-700 bg-rose-50 rounded">Inactive</span>
                            @endif
                        </td>
                        <td class="admin-table-actions space-x-1">
                            <button type="button" onclick="toggleEditRow({{ $area->id }})" class="admin-btn-secondary py-2 px-3 text-xs">Edit</button>
                            <form action="{{ route('admin.shipping.order-areas.update', $area) }}" method="POST" class="inline">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="{{ $area->status ? 0 : 1 }}">
                                <button type="submit" class="admin-btn-secondary py-2 px-3 text-xs">
                                    {{ $area->status ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <button type="button" onclick="confirmSubmit('del-area-{{ $area->id }}', { title: 'Delete Delivery Area', message: 'Are you sure you want to delete this delivery area?', confirmText: 'Yes, Delete', type: 'danger' })" class="admin-btn-secondary py-2 px-3 text-xs text-rose-600 hover:bg-rose-50">Delete</button>
                            <form id="del-area-{{ $area->id }}" action="{{ route('admin.shipping.order-areas.destroy', $area) }}" method="POST" style="display:none;">
                                @csrf @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    {{-- Edit Row --}}
                    <tr id="edit-row-{{ $area->id }}" style="display:none;" class="bg-slate-50">
                        <td colspan="6" class="admin-table-cell py-4">
                            <form action="{{ route('admin.shipping.order-areas.update', $area) }}" method="POST"
                                  class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
                                @csrf @method('PUT')
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Country *</label>
                                    <input type="text" name="country" value="{{ $area->country }}" class="admin-form-input text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">State</label>
                                    <input type="text" name="state" value="{{ $area->state }}" class="admin-form-input text-sm" placeholder="optional">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">City</label>
                                    <input type="text" name="city" value="{{ $area->city }}" class="admin-form-input text-sm" placeholder="optional">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Cost ({{ $currencySymbol }}) *</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-slate-400 text-xs pointer-events-none">{{ $currencySymbol }}</span>
                                        <input type="number" step="0.01" min="0" name="shipping_cost" value="{{ $area->shipping_cost }}" class="admin-form-input text-sm pl-5" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Status *</label>
                                    <select name="status" class="admin-form-input text-sm">
                                        <option value="1" {{ $area->status == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ $area->status == 0 ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="admin-btn-primary py-2 px-3 text-xs flex-1">Save</button>
                                    <button type="button" onclick="toggleEditRow({{ $area->id }})" class="admin-btn-secondary py-2 px-3 text-xs">Cancel</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="admin-table-cell py-10 text-center text-slate-500">
                            No delivery areas configured yet. Add one above.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $areas->links() }}</div>
</div>

<script>
function toggleEditRow(id) {
    const viewRow = document.getElementById('row-' + id);
    const editRow = document.getElementById('edit-row-' + id);
    const isHidden = editRow.style.display === 'none';
    editRow.style.display = isHidden ? 'table-row' : 'none';
}
</script>
@endsection
