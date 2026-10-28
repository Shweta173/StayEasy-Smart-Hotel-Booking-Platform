<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['name' => 'Free WiFi', 'icon' => 'wifi'],
            ['name' => 'Swimming Pool', 'icon' => 'pool'],
            ['name' => 'Parking', 'icon' => 'parking'],
            ['name' => 'Air Conditioning', 'icon' => 'ac'],
            ['name' => 'Restaurant', 'icon' => 'restaurant'],
            ['name' => 'Room Service', 'icon' => 'room-service'],
            ['name' => 'Gym/Fitness Center', 'icon' => 'gym'],
            ['name' => 'Spa', 'icon' => 'spa'],
            ['name' => 'Bar/Lounge', 'icon' => 'bar'],
            ['name' => 'Conference Rooms', 'icon' => 'conference'],
            ['name' => 'Pet Friendly', 'icon' => 'pet'],
            ['name' => 'Laundry Service', 'icon' => 'laundry'],
            ['name' => 'Airport Shuttle', 'icon' => 'shuttle'],
            ['name' => 'Wheelchair Accessible', 'icon' => 'wheelchair'],
            ['name' => 'TV', 'icon' => 'tv'],
            ['name' => 'Mini Bar', 'icon' => 'minibar'],
            ['name' => 'Safe', 'icon' => 'safe'],
            ['name' => 'Balcony', 'icon' => 'balcony'],
            ['name' => 'Kitchen/Kitchenette', 'icon' => 'kitchen'],
            ['name' => 'Sea View', 'icon' => 'sea-view'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::create($amenity);
        }
    }
}
