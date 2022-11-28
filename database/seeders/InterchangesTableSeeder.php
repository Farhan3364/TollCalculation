<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class InterchangesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('interchanges')->insert([
            ['name' => 'Zero Point', 'km' => 0],
            ['name' => 'NS Interchange', 'km' => 5],
            ['name' => 'Ph4 Interchange', 'km' => 10],
            ['name' => 'Ferozpur Interchange', 'km' => 17],
            ['name' => 'Lake City Interchange', 'km' => 24],
            ['name' => 'Raiwand Interchange', 'km' => 29],
            ['name' => 'Bahria Interchange', 'km' => 34],
        ]);
    }
}
