@extends('layouts.app')

@section('content')
<div class="bg-gray-100 min-h-screen p-6 text-gray-800">
    <!-- Top KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-200 shadow-sm border border-gray-300 rounded-lg p-6">
            <h3 class="text-sm text-slate-500">Total Penjualan</h3>
            <p class="text-2xl font-semibold">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
        </div>
        <div class="bg-gray-100 shadow-sm border border-gray-300 rounded-lg p-6">
            <h3 class="text-sm text-slate-500">Produk Terjual</h3>
            <p class="text-2xl font-semibold">{{ number_format($itemsSold) }}</p>
        </div>
        <div class="bg-white shadow-sm border border-slate-200 rounded-lg p-6">
            <h3 class="text-sm text-slate-500">Keuntungan Bersih</h3>
            <p class="text-2xl font-semibold">Rp {{ number_format($netProfit, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white shadow-sm border border-slate-200 rounded-lg p-6">
            <h3 class="text-sm text-slate-500">Transaksi Terbaru</h3>
            <p class="text-2xl font-semibold">{{ $recentCount ?? 0 }}</p>
        </div>
    </div>

    <!-- Middle Input Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- Smart WA Parser -->
        <div class="bg-white shadow-sm border border-slate-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-2">Smart WA Parser</h3>
            <textarea id="wa-input" rows="6" class="w-full p-2 border border-slate-300 rounded" placeholder="Paste WhatsApp message here..."></textarea>
            <button id="parse-btn" type="button" class="mt-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium py-2 px-4 rounded">Parse</button>
        </div>
        <!-- Manual Input -->
        <div class="bg-white shadow-sm border border-slate-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-2">Manual Input</h3>
            <form id="manual-form" method="POST" action="{{ route('transactions.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1" for="product">Produk</label>
                    <select id="product" name="product_id" class="w-full p-2 border border-slate-300 rounded">
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1" for="quantity">Qty</label>
                    <input id="quantity" name="quantity" type="number" min="1" value="1" class="w-full p-2 border border-slate-300 rounded" />
                </div>
                <button type="submit" class="bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium py-2 px-4 rounded">Add to Cart</button>
            </form>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="bg-white shadow-sm border border-slate-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold mb-4">Transaksi Terbaru</h3>
        <table class="w-full table-auto">
            <thead class="bg-slate-100">
                <tr>
                    <th class="px-4 py-2 text-left text-sm text-slate-600">#</th>
                    <th class="px-4 py-2 text-left text-sm text-slate-600">Produk</th>
                    <th class="px-4 py-2 text-left text-sm text-slate-600">Qty</th>
                    <th class="px-4 py-2 text-left text-sm text-slate-600">Total</th>
                    <th class="px-4 py-2 text-left text-sm text-slate-600">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions as $index => $trx)
                    <tr class="border-t">
                        <td class="px-4 py-2 text-sm">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 text-sm">{{ $trx->product->name }}</td>
                        <td class="px-4 py-2 text-sm">{{ $trx->quantity }}</td>
                        <td class="px-4 py-2 text-sm">Rp {{ number_format($trx->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 text-sm">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-2 text-center text-sm text-slate-500">Tidak ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Simple WA parser – extracts product, variant, and quantity
function parseWAText(text) {
    const lines = text.split('\n').filter(l => l.trim().length);
    const results = [];
    const productMap = {
        'aqua': 'Aqua',
        'le minerale': 'Le Minerale',
        'pristine': 'Pristine',
        'vit': 'Vit'
    };
    const variantMap = {
        'galon': 'Galon',
        'dus': 'Dus',
        'kardus': 'Dus',
        'gelas': 'Gelas',
        '220ml': '220ml',
        '330ml': '330ml',
        '600ml': '600ml',
        '1500ml': '1500ml',
        'kosong': 'Kosong'
    };
    const qtyRegex = /\b(\d+)\s*(?:pcs|pc|unit|x)?\b/i;

    lines.forEach(line => {
        const lower = line.toLowerCase();
        // Find product keyword
        let product = null;
        for (const key in productMap) {
            if (lower.includes(key)) { product = productMap[key]; break; }
        }
        if (!product) return; // not a known product line
        // Find variant keyword
        let variant = null;
        for (const key in variantMap) {
            if (lower.includes(key)) { variant = variantMap[key]; break; }
        }
        // Extract quantity
        const qtyMatch = lower.match(qtyRegex);
        const quantity = qtyMatch ? parseInt(qtyMatch[1], 10) : 1;

        results.push({ product, variant, quantity, original: line.trim() });
    });
    return results;
}

document.getElementById('parse-btn').addEventListener('click', function() {
    const text = document.getElementById('wa-input').value;
    const parsed = parseWAText(text);
    console.log('Parsed WA:', parsed);
    // Example: auto‑fill the manual form with the first parsed item
    if (parsed.length) {
        const first = parsed[0];
        // Find product id by matching name – assumes a data attribute on each option
        const select = document.getElementById('product');
        for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].text.trim().toLowerCase().includes(first.product.toLowerCase())) {
                select.selectedIndex = i;
                break;
            }
        }
        document.getElementById('quantity').value = first.quantity;
        // Optionally trigger form submission automatically
        // document.getElementById('manual-form').submit();
    }
});
</script>
@endpush
