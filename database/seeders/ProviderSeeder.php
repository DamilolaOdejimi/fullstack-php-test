<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    private $providers = [
        ['name'=>'Lagoon Hospitals', 'code'=> 'HMO-A', 'location' => "VI, Lagos", 'provider_type_id' => 1],
        ['name'=>'R Jolad Hospitals', 'code'=> 'RJL', 'location' => "Gbagada, Lagos", 'provider_type_id' => 1],
        ['name'=>'Smile HQ', 'code'=> 'HMO-C', 'location' => "Ilupeju, Lagos", 'provider_type_id' => 2],
        ['name'=>'Acuview Nigeria Limited', 'code'=> 'HMO-D', 'location' => "VI, Lagos", 'provider_type_id' => 3]
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach($this->providers as $provider){
            Provider::firstOrCreate($provider);
        }
    }
}
