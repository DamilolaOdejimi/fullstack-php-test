<?php

namespace Database\Seeders;

use App\Models\ProviderType;
use Illuminate\Database\Seeder;

class ProviderTypeSeeder extends Seeder
{
    private $types = [
        ['name'=>'hospital', 'code'=> 'HOS'],
        ['name'=>'dental clinic', 'code'=> 'DEN'],
        ['name'=>'optical center', 'code'=> 'OPT']
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach($this->types as $type){
            ProviderType::firstOrCreate($type);
        }
    }
}
