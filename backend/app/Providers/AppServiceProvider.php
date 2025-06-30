<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use App\Observers\ModelObserver;
use Illuminate\Support\Facades\View;
use Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $modelPath = app_path('Models');

        // Get all model files dynamically
        $modelFiles = \File::allFiles($modelPath);

        foreach ($modelFiles as $file) {
            $modelClass = 'App\\Models\\' . $file->getFilenameWithoutExtension();

            if (class_exists($modelClass) && $modelClass !== \App\Models\UserActivityLog::class) {
                // Dynamically attach the observer to each model, except UserActivityLog
                $modelClass::observe(ModelObserver::class);
            }
        }

        View::composer('frontend.*', function ($view) {
                if (Auth::user()) {
                    $myac = \App\Models\Account::where('id', auth()->user()->account_id)->first();
                } else {
                    $myac = null;
                }
                $view->with('myac', $myac);
            }); 
    }

}
