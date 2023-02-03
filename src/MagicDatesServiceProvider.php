<?php

namespace Librevlad\MagicDates;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class MagicDatesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return  void
     */
    public function register()
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('magic-dates.php'),
            ], 'config');
        }


        $magicDates = [];

        \Event::listen('eloquent.booted*', function ($obj) use (&$magicDates) {
            $class       = str_replace('eloquent.booted: ', '', $obj);
            $modelObject = app($class);
            $dates       = $modelObject->getDates();
            foreach ($dates as $date) {
                $magicDates[ $date ] = $magicDates[ $date ] ?? [];
                $magicDates[ $date ] = array_merge($magicDates[ $date ], [ $class ]);
            }

            foreach ($magicDates as $date => $models) {
                $word = $date;
                if (ends_with($word, '_at')) {
                    $word = substr($word, 0, - 3);
                }

                $methodName = lcfirst(Str::camel($word)) . 'Since';
                Builder::macro($methodName, function ($d) use ($methodName, $date) {
                    if (method_exists($this->model, $methodName) || method_exists($this->model, 'scope' . ucfirst($methodName))) {
                        return $this;
                    }

                    return $this->where($date, '>', $d);
                });

                $methodName = lcfirst(Str::camel($word)) . 'Before';
                Builder::macro($methodName, function ($d) use ($methodName, $date) {
                    if (method_exists($this->model, $methodName) || method_exists($this->model, 'scope' . ucfirst($methodName))) {
                        return $this;
                    }

                    return $this->where($date, '<', $d);
                });

                $methodName = lcfirst(Str::camel($word)) . 'Within';
                Builder::macro($methodName, function ($d) use ($methodName, $date) {
                    if (method_exists($this->model, $methodName) || method_exists($this->model, 'scope' . ucfirst($methodName))) {
                        return $this;
                    }

                    return $this->whereBetween($date, $d);
                });
            }
        });
    }

    /**
     * Bootstrap services.
     *
     * @return  void
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'magic-dates');
    }
}
