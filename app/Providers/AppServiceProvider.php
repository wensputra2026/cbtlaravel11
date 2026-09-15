<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Injeksi $appSetting dinamis ke seluruh view Blade secara global
        View::composer('*', function ($view) {
            try {
                $setting = Setting::current();
                $view->with('appSetting', $setting);
            } catch (\Throwable $e) {
                $view->with('appSetting', new Setting([
                    'sekolah'       => 'SMA Negeri Benlutu',
                    'nama_aplikasi' => 'SMANBEN-CBT',
                ]));
            }
        });

        // Injeksi $activeTahunAjaran, $selectedTahunAjaran, dan $allTahunAjaran
        View::composer(['layouts.admin', 'layouts.guru', 'admin.*', 'guru.*', 'exam.*'], function ($view) {
            try {
                $academicService = app(\App\Services\AcademicYear\AcademicYearService::class);
                $view->with([
                    'activeTahunAjaran'   => $academicService->getActiveYear(),
                    'activeTp'            => $academicService->getActiveYear(),
                    'selectedTahunAjaran' => $academicService->getSelectedYear(),
                    'allTahunAjaran'      => $academicService->getAllYears(),
                ]);
            } catch (\Throwable $e) {}
        });
    }
}
