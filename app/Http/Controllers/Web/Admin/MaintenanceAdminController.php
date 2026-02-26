<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class MaintenanceAdminController extends Controller
{
    /**
     * Enable maintenance marker file used by Apache rewrite rule.
     */
    public function enable(Request $request): RedirectResponse
    {
        try {
            $payload = json_encode([
                'enabled_at' => now('Asia/Jakarta')->toDateTimeString(),
                'enabled_by' => $request->user()?->email,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            file_put_contents($this->markerPath(), $payload !== false ? $payload : '');
            app()->maintenanceMode()->activate([
                'except' => ['admin*', 'login', 'logout', 'up'],
                'redirect' => null,
                'retry' => 120,
                'refresh' => null,
                'secret' => null,
                'status' => 503,
                'template' => view('errors.503', ['retryAfter' => 120])->render(),
            ]);

            $maintenanceStubPath = base_path('vendor/laravel/framework/src/Illuminate/Foundation/Console/stubs/maintenance-mode.stub');
            if (is_file($maintenanceStubPath)) {
                file_put_contents(
                    storage_path('framework/maintenance.php'),
                    (string) file_get_contents($maintenanceStubPath)
                );
            }

            return back()->with('success', 'Maintenance mode berhasil diaktifkan.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['maintenance' => 'Gagal mengaktifkan maintenance mode.']);
        }
    }

    /**
     * Disable maintenance marker file.
     */
    public function disable(): RedirectResponse
    {
        try {
            if (app()->maintenanceMode()->active()) {
                app()->maintenanceMode()->deactivate();
            }

            $maintenanceBootstrap = storage_path('framework/maintenance.php');
            if (is_file($maintenanceBootstrap)) {
                @unlink($maintenanceBootstrap);
            }

            $path = $this->markerPath();
            if (is_file($path)) {
                @unlink($path);
            }

            return back()->with('success', 'Maintenance mode berhasil dinonaktifkan.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['maintenance' => 'Gagal menonaktifkan maintenance mode.']);
        }
    }

    private function markerPath(): string
    {
        return public_path('maintenance.enable');
    }
}
