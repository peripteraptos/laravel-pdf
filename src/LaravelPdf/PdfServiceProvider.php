<?php

namespace niklasravnsborg\LaravelPdf;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;

class PdfServiceProvider extends IlluminateServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     * @throws \Exception
     */
    public function register(): void
    {
        $configPath = __DIR__ . '/../config/mpdf.php';
        $this->mergeConfigFrom($configPath, 'mpdf');

        $this->app->bind('mpdf.options', function ($app) {
            $defines = $app['config']->get('mpdf.defines');

            if ($defines) {
                $options = [];
                /**
                 * @var string $key
                 * @var mixed $value
                 */
                foreach ($defines as $key => $value) {
                    if ($key == "fontDir") {
                        $options[$key] = array_merge((new ConfigVariables())->getDefaults()['fontDir'], $value);
                    } elseif ($key == "fontdata") {
                        $options[$key] = array_merge((new FontVariables())->getDefaults()['fontdata'], $value);
                    } else {
                        $options[$key] = $value;
                    }
                }
            } else {
                $options = $app['config']->get('mpdf.options');
            }

            return $options;
        });

        $this->app->bind('mpdf', function ($app) {

            $options = $app->make('mpdf.options');
            $mpdf = new Mpdf($options);
            $path = realpath(base_path('public'));
            if ($path === false) {
                throw new \RuntimeException('Cannot resolve public path');
            }
            $mpdf->setBasePath($path);

            return $mpdf;
        });
        $this->app->alias('mpdf', Mpdf::class);

        $this->app->bind('mpdf.wrapper', function ($app) {
            return new Pdf($app['mpdf'], $app['config'], $app['files'], $app['view']);
        });
    }

    public function boot(): void
    {
        if (!$this->isLumen()) {
            $configPath = __DIR__ . '/../config/mpdf.php';
            $this->publishes([$configPath => config_path('mpdf.php')], 'config');
        }
    }

    /**
     * Check if package is running under Lumen app
     */
    protected function isLumen(): bool
    {
        return Str::contains($this->app->version(), 'Lumen') === true;
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return ['mpdf', 'mpdf.options', 'mpdf.wrapper'];
    }
}