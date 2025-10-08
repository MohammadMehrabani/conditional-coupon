<?php

namespace MohammadMehrabani\ConditionalCoupon;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class ConditionalCouponServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/conditional-coupon.php', 'conditional-coupon'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/conditional-coupon.php' => config_path('conditional-coupon.php'),
        ], 'conditional-coupon-config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_coupons_table.php.stub' => $this->getMigrationFileName('create_coupons_tables.php'),
        ], 'conditional-coupon-migrations');
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    protected function getMigrationFileName(string $migrationFileName): string
    {
        $timestamp = date('Y_m_d_His');

        $filesystem = $this->app->make(Filesystem::class);

        return Collection::make([$this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR])
            ->flatMap(fn ($path) => $filesystem->glob($path.'*_'.$migrationFileName))
            ->push($this->app->databasePath()."/migrations/{$timestamp}_{$migrationFileName}")
            ->first();
    }
}
