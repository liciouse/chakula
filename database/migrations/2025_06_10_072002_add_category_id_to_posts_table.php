<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Add category_id column
            $table->unsignedBigInteger('category_id')->nullable()->after('excerpt');
            
            // Add foreign key constraint if categories table exists
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            
            // Add other missing columns that might be needed
            if (!Schema::hasColumn('posts', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('content');
            }
            
            if (!Schema::hasColumn('posts', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            
            if (!Schema::hasColumn('posts', 'meta_keywords')) {
                $table->string('meta_keywords')->nullable()->after('meta_description');
            }
            
            if (!Schema::hasColumn('posts', 'tags')) {
                $table->json('tags')->nullable()->after('meta_keywords');
            }
            
            if (!Schema::hasColumn('posts', 'featured_image')) {
                $table->string('featured_image')->nullable()->after('tags');
            }
            
            if (!Schema::hasColumn('posts', 'views')) {
                $table->unsignedInteger('views')->default(0)->after('featured_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['category_id']);
            
            // Drop columns
            $table->dropColumn([
                'category_id',
                'meta_title',
                'meta_description', 
                'meta_keywords',
                'tags',
                'featured_image',
                'views'
            ]);
        });
    }
};