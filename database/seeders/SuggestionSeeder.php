<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Suggestion;


class SuggestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['category' => 'bedroom', 'image_url' => 'http://www.thewowdecor.com/wp-content/uploads/2015/07/cozy-master-bedrooms-idea.jpg'],
            ['category' => 'bedroom', 'image_url' => 'https://i0.wp.com/magzhouse.com/wp-content/uploads/2019/09/Incredible-Modern-Bedroom-Design-Ideas-19.jpg?ssl=1'],
            ['category' => 'kitchen', 'image_url' => 'https://tse3.mm.bing.net/th/id/OIP.SeJ01jt7_IdBLSvKk2fT2gHaFU?pid=Api&P=0&h=220'],
            ['category' => 'living room', 'image_url' => 'https://tse1.mm.bing.net/th/id/OIP.ZHegE_l83xI02wxLr7garwHaFj?pid=Api&P=0&h=220'],
            ['category' => 'bathroom', 'image_url' => 'https://tse2.mm.bing.net/th/id/OIP.RlAomPhJGyqhMFgnnFHnWQHaFj?pid=Api&P=0&h=220'],
            ['category' => 'balcony', 'image_url' => 'https://tse3.mm.bing.net/th/id/OIP.eYeYmL4npGY_GSA8Gv0dWwHaHa?pid=Api&P=0&h=220'],

        ];

        foreach ($data as $item) {
            Suggestion::create($item);
        }
    }
}
