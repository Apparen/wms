<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [['id' => 1, 'name' => 'Gudang Pusat Bandung', 'location' => 'Jl. Soekarno-Hatta No. 123, Bandung', 'manager_name' => 'Budi Santoso', 'phone' => '081234567890', 'description' => 'Pusat distribusi utama untuk wilayah Jawa Barat', 'is_active' => true,], ['id' => 2, 'name' => 'Jakarta Distribution Center', 'location' => 'Kawasan Industri Pulogadung, Jakarta', 'manager_name' => 'Siti Aminah', 'phone' => '081398765432', 'description' => 'Hub utama untuk pengiriman Jabodetabek', 'is_active' => false,], ['id' => 3, 'name' => 'Surabaya Branch', 'location' => 'Jl. Rungkut Industri Raya, Surabaya', 'manager_name' => 'Andi Wijaya', 'phone' => '085612345678', 'description' => 'Gudang penyimpanan stok kain dan material kasar', 'is_active' => true,], ['id' => 4, 'name' => 'Yogyakarta Warehouse', 'location' => 'Jl. Magelang KM 5, Yogyakarta', 'manager_name' => 'Rina Permata', 'phone' => '081900112233', 'description' => 'Gudang transit untuk wilayah Jawa Tengah', 'is_active' => true,], ['id' => 5, 'name' => 'Medan Logistics Hub', 'location' => 'Kawasan Industri Medan (KIM), Medan', 'manager_name' => 'Zulfikar', 'phone' => '081122334455', 'description' => 'Pusat pengiriman wilayah Sumatera', 'is_active' => false,],];
        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}
