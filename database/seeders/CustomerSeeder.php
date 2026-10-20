<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Vehicle;

class CustomerSeeder extends Seeder
{
    /**
     * Run the customer seeds.
     */
    public function run(): void
    {
        // Assume vehicles have been seeded already
        $vehicles = Vehicle::all();
        if ($vehicles->isEmpty()) {
            return; // safety guard
        }

        $customers = [
            [
                'name' => 'Budi Santoso',
                'address' => 'Jl. Merdeka No.10, Jakarta',
                'distance_km' => 12.5,
                'gallon_type' => 'Aqua',
                'price_per_gallon' => 24000,
                'default_vehicle_id' => $vehicles->first()->id,
            ],
            [
                'name' => 'Siti Nurhaliza',
                'address' => 'Jl. Sudirman No.5, Bandung',
                'distance_km' => 30.0,
                'gallon_type' => 'Le Minerale',
                'price_per_gallon' => 23000,
                'default_vehicle_id' => $vehicles->last()->id,
            ],
            [
                'name' => 'Andi Wijaya',
                'address' => 'Jl. Gajah Mada No.25, Surabaya',
                'distance_km' => 45.2,
                'gallon_type' => 'Pristine',
                'price_per_gallon' => 25000,
                'default_vehicle_id' => $vehicles->skip(1)->first()->id,
            ],
        ];

        foreach ($customers as $data) {
            Customer::create($data);
        }
    }
}
