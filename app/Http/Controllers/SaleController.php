<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['customer', 'vehicle'])
                     ->latest()
                     ->paginate(10);
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::all();
        $vehicles = Vehicle::all();
        return view('sales.create', compact('customers', 'vehicles'));
    }

    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'product_id' => 'required|string',
            'quantity'   => 'required|integer|min:1',
        ]);

        // Find product by code
        $product = Product::where('code', $request->product_id)->first();

        if (! $product) {
            return back()->withErrors(['product_id' => 'Produk tidak ditemukan.'])->withInput();
        }

        // Stock check
        if ($product->stock !== null && $product->stock < $request->quantity) {
            return back()->withErrors(['quantity' => 'Stok tidak mencukupi.'])->withInput();
        }

        // Dynamic pricing for le_minerale_galon
        $unitPrice = $product->price;
        if ($product->code === 'le_minerale_galon' && $request->quantity > 15) {
            $unitPrice = 20500;
        }

        $totalPrice = $unitPrice * $request->quantity;
        $totalCost  = $product->cost_price * $request->quantity;
        $profit     = $totalPrice - $totalCost;

        // Create sale record
        Sale::create([
            'user_id'        => auth()->id(),
            'product_id'     => $product->id,
            'quantity'       => $request->quantity,
            'price_per_unit' => $unitPrice,
            'total_price'    => $totalPrice,
            'profit'         => $profit,
            'date'           => now(),
        ]);

        // Decrement stock
        if ($product->stock !== null) {
            $product->decrement('stock', $request->quantity);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Transaksi berhasil disimpan.');
    }
}