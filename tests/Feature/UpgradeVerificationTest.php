<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpgradeVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that Laravel version is 12.x
     */
    public function test_laravel_version_is_12(): void
    {
        $version = App::version();

        $this->assertStringStartsWith('12.', $version, "Expected Laravel 12.x but got {$version}");
    }

    /**
     * Test that core service providers are loaded
     */
    public function test_core_service_providers_are_loaded(): void
    {
        $loadedProviders = collect(app()->getLoadedProviders());

        $requiredProviders = [
            \Illuminate\Auth\AuthServiceProvider::class,
            \Illuminate\Cache\CacheServiceProvider::class,
            \Illuminate\Database\DatabaseServiceProvider::class,
            \Illuminate\Filesystem\FilesystemServiceProvider::class,
            \Illuminate\Routing\RoutingServiceProvider::class,
            \Illuminate\View\ViewServiceProvider::class,
        ];

        foreach ($requiredProviders as $provider) {
            $this->assertTrue(
                $loadedProviders->has($provider),
                "Expected {$provider} to be loaded"
            );
        }
    }

    /**
     * Test that the files binding exists
     */
    public function test_files_binding_exists(): void
    {
        $this->assertTrue(
            app()->bound('files'),
            'The files binding should exist'
        );

        $files = app('files');
        $this->assertInstanceOf(\Illuminate\Filesystem\Filesystem::class, $files);
    }

    /**
     * Test that key routes exist
     */
    public function test_key_routes_exist(): void
    {
        $routes = ['login', 'logout', 'register', 'apps.index'];

        foreach ($routes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Expected route '{$routeName}' to exist"
            );
        }
    }

    /**
     * Test that middleware aliases are registered
     */
    public function test_middleware_aliases_are_registered(): void
    {
        $middleware = app('router')->getMiddleware();

        $requiredAliases = ['auth', 'guest', 'signed', 'throttle', 'verified'];

        foreach ($requiredAliases as $alias) {
            $this->assertArrayHasKey(
                $alias,
                $middleware,
                "Expected middleware alias '{$alias}' to be registered"
            );
        }
    }

    /**
     * Test that Inertia middleware is configured
     */
    public function test_inertia_middleware_is_configured(): void
    {
        $webMiddlewareGroup = app('router')->getMiddlewareGroups()['web'] ?? [];

        $hasInertiaMiddleware = collect($webMiddlewareGroup)->contains(function ($m) {
            return is_string($m) && str_contains($m, 'HandleInertiaRequests');
        });

        $this->assertTrue(
            $hasInertiaMiddleware,
            'HandleInertiaRequests middleware should be in web group'
        );
    }

    /**
     * Test that Ziggy class can be instantiated
     */
    public function test_ziggy_can_be_instantiated(): void
    {
        $ziggy = new \Tighten\Ziggy\Ziggy();

        $this->assertInstanceOf(\Tighten\Ziggy\Ziggy::class, $ziggy);

        $array = $ziggy->toArray();
        $this->assertIsArray($array);
    }

    /**
     * Test that app boots without errors
     */
    public function test_application_boots_successfully(): void
    {
        $this->assertFalse(
            app()->isDownForMaintenance(),
            'Application should not be in maintenance mode'
        );

        $this->assertTrue(
            app()->isBooted(),
            'Application should be booted'
        );
    }

    /**
     * Test that config is loaded correctly
     */
    public function test_config_is_loaded(): void
    {
        $this->assertNotNull(config('app.name'));
        $this->assertNotNull(config('app.key'));
        $this->assertEquals('sqlite', config('database.default'));
    }
}
