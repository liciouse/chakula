<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->integer('views')->default(0);
            
            // Add status column instead of just boolean published
            $table->enum('status', ['draft', 'pending', 'published'])->default('draft');
            
            // Add category relationship
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            
            // Keep published_at for when articles are published
            $table->timestamp('published_at')->nullable();
            
            // Add SEO meta fields (optional)
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'status']);
            $table->index('status');
            $table->index('category_id');
            $table->index('published_at');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};