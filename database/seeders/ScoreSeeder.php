<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScoreSeeder extends Seeder
{
    public function run()
    {
        \App\Models\Score::create([
            'semester' => 'Semester 1',
            'mataKuliah' => 'Matif 1',
            'gambar' => 'gambar/BjMKzQctirmiNLl0UMbhmBde6vSPHB0MLO4VCc4B.jpg',
            'mine' => false,
            'email' => ''
        ]);
    }
}
