<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Division;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Known facts only: the 8 divisions (capital coordinates) and their 64 districts.
 * Session types, attendee types and statuses are enums (app/Enums). Campuses, roadshow
 * districts, speakers, sessions, sponsors and award categories wait for the client.
 */
class StructureSeeder extends Seeder
{
    public const DIVISIONS = [
        'Dhaka' => [23.8103, 90.4125, ['Dhaka', 'Faridpur', 'Gazipur', 'Gopalganj', 'Kishoreganj', 'Madaripur', 'Manikganj', 'Munshiganj', 'Narayanganj', 'Narsingdi', 'Rajbari', 'Shariatpur', 'Tangail']],
        'Chattogram' => [22.3569, 91.7832, ['Bandarban', 'Brahmanbaria', 'Chandpur', 'Chattogram', "Cox's Bazar", 'Cumilla', 'Feni', 'Khagrachhari', 'Lakshmipur', 'Noakhali', 'Rangamati']],
        'Rajshahi' => [24.3745, 88.6042, ['Bogura', 'Chapainawabganj', 'Joypurhat', 'Naogaon', 'Natore', 'Pabna', 'Rajshahi', 'Sirajganj']],
        'Khulna' => [22.8456, 89.5403, ['Bagerhat', 'Chuadanga', 'Jashore', 'Jhenaidah', 'Khulna', 'Kushtia', 'Magura', 'Meherpur', 'Narail', 'Satkhira']],
        'Barishal' => [22.7010, 90.3535, ['Barguna', 'Barishal', 'Bhola', 'Jhalokathi', 'Patuakhali', 'Pirojpur']],
        'Sylhet' => [24.8949, 91.8687, ['Habiganj', 'Moulvibazar', 'Sunamganj', 'Sylhet']],
        'Rangpur' => [25.7439, 89.2752, ['Dinajpur', 'Gaibandha', 'Kurigram', 'Lalmonirhat', 'Nilphamari', 'Panchagarh', 'Rangpur', 'Thakurgaon']],
        'Mymensingh' => [24.7471, 90.4203, ['Jamalpur', 'Mymensingh', 'Netrokona', 'Sherpur']],
    ];

    public function run(): void
    {
        $sort = 0;
        foreach (self::DIVISIONS as $name => [$lat, $lng, $districts]) {
            $division = Division::updateOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'lat' => $lat, 'lng' => $lng, 'sort' => $sort++]);
            foreach ($districts as $d) {
                District::updateOrCreate(['slug' => Str::slug($d)], ['name' => $d, 'division_id' => $division->id]);
            }
        }
    }
}
