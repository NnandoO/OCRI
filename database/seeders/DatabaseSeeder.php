<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            Convenios2005Seeder::class,
            Convenios2006Seeder::class,
            Convenios2007Seeder::class,
            Convenios2008Seeder::class,
            Convenios2009Seeder::class,
            Convenios2010Seeder::class,
            Convenios2011Seeder::class,
            Convenios2012Seeder::class,
            Convenios2013Seeder::class,
            Convenios2014Seeder::class,
            Convenios2015Seeder::class,
            Convenios2016Seeder::class,
            Convenios2017Seeder::class,
            Convenios2018Seeder::class,
            Convenios2019Seeder::class,
            Convenios2020Seeder::class,
            Convenios2021Seeder::class,
            Convenios2022Seeder::class,
            Convenios2023Seeder::class,
            Convenios2024Seeder::class,
            Convenios2025Seeder::class,
        ]);
    }
}