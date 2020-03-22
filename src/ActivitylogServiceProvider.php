<?php

namespace Spatie\Activitylog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\Contracts\Activity as ActivityContract;
use Spatie\Activitylog\Exceptions\InvalidConfiguration;
use Spatie\Activitylog\Models\Activity as ActivityModel;

class ActivitylogServiceProvider extends ServiceProvider {

    public function boot() {
        $this->publishes([
            __DIR__
            . '/../config/activitylog.php' => config_path('activitylog.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__ . '/../config/activitylog.php',
            'activitylog');

        if (!class_exists('CreateActivityLogTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__
                . '/../migrations/create_activity_log_table.php.stub' => database_path("/migrations/{$timestamp}_create_activity_log_table.php"),
            ], 'migrations');
        }

        if (!class_exists('CreateActivityLogTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__
                . '/../migrations/update_activity_log_table.php.stub' => database_path("/migrations/{$timestamp}_update_activity_log_table.php"),
            ], 'migrations');
        }


    }

    public function register() {
        $this->app->bind('command.activitylog:clean',
            CleanActivitylogCommand::class);

        $this->commands([
            'command.activitylog:clean',
        ]);

        $this->app->bind(ActivityLogger::class);

        $this->app->singleton(ActivityLogStatus::class);
    }

    public static function determineActivityModel(): string {
        $activityModel =
            config('activitylog.activity_model') ?? ActivityModel::class;

        if (!is_a($activityModel, Activity::class, TRUE)
            || !is_a($activityModel, Model::class, TRUE)) {
            throw InvalidConfiguration::modelIsNotValid($activityModel);
        }

        return $activityModel;
    }

    public static function getActivityModelInstance(): ActivityContract {
        $activityModelClassName = self::determineActivityModel();

        return new $activityModelClassName();
    }

}
