<?php

namespace Database\Seeders;

use App\Interfaces\BatchingTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HmoSeeder extends Seeder
{
    private $hmos = [
        ['name'=>'Reliance HMO', 'code'=> 'HMO-A', 'hmo_email' => 'hmo-a@email.com', 'batching_type' => BatchingTypes::SENT_DATE],
        ['name'=>'AxaMansard HMO', 'code'=> 'HMO-B', 'hmo_email' => 'hmo-b@email.com', 'batching_type' => BatchingTypes::SENT_DATE],
        ['name'=>'Avon Medical HMO', 'code'=> 'HMO-C', 'hmo_email' => 'hmo-c@email.com', 'batching_type' => BatchingTypes::ENCOUNTER_DATE],
        ['name'=>'Hygeia HMO', 'code'=> 'HMO-D', 'hmo_email' => 'hmo-d@email.com', 'batching_type' => BatchingTypes::ENCOUNTER_DATE],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('hmos')->insert($this->hmos);
    }
}
