<?php

use Illuminate\Database\Seeder;

class ReviewerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [];
        $faker = Faker\Factory::create();
        
        for ($n=0; $n < 50; $n++) { 
            $data[] = [
                'name' => $faker->sentence(),
                'status' => $faker->randomElement(['inactive','active']),
                'randomly_display_questionnaires' => $faker->randomElement(['yes','no']),
                'questionnaires_to_display' => $faker->randomElement([10, 20, 30, 40, 50]),
                'time_limit' => $faker->randomElement([0, 10, 20, 30, 40, 50, 60, 70, 80]),
                'price' => $faker->numberBetween(100, 500),
            ];
        }

        $chunks = array_chunk($data, 50);
        foreach ($chunks as $chunk) {
            \App\Reviewer::insert($chunk);
        }
    }
}
