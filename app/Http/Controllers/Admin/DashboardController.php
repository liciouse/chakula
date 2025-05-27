<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Review;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        try {
            $stats = $this->getDashboardStats();
            $recentActivities = $this->getRecentActivities();
            
            $view = request()->ajax() 
                ? 'admin.dashboard.content' 
                : 'admin.dashboard.index';
                
            return view($view, [
                'stats' => $stats,
                'activities' => $recentActivities,
                'chartData' => $this->getChartData()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Dashboard Error: ' . $e->getMessage());
            
            return view('admin.dashboard.index', [
                'stats' => $this->getFallbackStats(),
                'activities' => [],
                'chartData' => []
            ]);
        }
    }

    protected function getDashboardStats()
    {
        return [
            'users' => $this->getCountIfTableExists('users', User::class),
            'posts' => $this->getCountIfTableExists('posts', Post::class),
            'comments' => $this->getCountIfTableExists('comments', Comment::class),
            'pendingReviews' => $this->getPendingReviewsCount(),
            'newUsers' => $this->getNewUsersCount(),
            'activePosts' => $this->getActivePostsCount()
        ];
    }

    protected function getRecentActivities()
    {
        try {
            $activities = [];
            
            // Recent users
            if (Schema::hasTable('users')) {
                $activities['users'] = User::latest()
                    ->limit(5)
                    ->get(['id', 'name', 'email', 'created_at']);
            }
            
            // Recent posts
            if (Schema::hasTable('posts')) {
                $activities['posts'] = Post::with('user')
                    ->latest()
                    ->limit(5)
                    ->get(['id', 'title', 'user_id', 'created_at']);
            }
            
            // Recent comments
            if (Schema::hasTable('comments')) {
                $activities['comments'] = Comment::with(['user', 'post'])
                    ->latest()
                    ->limit(5)
                    ->get(['id', 'content', 'user_id', 'post_id', 'created_at']);
            }
            
            return $activities;
            
        } catch (\Exception $e) {
            Log::warning("Failed loading recent activities: " . $e->getMessage());
            return [];
        }
    }

    protected function getChartData()
    {
        try {
            $days = 30;
            $range = Carbon::now()->subDays($days);
            
            $data = [
                'labels' => [],
                'users' => [],
                'posts' => [],
                'comments' => []
            ];
            
            // Generate labels for each day
            for ($i = $days; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('M j');
                $data['labels'][] = $date;
            }
            
            // Get user registration data
            if (Schema::hasTable('users')) {
                $usersData = User::where('created_at', '>=', $range)
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->pluck('count', 'date');
                
                $this->fillChartData($data['users'], $usersData, $days);
            }
            
            // Get posts data
            if (Schema::hasTable('posts')) {
                $postsData = Post::where('created_at', '>=', $range)
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->pluck('count', 'date');
                
                $this->fillChartData($data['posts'], $postsData, $days);
            }
            
            // Get comments data
            if (Schema::hasTable('comments')) {
                $commentsData = Comment::where('created_at', '>=', $range)
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->pluck('count', 'date');
                
                $this->fillChartData($data['comments'], $commentsData, $days);
            }
            
            return $data;
            
        } catch (\Exception $e) {
            Log::warning("Failed generating chart data: " . $e->getMessage());
            return [];
        }
    }

    protected function fillChartData(&$array, $data, $days)
    {
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $array[] = $data[$date] ?? 0;
        }
    }

    protected function getCountIfTableExists($tableName, $modelClass)
    {
        if (Schema::hasTable($tableName)) {
            try {
                return $modelClass::count();
            } catch (\Exception $e) {
                Log::warning("Failed counting $tableName: " . $e->getMessage());
                return 0;
            }
        }
        return 0;
    }

    protected function getPendingReviewsCount()
    {
        if (Schema::hasTable('reviews')) {
            try {
                return Review::where('status', 'pending')->count();
            } catch (\Exception $e) {
                Log::warning("Failed counting pending reviews: " . $e->getMessage());
                return 0;
            }
        }
        return 0;
    }

    protected function getNewUsersCount()
    {
        if (Schema::hasTable('users')) {
            try {
                return User::where('created_at', '>=', Carbon::now()->subDays(7))->count();
            } catch (\Exception $e) {
                Log::warning("Failed counting new users: " . $e->getMessage());
                return 0;
            }
        }
        return 0;
    }

    protected function getActivePostsCount()
    {
        if (Schema::hasTable('posts')) {
            try {
                return Post::where('status', 'published')
                    ->where('created_at', '>=', Carbon::now()->subDays(30))
                    ->count();
            } catch (\Exception $e) {
                Log::warning("Failed counting active posts: " . $e->getMessage());
                return 0;
            }
        }
        return 0;
    }

    protected function getFallbackStats()
    {
        return [
            'users' => 0,
            'posts' => 0,
            'comments' => 0,
            'pendingReviews' => 0,
            'newUsers' => 0,
            'activePosts' => 0
        ];
    }
}