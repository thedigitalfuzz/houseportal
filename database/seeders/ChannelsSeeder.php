<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Channel;

class ChannelsSeeder extends Seeder
{
    public function run(): void
    {
        Channel::updateOrCreate(['name' => 'general'], [
            'type' => 'public',
            'created_by' => 0, // admin id
        ]);

        Channel::updateOrCreate(['name' => 'wallet-managers'], [
            'type' => 'private',
            'created_by' => 0,
        ]);

        Channel::updateOrCreate(['name' => 'entrystaffs'], [
            'type' => 'private',
            'created_by' => 0,
        ]);

        Channel::updateOrCreate(['name' => 'announcements'], [
            'type' => 'announcement',
            'created_by' => 0,
        ]);
    }
}
