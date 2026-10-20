<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use PDF;

class DashboardController extends Controller
{
    // Export PDF
    public function exportPDF()
    {
        $data = $this->getReportData();
        $pdf = PDF::loadView('reports.sales', $data);
        return $pdf->download('sales_report.pdf');
    }

    // Export Excel
    public function exportExcel()
    {
        return Excel::download(new SalesReportExport($this->getReportData()), 'sales_report.xlsx');
    }

    // Ambil semua data laporan
    private function getReportData()
    {
        $sales = Sale::with(['customer', 'vehicle', 'product'])->latest()->get();

        $totalSales = $sales->sum('total_amount');
        $totalCost = $sales->sum(function ($sale) {
            return $this->calculateSaleCost($sale);
        });
        $netProfit = $totalSales - $totalCost;

        return [
            'sales' => $sales,
            'totalSales' => $totalSales,
            'totalCost' => $totalCost,
            'netProfit' => $netProfit
        ];
    }

    // Hitung biaya per sale berdasarkan skenario kendaraan & bahan bakar
    private function calculateSaleCost($sale)
    {
        $productCost = $sale->quantity * ($sale->product->cost_price ?? 0);

        $vehicle = $sale->vehicle;
        $fuelCost = 0;
        $tollFee = 0;

        if ($vehicle) {
            $capacity = $vehicle->capacity ?? 0; // kapasitas galon
            $fuelPrice = $vehicle->fuel_price ?? 0; // harga per liter
            $fuelEfficiency = $vehicle->fuel_efficiency ?? 0; // km per liter
            $defaultToll = $vehicle->default_toll_fee ?? 0; // default biaya tol

            $distance = $sale->distance ?? 0; // jarak perjalanan (km)
            $trips = $capacity > 0 ? ceil($sale->quantity / $capacity) : 1;

            // Biaya BBM = (jarak / efisiensi) * harga per liter * jumlah trip
            if ($fuelEfficiency > 0) {
                $fuelCost = ($distance / $fuelEfficiency) * $fuelPrice * $trips;
            }

            // Biaya tol = default toll fee * jumlah trip
            $tollFee = $defaultToll * $trips;
        }

        return $productCost + $fuelCost + $tollFee;
    }

    public function index(Request $request)
    {
        // Filter for chart (keep existing logic)
        $filter = $request->get('filter', 'monthly'); // default monthly
        $monthLabels = [];
        $monthSales = [];
        $monthCost = [];
        $monthProfit = [];

        if ($filter === 'daily') {
            for ($i = 6; $i >= 0; $i--) {
                $day = Carbon::now()->subDays($i);
                $sales = Sale::with(['vehicle', 'product'])
                    ->whereDate('created_at', $day->toDateString())
                    ->get();

                $monthLabels[] = $day->format('d M');
                $salesTotal = $sales->sum('total_price');
                $costTotal = $sales->sum(fn($s) => $this->calculateSaleCost($s));
                $monthSales[] = $salesTotal;
                $monthCost[] = $costTotal;
                $monthProfit[] = $salesTotal - $costTotal;
            }
        } elseif ($filter === 'weekly') {
            for ($i = 6; $i >= 0; $i--) {
                $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
                $sales = Sale::with(['vehicle', 'product'])
                    ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                    ->get();

                $monthLabels[] = 'Minggu ' . $startOfWeek->format('d M');
                $salesTotal = $sales->sum('total_price');
                $costTotal = $sales->sum(fn($s) => $this->calculateSaleCost($s));
                $monthSales[] = $salesTotal;
                $monthCost[] = $costTotal;
                $monthProfit[] = $salesTotal - $costTotal;
            }
        } else { // monthly
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $sales = Sale::with(['vehicle', 'product'])
                    ->whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->get();

                $monthLabels[] = $month->format('M Y');
                $salesTotal = $sales->sum('total_price');
                $costTotal = $sales->sum(fn($s) => $this->calculateSaleCost($s));
                $monthSales[] = $salesTotal;
                $monthCost[] = $costTotal;
                $monthProfit[] = $salesTotal - $costTotal;
            }
        }

        // KPI calculations
        $totalSales = Sale::sum('total_price');
        $itemsSold = Sale::sum('quantity');
        $totalCost = Sale::all()->sum(fn($sale) => $this->calculateSaleCost($sale));
        $netProfit = $totalSales - $totalCost;

        $recentTransactions = Sale::with('product')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        $recentCount = $recentTransactions->count();

        $products = Product::all();
        return view('dashboard', compact(
            'monthLabels',
            'monthSales',
            'monthCost',
            'monthProfit',
            'filter',
            'totalSales',
            'itemsSold',
            'netProfit',
            'recentCount',
            'recentTransactions',
            'products'
        ));
    }
        $filter = $request->get('filter', 'monthly'); // default monthly

        $monthLabels = [];
        $monthSales = [];
        $monthCost = [];
        $monthProfit = [];

        if ($filter === 'daily') {
            for ($i = 6; $i >= 0; $i--) {
                $day = Carbon::now()->subDays($i);
                $sales = Sale::with(['vehicle', 'product'])
                            ->whereDate('created_at', $day->toDateString())
                            ->get();

                $monthLabels[] = $day->format('d M');
                $salesTotal = $sales->sum('total_amount');
                $costTotal = $sales->sum(fn($s) => $this->calculateSaleCost($s));

                $monthSales[] = $salesTotal;
                $monthCost[] = $costTotal;
                $monthProfit[] = $salesTotal - $costTotal;
            }
        } elseif ($filter === 'weekly') {
            for ($i = 6; $i >= 0; $i--) {
                $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
                $sales = Sale::with(['vehicle', 'product'])
                            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                            ->get();

                $monthLabels[] = 'Minggu ' . $startOfWeek->format('d M');
                $salesTotal = $sales->sum('total_amount');
                $costTotal = $sales->sum(fn($s) => $this->calculateSaleCost($s));

                $monthSales[] = $salesTotal;
                $monthCost[] = $costTotal;
                $monthProfit[] = $salesTotal - $costTotal;
            }
        } else { // monthly
            for ($i = 5; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $sales = Sale::with(['vehicle', 'product'])
                            ->whereMonth('created_at', $month->month)
                            ->whereYear('created_at', $month->year)
                            ->get();

                $monthLabels[] = $month->format('M Y');
                $salesTotal = $sales->sum('total_amount');
                $costTotal = $sales->sum(fn($s) => $this->calculateSaleCost($s));

                $monthSales[] = $salesTotal;
                $monthCost[] = $costTotal;
                $monthProfit[] = $salesTotal - $costTotal;
            }
        }

        return view('dashboard', compact('monthLabels', 'monthSales', 'monthCost', 'monthProfit', 'filter'));
    }
}
