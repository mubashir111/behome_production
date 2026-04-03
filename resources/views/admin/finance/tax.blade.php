@extends('layouts.admin')

@section('title', 'Tax Rules')

@section('content')
<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Tax Management</h2>
            <p class="admin-page-subtitle">Create and manage tax rules for product pricing.</p>
        </div>
        <a href="{{ route('admin.finance.currency') }}" class="admin-btn-secondary">Currency Settings</a>
    </div>

    @include('admin._alerts')

    <div class="admin-card-grid">
        <div class="admin-card">
            <h3 class="text-xl font-semibold text-slate-900 mb-4">Add New Tax Rule</h3>
            <form action="{{ route('admin.finance.tax.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="admin-form-field">
                    <label class="admin-form-label">Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="admin-form-input" placeholder="VAT" />
                </div>
                <div class="admin-form-field">
                    <label class="admin-form-label">Code</label>
                    <input type="text" name="code" value="{{ old('code') }}" required class="admin-form-input" placeholder="VAT-5" />
                </div>
                <div class="admin-form-field">
                    <label class="admin-form-label">Rate (%)</label>
                    <input type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate') }}" required class="admin-form-input" placeholder="5.00" />
                </div>
                <div class="admin-form-field">
                    <label class="admin-form-label">Status</label>
                    <select name="status" class="admin-form-input">
                        <option value="5" {{ old('status', 5) == 5 ? 'selected' : '' }}>Active</option>
                        <option value="10" {{ old('status') == 10 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn-primary">Add Tax Rule</button>
                </div>
            </form>
        </div>

        <div class="admin-table-card lg:col-span-2">
            <div class="admin-card-header px-5 md:px-6 pt-5 md:pt-6">
                <h3 class="text-xl font-semibold text-slate-900">Tax Rules</h3>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th class="admin-table-head-cell">Name</th>
                            <th class="admin-table-head-cell">Code</th>
                            <th class="admin-table-head-cell">Rate</th>
                            <th class="admin-table-head-cell">Status</th>
                            <th class="admin-table-head-cell text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        @forelse($taxes as $tax)
                        <tr class="admin-table-row">
                            <td class="admin-table-cell text-sm text-slate-700">{{ $tax->name }}</td>
                            <td class="admin-table-cell text-sm text-slate-600">{{ $tax->code }}</td>
                            <td class="admin-table-cell text-sm text-slate-700">{{ number_format($tax->tax_rate, 2) }}%</td>
                            <td class="admin-table-cell">@if($tax->status == 5)<span class="px-2 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded">Active</span>@else<span class="px-2 py-1 text-xs font-semibold text-rose-700 bg-rose-50 rounded">Inactive</span>@endif</td>
                            <td class="admin-table-actions space-x-1">
                                <a href="#" class="admin-btn-secondary py-2 px-3 text-xs">Edit</a>
                                <button type="button" onclick="confirmSubmit('del-tax-{{ $tax->id }}', { title: 'Delete Tax Rule', message: 'Are you sure you want to delete this tax rule? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })" class="admin-btn-secondary py-2 px-3 text-xs text-rose-600 hover:bg-rose-50">Delete</button>
                                <form id="del-tax-{{ $tax->id }}" action="{{ route('admin.finance.tax.destroy', $tax) }}" method="POST" style="display:none;">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="admin-table-cell py-8 text-center text-slate-500">No tax rules found yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>{{ $taxes->links() ?? '' }}</div>
</div>
@endsection
