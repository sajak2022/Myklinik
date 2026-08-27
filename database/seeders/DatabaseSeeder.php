<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Country;
use Nakanakaii\Countries\Countries;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

// Menggunakan akses array/object yang aman
        foreach (Countries::all() as $country) {
            // Ubah ke array jika berbentuk object/stdClass, atau sebaliknya
            $countryData = (array) $country;

            Country::updateOrCreate(
                ['code' => $countryData['code'] ?? $countryData['alpha2'] ?? null],
                [
                    'name' => $countryData['name'] ?? null,
                    'dial_code' => $countryData['dialCode'] ?? $countryData['dial_code'] ?? null,
                    'flag' => $countryData['flag'] ?? null,
                ]
            );
        }

        $this->call([
            ReferensiSeeder::class,
            UnitEksternalSeeder::class,
            PenyediaSeeder::class,
            TindakanSeeder::class,
            BarangSeeder::class,
            PasienSeeder::class,
            PegawaiSeeder::class,
        ]);
    }
}
