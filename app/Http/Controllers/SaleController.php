<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\Product;
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
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id'  => 'required|exists:vehicles,id',
            'product_id'  => 'required|exists:products,id',
            'quantity'    => 'required|integer|min:1',
        ]);

        // Load relations
        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        $product = Product::findOrFail($request->product_id);

        // Stock check & update
        if ($product->stock !== null) {
            if ($product->stock < $request->quantity) {
                return back()->withErrors(['quantity' => 'Stok tidak mencukupi.'])->withInput();
            }
            $product->decrement('stock', $request->quantity);
        }

        // Determine vehicle capacity (galon per trip)
        $capacity = 0;
        if (strtolower($vehicle->type) === 'motor') {
            $capacity = 4; // example capacity
        } elseif (strtolower($vehicle->type) === 'mobil') {
            $capacity = 20;
        }

        // Calculate trips needed
        $trips = $capacity > 0 ? ceil($request->quantity / $capacity) : 1;

        // Fuel and toll cost (simple model)
        $fuelCostPerTrip = $vehicle->fuel_cost ?? 0;
        $totalFuelCost = $fuelCostPerTrip * $trips;
        $tollFee = $vehicle->toll_fee ?? 0;

        // Total price (selling price) and cost (for profit calculation)
        $totalPrice = $product->price * $request->quantity + $totalFuelCost + $tollFee;
        $totalCost  = $product->cost_price * $request->quantity + $totalFuelCost + $tollFee;
        $profit     = $totalPrice - $totalCost;

        // Save transaction
        $sale = Sale::create([
            'customer_id' => $request->customer_id,
            'vehicle_id'  => $request->vehicle_id,
            'product_id'  => $request->product_id,
            'quantity'    => $request->quantity,
            'trips'       => $trips,
            'fuel_cost'   => $totalFuelCost,
            'toll_fee'    => $tollFee,
            'total_price' => $totalPrice,
            'total_cost'  => $totalCost,
            'profit'      => $profit,
        ]);

        return redirect()->route('sales.index')
            ->with('success', 'Transaksi berhasil disimpan.');
    }
        // Validasi input
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        // Ambil data relasi
        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        $product = Product::findOrFail($request->product_id);

        // Tentukan kapasitas kendaraan
        $capacity = 0;
        if (strtolower($vehicle->type) === 'motor') {
            $capacity = 4;
        } elseif (strtolower($vehicle->type) === 'mobil') {
            $capacity = 20;
        }

        // Hitung jumlah trip
        $total_quantity = $request->quantity;
        $trips = ceil($total_quantity / $capacity);

        // Hitung biaya bahan bakar (per trip)
        $fuel_cost_per_trip = $vehicle->fuel_cost;
        $total_fuel_cost = $fuel_cost_per_trip * $trips;

        // Untuk sekarang toll_fee = 0 (nanti bisa otomatis berdasarkan jarak)
        $toll_fee = 0;

        // Total harga produk
        $product_total_price = $product->price * $total_quantity;

        // Total keseluruhan
        $total_price = $product_total_price + $total_fuel_cost + $toll_fee;

        // Simpan transaksi
        $sale = Sale::create([
            'customer_id' => $request->customer_id,
            'vehicle_id' => $request->vehicle_id,
            'product_id' => $request->product_id,
            'quantity' => $total_quantity,
            'trips' => $trips,
            'fuel_cost' => $total_fuel_cost,
            'toll_fee' => $toll_fee,
            'total_price' => $total_price
        ]);

        return redirect()->route('sales.index')->with('success', 'Transaksi berhasil disimpan.');
    }

} 