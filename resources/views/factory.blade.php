<?php if (!defined('ABSPATH')) die();?>
namespace WPWCore\Database\Factories;

use WPWCore\Database\Eloquent\Factories\Factory;

class {{ $className }}Factory extends Factory
{
    /**
    * Define the model's default state.
    *
    * @return array
    <string, mixed>
    */
        public function definition(): array
        {
            $faker = \Faker\Factory::create();
            return [
            @foreach($properties as $name => $property)
                '{{$name}}' => {!! $property !!},
            @endforeach
            ];
        }
}

