<?php

namespace Alps\Bookmarker;

use Statamic\Statamic;

class ServiceProvider extends \Statamic\Providers\AddonServiceProvider
{
    protected $stylesheets = [
    ];

    protected $scripts = [
    ];

    protected $publishables = [
    ];

    protected $fieldtypes = [
    ];

    public function bootAddon()
    {
        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', [
                '--tag' => 'statamic-bookmarker',
                '--force' => true,
            ]);
        });
    }
}
