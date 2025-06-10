<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddViewsColumn extends Command
{
    protected $signature = 'posts:add-views';
    protected $description = 'Add views column to posts table';

    public function handle()
    {
        try {
            // Check if column exists
            if (Schema::hasColumn('posts', 'views')) {
                $this->info('Views column already exists in posts table.');
                return;
            }

            // Add the column
            Schema::table('posts', function ($table) {
                $table->unsignedBigInteger('views')->default(0);
            });

            $this->info('Views column added successfully to posts table.');
            
            // Verify it was added
            if (Schema::hasColumn('posts', 'views')) {
                $this->info('✓ Verification: Views column exists.');
            } else {
                $this->error('✗ Verification failed: Views column not found.');
            }

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}