<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\RoomType;
use App\Models\RatePlan;
use App\Models\Amenity;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        // Property 1: Luxury Beach Resort
        $property1 = Property::create([
            'name' => 'Grand Oceanview Resort',
            'description' => 'Experience luxury at its finest with stunning ocean views, world-class amenities, and exceptional service. Our resort features pristine beaches, multiple dining options, and spacious rooms designed for your comfort.',
            'location' => 'Miami Beach, Florida',
            'city' => 'Miami Beach',
            'state' => 'Florida',
            'country' => 'United States',
            'address' => '123 Ocean Drive',
            'postal_code' => '33139',
            'status' => 'published',
            'created_by' => 1,
        ]);

        PropertyImage::create([
            'property_id' => $property1->id,
            'image_url' => '/images/properties/oceanview-1.jpg',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        PropertyImage::create([
            'property_id' => $property1->id,
            'image_url' => '/images/properties/oceanview-2.jpg',
            'sort_order' => 2,
            'is_primary' => false,
        ]);

        // Room Type 1: Standard Room
        $standard1 = RoomType::create([
            'property_id' => $property1->id,
            'name' => 'Standard Ocean View',
            'description' => 'Comfortable room with ocean view, king bed, and modern amenities.',
            'base_price' => 199.00,
            'max_occupancy' => 2,
            'total_units' => 50,
        ]);

        $standard1->amenities()->attach([1, 4, 15, 16]); // WiFi, AC, TV, Mini Bar

        // Room Type 2: Deluxe Suite
        $deluxe1 = RoomType::create([
            'property_id' => $property1->id,
            'name' => 'Deluxe Suite',
            'description' => 'Spacious suite with separate living area, ocean view balcony, and premium amenities.',
            'base_price' => 349.00,
            'max_occupancy' => 4,
            'total_units' => 20,
        ]);

        $deluxe1->amenities()->attach([1, 4, 15, 16, 17, 18, 20]); // WiFi, AC, TV, Mini Bar, Safe, Balcony, Sea View

        // Rate Plans for Property 1
        // Summer Season (Higher rates)
        RatePlan::create([
            'room_type_id' => $standard1->id,
            'start_date' => Carbon::create(2026, 6, 1),
            'end_date' => Carbon::create(2026, 8, 31),
            'price_per_night' => 299.00,
            'min_nights' => 2,
        ]);

        RatePlan::create([
            'room_type_id' => $deluxe1->id,
            'start_date' => Carbon::create(2026, 6, 1),
            'end_date' => Carbon::create(2026, 8, 31),
            'price_per_night' => 449.00,
            'min_nights' => 2,
        ]);

        // Holiday Season
        RatePlan::create([
            'room_type_id' => $standard1->id,
            'start_date' => Carbon::create(2026, 12, 20),
            'end_date' => Carbon::create(2027, 1, 5),
            'price_per_night' => 399.00,
            'min_nights' => 3,
        ]);

        // Property 2: City Business Hotel
        $property2 = Property::create([
            'name' => 'Metropolitan Business Hotel',
            'description' => 'Modern business hotel in the heart of downtown. Perfect for business travelers with state-of-the-art conference facilities, high-speed internet, and proximity to major corporate offices.',
            'location' => 'New York City, New York',
            'city' => 'New York',
            'state' => 'New York',
            'country' => 'United States',
            'address' => '456 Broadway',
            'postal_code' => '10013',
            'status' => 'published',
            'created_by' => 1,
        ]);

        PropertyImage::create([
            'property_id' => $property2->id,
            'image_url' => '/images/properties/metro-1.jpg',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        $standard2 = RoomType::create([
            'property_id' => $property2->id,
            'name' => 'Business Standard',
            'description' => 'Efficient workspace, high-speed WiFi, comfortable bed.',
            'base_price' => 179.00,
            'max_occupancy' => 2,
            'total_units' => 100,
        ]);

        $standard2->amenities()->attach([1, 4, 15, 17]); // WiFi, AC, TV, Safe

        $executive2 = RoomType::create([
            'property_id' => $property2->id,
            'name' => 'Executive Suite',
            'description' => 'Premium suite with office space and city views.',
            'base_price' => 299.00,
            'max_occupancy' => 3,
            'total_units' => 30,
        ]);

        $executive2->amenities()->attach([1, 4, 15, 16, 17, 19]); // WiFi, AC, TV, Mini Bar, Safe, Kitchen

        // Property 3: Mountain Lodge (Draft)
        $property3 = Property::create([
            'name' => 'Alpine Mountain Lodge',
            'description' => 'Cozy mountain retreat with stunning alpine views, perfect for winter sports enthusiasts and nature lovers.',
            'location' => 'Aspen, Colorado',
            'city' => 'Aspen',
            'state' => 'Colorado',
            'country' => 'United States',
            'address' => '789 Mountain Road',
            'postal_code' => '81611',
            'status' => 'draft',
            'created_by' => 2,
        ]);

        PropertyImage::create([
            'property_id' => $property3->id,
            'image_url' => '/images/properties/alpine-1.jpg',
            'sort_order' => 1,
            'is_primary' => true,
        ]);

        $cabin = RoomType::create([
            'property_id' => $property3->id,
            'name' => 'Mountain Cabin',
            'description' => 'Rustic cabin with fireplace and mountain views.',
            'base_price' => 249.00,
            'max_occupancy' => 4,
            'total_units' => 25,
        ]);

        $cabin->amenities()->attach([1, 15, 19]); // WiFi, TV, Kitchen
    }
}
