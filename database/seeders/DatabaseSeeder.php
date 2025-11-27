<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Runs database/thingy-seed.sql as-is.
     * The SQL reads current_setting('app.schema', true) and defaults to 'thingy'.
     */
    public function run(): void
    {
        // Get schema name from environment (e.g., .env or .env.testing)
        $schema = env('DB_SCHEMA');

        // Load the raw SQL file
        $path = base_path('database/lbaw-seed.sql');
        $sql = file_get_contents($path);

        // If DB_SCHEMA is set, expose it to the SQL script
        // (the script reads it via current_setting('app.schema', true))
        if ($schema !== null) {
            DB::statement("SELECT set_config('app.schema', ?, false)", [$schema]);
        }

        // Run the SQL script
        DB::unprepared($sql);

        // Copy photos from database/photos into the public storage disk under listing_photos/
        try {
            $srcDir = base_path('database/photos');
            if (is_dir($srcDir)) {
                if (!Storage::disk('public')->exists('listing_photos')) {
                    Storage::disk('public')->makeDirectory('listing_photos');
                }

                $files = scandir($srcDir);
                $copied = 0;
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') continue;
                    $full = $srcDir . DIRECTORY_SEPARATOR . $file;
                    if (!is_file($full)) continue;
                    $dest = 'listing_photos/' . $file;
                    try {
                        Storage::disk('public')->put($dest, file_get_contents($full));
                        $copied++;
                    } catch (\Exception $e) {
                        $this->command?->error('Failed to copy ' . $file . ': ' . $e->getMessage());
                    }
                }
                $this->command?->info('Copied ' . $copied . ' photos into storage (public/listing_photos).');
            } else {
                $this->command?->warn('No database/photos directory found; skipping photo copy.');
            }
        } catch (\Exception $e) {
            $this->command?->error('Photo copying failed: ' . $e->getMessage());
        }

        // Show a message in the Artisan console
        $this->command?->info('Database seeded using schema: ' . ($schema ?? 'thingy (default)'));
    }
}
