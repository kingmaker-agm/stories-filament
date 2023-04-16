<?php

namespace App\Providers;

use App\Filament\Forms\Components\Actions\OpenUrlAction;
use Filament\Forms\Components\Component;
use Illuminate\Support\ServiceProvider;

class FilamentCustomProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureComponents();
    }

    private function configureComponents(): void
    {
        OpenUrlAction::configureUsing(
            function (OpenUrlAction $action) {
                $action->url(function (Component $component) {
                    $url = $component->getState();
                    return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
                });
            }
        );
    }
}
