<?php

declare(strict_types=1);

namespace KommandHub\FolderProtection\Tests;

use KommandHub\FolderProtection\FolderProtectionServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            FolderProtectionServiceProvider::class,
        ];
    }
}
