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

    <div class="space-y-6">
        <div class="admin-card">
            <div class="mb-4 flex items-center justify-between gap-4">
                <h3 class="text-xl font-semibold text-slate-900">{{ $isEditing ? 'Edit Currency' : 'Add New Currency' }}</h3>
                @if($isEditing)
                    <a href="{{ route('admin.finance.currency') }}" class="admin-btn-secondary">Cancel</a>
                @endif
            </div>

            <form action="{{ $formAction }}" method="POST" class="space-y-6">
                @csrf
                @if($isEditing)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="admin-form-field">
                        <label class="admin-form-label flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span>Name</span>
                        </label>
                        <input type="text" name="name" value="{{ $currencyName }}" required class="admin-input focus:ring-2 focus:ring-indigo-500/10 transition-all" placeholder="e.g. US Dollar" />
                        <p class="text-[11px] text-slate-400 mt-1">Full name of the currency.</p>
                    </div>

                    <div class="admin-form-field">
                        <label class="admin-form-label flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                            <span>Code</span>
                        </label>
                        <input type="text" name="code" value="{{ $currencyCode }}" required class="admin-input focus:ring-2 focus:ring-indigo-500/10 transition-all font-mono uppercase" placeholder="e.g. USD" />
                        <p class="text-[11px] text-slate-400 mt-1">Standard 3-letter currency code.</p>
                    </div>

                    <div class="admin-form-field">
                        <label class="admin-form-label flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Symbol</span>
                        </label>
                        <input type="text" name="symbol" value="{{ $currencySymbol }}" required class="admin-input focus:ring-2 focus:ring-indigo-500/10 transition-all" placeholder="e.g. $" />
                        <p class="text-[11px] text-slate-400 mt-1">Currency symbol for display.</p>
                    </div>

                    <div class="admin-form-field">
                        <label class="admin-form-label flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            <span>Exchange Rate</span>
                        </label>
                        <input type="number" step="0.000001" min="0" name="exchange_rate" value="{{ $exchangeRate }}" required class="admin-input focus:ring-2 focus:ring-indigo-500/10 transition-all" placeholder="1.000000" />
                        <p class="text-[11px] text-slate-400 mt-1">Rate relative to the default currency.</p>
                    </div>
                </div>

                <div class="bg-slate-50/50 p-5 rounded-2xl border border-slate-200 shadow-sm transition-all hover:bg-slate-100/50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-indigo-600 border border-slate-100">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-[14px] font-bold text-slate-900">Cryptocurrency Mode</h4>
                                <p class="text-[12px] text-slate-500 mt-0.5">Enable specialized handling for digital assets.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <label class="inline-flex relative items-center cursor-pointer">
                                <input type="hidden" name="is_cryptocurrency" value="0">
                                <input type="checkbox" name="is_cryptocurrency" value="1" id="is_cryptocurrency" class="sr-only peer" {{ $isCryptocurrency === 1 ? 'checked' : '' }}>
                                <div class="w-12 h-7 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-indigo-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="admin-form-actions pt-2">
                    <button type="submit" class="admin-btn-primary min-w-[220px] shadow-indigo-200">
                        {{ $isEditing ? 'Update Configuration' : 'Create Currency' }}
                    </button>
                </div>
            </form>
        </div>

        <div class="admin-table-card">
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
                            <td class="admin-table-actions">
                                <div class="flex items-center justify-end" style="gap: 2rem;">
                                    <a href="{{ route('admin.finance.currency.edit', $currency) }}" class="admin-btn-secondary py-2 px-4 text-xs bg-indigo-600 text-white border-none hover:bg-indigo-700 hover:text-white transition-all shadow-sm">Edit</a>
                                    @if($currency->id !== $defaultCurrencyId)
                                        <form action="{{ route('admin.finance.currency.default', $currency) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="admin-btn-secondary py-2 px-4 text-xs bg-emerald-600 text-white border-none hover:bg-emerald-700 transition-all shadow-sm">Set Default</button>
                                        </form>
                                    @endif
                                    @if($currency->id !== $defaultCurrencyId)
                                    <button type="button" onclick="confirmSubmit('del-currency-{{ $currency->id }}', { title: 'Delete Currency', message: 'Are you sure you want to delete this currency? This action cannot be undone.', confirmText: 'Yes, Delete', type: 'danger' })" class="admin-btn-secondary py-2 px-4 text-xs bg-rose-600 text-white border-none hover:bg-rose-700 transition-all shadow-sm" title="Delete this currency">Delete</button>
                                    <form id="del-currency-{{ $currency->id }}" action="{{ route('admin.finance.currency.destroy', $currency) }}" method="POST" style="display:none;">
                                        @csrf @method('DELETE')
                                    </form>
                                    @else
                                    <button type="button" disabled class="admin-btn-secondary py-2 px-4 text-xs bg-rose-600/50 text-white border-none cursor-not-allowed opacity-50" title="Default currency cannot be deleted">Delete</button>
                                    @endif
                                </div>
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
