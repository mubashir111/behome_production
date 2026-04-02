<?php

namespace Database\Seeders;


use App\Enums\Ask;
use App\Models\Subscriber;
use Illuminate\Database\Seeder;


class SubscriberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        if (env('DEMO', false)) {
            Subscriber::insert([
                [
                    'email' => 'subscriber@example.com',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
