<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Ticket;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Ticket::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Konser Dewa 19',
            'price' => 500000,
            'stock' => 100,
        ]);
    }
}
