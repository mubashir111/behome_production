{{-- Offer / Discount Card --}}
<div class="bg-white rounded-xl border border-amber-200 shadow-sm overflow-hidden" id="offer-card">
    <div class="px-6 py-4 border-b border-amber-100 bg-amber-50/60 flex items-center gap-2">
        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
        </svg>
        <h2 class="text-sm font-bold text-amber-900 uppercase tracking-wider">Offer / Discount</h2>
    </div>
    <div class="p-6 space-y-5">

        {{-- Discount Amount --}}
        <div class="space-y-1.5">
            <label for="discount" class="text-sm font-medium text-slate-700">
                Discount Amount
                <span class="text-slate-400 font-normal text-xs ml-1">(fixed price off)</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <span class="text-slate-400 font-bold text-xs">−</span>
                </div>
                <input type="number" step="0.01" min="0" name="discount" id="discount"
                       value="{{ old('discount', $product->discount ?? 0) }}"
                       class="w-full pl-8 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-4 focus:ring-amber-100 focus:border-amber-400 transition-all text-sm font-bold"
                       placeholder="0.00"
                       oninput="updateOfferPreview()">
            </div>
        </div>

        {{-- Offer Start Date --}}
        <div class="space-y-1.5">
            <label for="offer_start_date" class="text-sm font-medium text-slate-700">Offer Start Date</label>
            <input type="datetime-local" name="offer_start_date" id="offer_start_date"
                   value="{{ old('offer_start_date', isset($product->offer_start_date) ? \Carbon\Carbon::parse($product->offer_start_date)->format('Y-m-d\TH:i') : '') }}"
                   class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-4 focus:ring-amber-100 focus:border-amber-400 transition-all text-sm"
                   oninput="updateOfferPreview()">
        </div>

        {{-- Offer End Date --}}
        <div class="space-y-1.5">
            <label for="offer_end_date" class="text-sm font-medium text-slate-700">Offer End Date</label>
            <input type="datetime-local" name="offer_end_date" id="offer_end_date"
                   value="{{ old('offer_end_date', isset($product->offer_end_date) ? \Carbon\Carbon::parse($product->offer_end_date)->format('Y-m-d\TH:i') : '') }}"
                   class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-4 focus:ring-amber-100 focus:border-amber-400 transition-all text-sm"
                   oninput="updateOfferPreview()">
        </div>

        {{-- Hero Slider Toggle --}}
        <div class="flex items-center justify-between p-4 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 transition-all cursor-pointer" onclick="toggleSwitch('hero_slider_toggle')">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                </div>
                <div>
                    <div class="text-sm font-bold text-slate-800">Featured on Hero Slider</div>
                    <div class="text-[11px] text-slate-400 font-medium">Show this product in the home promotion slides</div>
                </div>
            </div>
            <div class="relative pointer-events-none">
                <input type="hidden" name="is_hero_slider" value="10">
                <input type="checkbox" name="is_hero_slider" value="5" {{ (isset($product->is_hero_slider) && $product->is_hero_slider == 5) ? 'checked' : '' }} class="sr-only" id="hero_slider_toggle">
                <div id="hero_slider_toggle_track" style="display:block;width:44px;height:24px;{{ (isset($product->is_hero_slider) && $product->is_hero_slider == 5) ? 'background:#6366f1;' : 'background:#e2e8f0;' }}border-radius:12px;position:relative;transition:background 0.2s;">
                    <span id="hero_slider_toggle_knob" style="position:absolute;top:2px;left:2px;width:20px;height:20px;background:white;border-radius:10px;transition:transform 0.2s;box-shadow:0 1px 4px rgba(0,0,0,0.15);{{ (isset($product->is_hero_slider) && $product->is_hero_slider == 5) ? 'transform:translateX(20px);' : '' }}"></span>
                </div>
            </div>
        </div>

        {{-- Live Preview --}}
        <div id="offer-preview" class="rounded-lg border border-slate-100 bg-slate-50 p-4 text-center" style="display:none;">
            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider mb-1">Offer Price Preview</p>
            <div class="flex items-center justify-center gap-3">
                <span id="offer-original-price" class="text-slate-400 line-through text-sm"></span>
                <span id="offer-final-price" class="text-amber-600 font-extrabold text-2xl"></span>
            </div>
            <p id="offer-status-badge" class="mt-2 text-xs font-bold rounded-full px-3 py-1 inline-block"></p>
        </div>

        <p class="text-xs text-slate-400 leading-relaxed">
            Set a discount amount and active date range. The product will automatically appear in the
            <strong class="text-slate-500">Hero Slider offer card</strong> on the homepage when the offer is live.
        </p>
    </div>
</div>

@push('scripts')
<script>
function updateOfferPreview() {
    const sellingPriceInput = document.getElementById('selling_price');
    const discountInput     = document.getElementById('discount');
    const startInput        = document.getElementById('offer_start_date');
    const endInput          = document.getElementById('offer_end_date');
    const preview           = document.getElementById('offer-preview');
    const originalEl        = document.getElementById('offer-original-price');
    const finalEl           = document.getElementById('offer-final-price');
    const badgeEl           = document.getElementById('offer-status-badge');

    if (!sellingPriceInput || !discountInput) return;

    const sellingPrice = parseFloat(sellingPriceInput.value) || 0;
    const discount     = parseFloat(discountInput.value) || 0;

    if (discount <= 0) { preview.style.display = 'none'; return; }

    const offerPrice = Math.max(0, sellingPrice - discount);
    const pct = sellingPrice > 0 ? Math.round((discount / sellingPrice) * 100) : 0;

    originalEl.textContent = '£' + sellingPrice.toFixed(2);
    finalEl.textContent    = '£' + offerPrice.toFixed(2) + (pct > 0 ? ' (' + pct + '% off)' : '');

    // Status badge
    const now   = new Date();
    const start = startInput.value ? new Date(startInput.value) : null;
    const end   = endInput.value   ? new Date(endInput.value)   : null;

    if (start && end) {
        if (now >= start && now <= end) {
            badgeEl.textContent = '🟢 Offer is LIVE right now';
            badgeEl.className   = 'mt-2 text-xs font-bold rounded-full px-3 py-1 inline-block bg-emerald-100 text-emerald-700';
        } else if (now < start) {
            badgeEl.textContent = '🕐 Offer scheduled — not active yet';
            badgeEl.className   = 'mt-2 text-xs font-bold rounded-full px-3 py-1 inline-block bg-amber-100 text-amber-700';
        } else {
            badgeEl.textContent = '🔴 Offer has expired';
            badgeEl.className   = 'mt-2 text-xs font-bold rounded-full px-3 py-1 inline-block bg-red-100 text-red-700';
        }
    } else {
        badgeEl.textContent = '⚠️ Set start & end dates to activate';
        badgeEl.className   = 'mt-2 text-xs font-bold rounded-full px-3 py-1 inline-block bg-slate-100 text-slate-500';
    }

    preview.style.display = 'block';
}

// Run on page load to restore state
document.addEventListener('DOMContentLoaded', updateOfferPreview);

// Also update when selling price changes
document.addEventListener('DOMContentLoaded', function() {
    const sp = document.getElementById('selling_price');
    if (sp) sp.addEventListener('input', updateOfferPreview);
});
</script>
@endpush
