<?php

declare(strict_types=1);

namespace KommandHub\FolderProtection\Tests;

use Illuminate\Support\Facades\Route;
use KommandHub\FolderProtection\Http\Middleware\FolderProtection;

class FolderProtectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/', function () {
            return 'ok';
        })->middleware(FolderProtection::class);
    }

    public function test_enabled_requires_authentication(): void
    {
        config(['folder-protection.enabled' => true]);
        config(['folder-protection.user' => 'user']);
        config(['folder-protection.password' => 'pass']);

        $response = $this->get('/');

        $response->assertStatus(401);
        $response->assertHeader('WWW-Authenticate', 'Basic realm="Protected Area"');
    }

    public function test_accepts_valid_credentials(): void
    {
        config(['folder-protection.enabled' => true]);
        config(['folder-protection.user' => 'user']);
        config(['folder-protection.password' => 'pass']);

        $response = $this->get('/', [
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW' => 'pass',
        ]);

        $response->assertStatus(200);
        $response->assertSee('ok');
    }

    public function test_accepts_valid_credentials_via_authorization_header(): void
    {
        config(['folder-protection.enabled' => true]);
        config(['folder-protection.user' => 'user']);
        config(['folder-protection.password' => 'pass']);

        $response = $this->get('/', [
            'Authorization' => 'Basic '.base64_encode('user:pass'),
        ]);

        $response->assertStatus(200);
        $response->assertSee('ok');
    }

    public function test_rejects_invalid_credentials_via_authorization_header(): void
    {
        config(['folder-protection.enabled' => true]);
        config(['folder-protection.user' => 'user']);
        config(['folder-protection.password' => 'pass']);

        $response = $this->get('/', [
            'Authorization' => 'Basic '.base64_encode('user:wrong'),
        ]);

        $response->assertStatus(401);
    }

    public function test_ignores_if_user_or_pass_are_empty(): void
    {
        config(['folder-protection.enabled' => true]);
        config(['folder-protection.user' => '']);
        config(['folder-protection.password' => '']);

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_disabled_does_not_require_authentication(): void
    {
        config(['folder-protection.enabled' => false]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
