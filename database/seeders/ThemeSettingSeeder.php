<?php

namespace Database\Seeders;

use App\Models\ThemeSetting;
use Illuminate\Database\Seeder;

class ThemeSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ThemeSetting::firstOrCreate(
            ['name' => 'Default Theme'],
            [
                'settings' => [
                    'colors' => [
                        'primary' => '#2563eb',
                        'secondary' => '#111827',
                        'background' => '#ffffff',
                        'text' => '#111827',
                    ],
                    'typography' => [
                        'font_family' => 'Inter',
                    ],
                    'layout' => [
                        'page_width' => '1280px',
                    ],
                ],
                'published_settings' => [
                    'colors' => [
                        'primary' => '#2563eb',
                        'secondary' => '#111827',
                        'background' => '#ffffff',
                        'text' => '#111827',
                    ],
                    'typography' => [
                        'font_family' => 'Inter',
                    ],
                    'layout' => [
                        'page_width' => '1280px',
                    ],
                ],
                'published_at' => now(),
            ]
        );
    }
}
