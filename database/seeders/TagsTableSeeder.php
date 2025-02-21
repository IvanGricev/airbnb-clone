<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagsTableSeeder extends Seeder
{
    public function run()
    {
        $tags = [
            // Тип жилья
            ['name' => 'Дом', 'category' => 'Тип жилья'],
            ['name' => 'Квартира', 'category' => 'Тип жилья'],
            ['name' => 'Особняк', 'category' => 'Тип жилья'],
            ['name' => 'Замок', 'category' => 'Тип жилья'],
            ['name' => 'Номер в отеле', 'category' => 'Тип жилья'],
            // Наличие удобств
            ['name' => 'Сауна', 'category' => 'Удобства'],
            ['name' => 'Баня', 'category' => 'Удобства'],
            ['name' => 'Джакузи', 'category' => 'Удобства'],
            ['name' => 'Бассейн', 'category' => 'Удобства'],
            ['name' => 'Домашний кинотеатр', 'category' => 'Удобства'],
            // Локация
            ['name' => 'Побережье моря', 'category' => 'Локация'],
            ['name' => 'Побережье реки', 'category' => 'Локация'],
            ['name' => 'Центр города', 'category' => 'Локация'],
            ['name' => 'Лес', 'category' => 'Локация'],
        ];

        foreach ($tags as $tagData) {
            Tag::firstOrCreate([
                'name' => $tagData['name'],
                'category' => $tagData['category'],
            ]);
        }
        
        $this->call(TagsTableSeeder::class);
    }
    
}
