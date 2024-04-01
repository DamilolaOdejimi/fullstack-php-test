<?php

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    private $providers = [
        ['name'=>'Lagoon Hospitals', 'location' => "VI, Lagos", 'provider_type_id' => 1],
        ['name'=>'R Jolad Hospitals', 'location' => "Gbagada, Lagos", 'provider_type_id' => 1],
        ['name'=>'Smile HQ', 'location' => "Ilupeju, Lagos", 'provider_type_id' => 2],
        ['name'=>'Acuview Nigeria Limited', 'location' => "VI, Lagos", 'provider_type_id' => 3]
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
