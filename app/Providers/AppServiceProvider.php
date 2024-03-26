<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Puklipo\Vapor\Middleware\GzipResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        Model::shouldBeStrict(! $this->app->isProduction());

        Gate::define('admin', fn (User $user) => 1);

        GzipResponse::encodeWhen(function (Request $request, mixed $response): bool {
            return in_array('gzip', $request->getEncodings())
                && $request->method() === 'GET'
                && ! $request->hasHeader('X-Livewire')
                && function_exists('gzencode')
                && ! $response->headers->contains('Content-Encoding', 'gzip')
                && ! $response instanceof BinaryFileResponse;
        });
    }
}
