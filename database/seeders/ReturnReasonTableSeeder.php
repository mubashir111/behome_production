<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\ReturnReason;
use Illuminate\Database\Seeder;

class ReturnReasonTableSeeder extends Seeder
{

    public array $returnReasons = [
        'Ordered the wrong product or size',
        'Shipped the wrong product or size',
        'The product was damaged or defective',
        'The product did not match the description',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('DEMO', false)) {
            foreach ($this->returnReasons as $returnReason) {
                ReturnReason::create([
                    'title'   => $returnReason,
                    'details' => "There is a great divergence between the way a product is depicted by the merchant and the actual appearance or functionality of the product. I want to refund it",
                    'status'  => Status::ACTIVE,
                ]);
            }
        }
    }
}