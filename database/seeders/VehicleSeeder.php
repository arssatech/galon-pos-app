<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;

class VehicleSeeder extends Seeder
{
    /**
     * Run the vehicle seeds.
     */
    public function run(): void
    {
        $vehicles = [
            [
                'name' => 'Truck A',
                'type' => 'Truck',
                'capacity' => 200,
                'fuel_efficiency' => 8.5,
                'fuel_price' => 15000,
                'default_toll_fee' => 25000,
            ],
            [
                'name' => 'Van B',
                'type' => 'Van',
                'capacity' => 100,
                'fuel_efficiency' => 10.2,
                'fuel_price' => 15000,
                'default_toll_fee' => 15000,
            ],
            [
                'name' => 'Motorcycle C',
                'type' => 'Motorcycle',
                'capacity' => 20,
                'fuel_efficiency' => 25.0,
                'fuel_price' => 15000,
                'default_toll_fee' => 5000,
            ],
        ];

        foreach ($vehicles as $data) {
            Vehicle::create($data);
        }
    }
}
