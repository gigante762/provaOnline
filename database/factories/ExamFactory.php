<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => '1',
            'title' => 'Exame '.rand(0,1000),
            'content' => $this->faker->text,
            'open_at' => now(),
            'close_at' => now()->addDay(rand(1,20)),
            'minutes' => rand(3,10),
        ];
    }
}
