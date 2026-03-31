@extends('layouts.admin')

@section('title', 'Currency Rates')

@section('content')
<div class="admin-page">
    @php
        $isEditing = !blank($editCurrency);
        $formAction = $isEditing ? route('admin.finance.currency.update', $editCurrency) : route('admin.finance.currency.store');
        $currencyName = old('name', $editCurrency->name ?? '');
        $currencyCode = old('code', $editCurrency->code ?? '');
        $currencySymbol = old('symbol', $editCurrency->symbol ?? '');
        $exchangeRate = old('exchange_rate', isset($editCurrency) ? (string) $editCurrency->exchange_rate : '');
        $isCryptocurrency = (int) old('is_cryptocurrency', $editCurrency->is_cryptocurrency ?? 0);
    @endphp

    <div class="admin-page-header">
        <div>
            <h2 class="admin-page-title">Currency Management</h2>
            <p class="admin-page-subtitle">Add, update, and remove supported currencies.</p>
        </div>
        <a href="{{ route('admin.finance.tax') }}" class="admin-btn-secondary">Tax Settings</a>
    </div>

    @include('admin._alerts')

    <div class="admin-card-grid">
        <div class="admin-card">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h3 class="text-xl font-semibold text-slate-900">{{ $isEditing ? 'Edit Currency' : 'Add New Currency' }}</h3>
                @if($isEditing)
                    <a href="{{ route('admin.finance.currency') }}" class="admin-btn-secondary">Cancel</a>
                @endif
            </div>

            <form action="{{ $formAction }}" method="POST" class="space-y-4">
                @csrf
                @if($isEditing)
                    @method('PUT')
                @endif
                <div class="admin-form-field">
                    <label class="admin-form-label">Name</label>
                    <input type="text" name="name" value="{{ $currencyName }}" required class="admin-form-input" placeholder="US Dollar" />
                </div>
                <div class="admin-form-field">
                    <label class="admin-form-label">Code</label>
                    <input type="text" name="code" value="{{ $currencyCode }}" required class="admin-form-input" placeholder="USD" />
                </div>
                <div class="admin-form-field">
                    <label class="admin-form-label">Symbol</label>
                    <input type="text" name="symbol" value="{{ $currencySymbol }}" required class="admin-form-input" placeholder="$" />
                </div>
                <div class="admin-form-field">
                    <label class="admin-form-label">Exchange Rate</label>
                    <input type="number" step="0.000001" min="0" name="exchange_rate" value="{{ $exchangeRate }}" required class="admin-form-input" placeholder="1.000000" />
                </div>
                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_cryptocurrency" value="0" />
                    <input type="checkbox" id="is_cryptocurrency" name="is_cryptocurrency" value="1" {{ $isCryptocurrency === 1 ? 'checked' : '' }} class="h-4 w-4 text-indigo-600 border-slate-300 rounded" />
                    <label for="is_cryptocurrency" class="text-sm text-slate-600">Is Cryptocurrency</label>
                </div>

                <div class="admin-form-actions">
                    <button type="submit" class="admin-btn-primary">{{ $isEditing ? 'Update Currency' : 'Add Currency' }}</button>
                </div>
            </form>
        </div>

        <div class="admin-table-card lg:col-span-2">
            <div class="admin-card-header px-5 md:px-6 pt-5 md:pt-6">
                <h3 class="text-xl font-semibold text-slate-900">Currency List</h3>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead class="admin-table-head">
                        <tr>
                            <th class="admin-table-head-cell">Name</th>
                            <th class="admin-table-head-cell">Code</th>
                            <th class="admin-table-head-cell">Symbol</th>
                            <th class="admin-table-head-cell">Rate</th>
                            <th class="admin-table-head-cell">Crypto</th>
                            <th class="admin-table-head-cell text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="admin-table-body">
                        @forelse($currencies as $currency)
                        <tr class="admin-table-row">
                            <td class="admin-table-cell text-sm text-slate-700">
                                <div class="flex items-center gap-2">
                                    <span>{{ $currency->name }}</span>
                                    @if($currency->id === $defaultCurrencyId)
                                        <span class="px-2 py-1 text-xs font-semibold text-indigo-700 bg-indigo-50 rounded">Default</span>
                                    @endif
                                </div>
                            </td>
                            <td class="admin-table-cell text-sm font-mono text-slate-600">{{ $currency->code }}</td>
                            <td class="admin-table-cell text-sm text-slate-600">{{ $currency->symbol }}</td>
                            <td class="admin-table-cell text-sm text-slate-700">{{ number_format($currency->exchange_rate, 6) }}</td>
                            <td class="admin-table-cell">@if($currency->is_cryptocurrency) <span class="px-2 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded">Yes</span> @else <span class="px-2 py-1 text-xs font-semibold text-slate-500 bg-slate-100 rounded">No</span> @endif</td>
                            <td class="admin-table-actions space-x-1">
                                <a href="{{ route('admin.finance.currency.edit', $currency) }}" class="admin-btn-secondary py-2 px-3 text-xs">Edit</a>
                                @if($currency->id !== $defaultCurrencyId)
                                    <form action="{{ route('admin.finance.currency.default', $currency) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="admin-btn-secondary py-2 px-3 text-xs text-indigo-700 hover:bg-indigo-50">Set Default</button>
                                    </form>
                                @endif
                                @if($currency->id !== $defaultCurrencyId)
                                <button type="button" onclick="confirmSubmit('del-currency-{{ $currency->id }}', { title: 'Delete Currency', message: 'Are you sure you want to delete this currency? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })" class="admin-btn-secondary py-2 px-3 text-xs text-rose-600 hover:bg-rose-50" title="Delete this currency">Delete</button>
                                <form id="del-currency-{{ $currency->id }}" action="{{ route('admin.finance.currency.destroy', $currency) }}" method="POST" style="display:none;">
                                    @csrf @method('DELETE')
                                </form>
                                @else
                                <button type="button" disabled class="admin-btn-secondary py-2 px-3 text-xs text-rose-600 opacity-50 cursor-not-allowed" title="Default currency cannot be deleted">Delete</button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="admin-table-cell py-8 text-center text-slate-500">No currencies found yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        {{ $currencies->links() ?? '' }}
    </div>
</div>
@endsection
