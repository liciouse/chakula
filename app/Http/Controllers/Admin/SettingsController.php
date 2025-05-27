<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }
    
    public function index()
    {
        try {
            $view = request()->ajax() ? 'admin.settings.content' : 'admin.settings.index';
            
            return view($view, [
                'settings' => [
                    // Add your settings data here
                    'site_name' => config('app.name'),
                    'maintenance_mode' => app()->isDownForMaintenance(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('SettingsController index error: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to load settings. Please try again.');
        }
    }
    
    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'site_name' => 'required|string|max:255',
                'maintenance_mode' => 'sometimes|boolean',
            ]);

            // Update site name in .env file
            $this->updateEnvVariable('APP_NAME', '"' . $validated['site_name'] . '"');

            // Toggle maintenance mode
            if ($request->has('maintenance_mode')) {
                if ($validated['maintenance_mode'] && !app()->isDownForMaintenance()) {
                    Artisan::call('down');
                } else if (!$validated['maintenance_mode'] && app()->isDownForMaintenance()) {
                    Artisan::call('up');
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('SettingsController update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings. Please try again.'
            ], 500);
        }
    }

    protected function updateEnvVariable($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $content = file_get_contents($path);
            
            // If the key exists, replace its value
            if (strpos($content, $key . '=') !== false) {
                $content = preg_replace('/^' . $key . '=.*$/m', $key . '=' . $value, $content);
            } else {
                // If the key doesn't exist, add it
                $content .= "\n" . $key . '=' . $value;
            }
            
            file_put_contents($path, $content);
        }
    }
}