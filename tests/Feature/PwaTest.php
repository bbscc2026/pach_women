<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ContentPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_worker_is_served_with_a_version_and_offline_page_precached(): void
    {
        $response = $this->get('/sw.js')->assertOk();

        $this->assertStringContainsString('application/javascript', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control'));
        $this->assertMatchesRegularExpression('/const VERSION = "[a-f0-9]{12}";/', $response->getContent());
        $this->assertStringContainsString('"\/offline"', $response->getContent());
        $this->assertNull($response->headers->get('Set-Cookie'), 'No session cookie for the service worker.');
    }

    public function test_offline_page_works_without_the_database_layout(): void
    {
        $this->get('/offline')->assertOk()->assertSee("You're offline", false)->assertSee('Try again');
    }

    public function test_manifests_are_valid_json_with_icons(): void
    {
        foreach (['site.webmanifest', 'admin.webmanifest'] as $file) {
            $manifest = json_decode(file_get_contents(public_path($file)), true);

            $this->assertIsArray($manifest, "$file is valid JSON");
            $this->assertSame('standalone', $manifest['display']);

            foreach ($manifest['icons'] as $icon) {
                $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
            }
        }
    }

    public function test_shop_pages_link_the_app_manifest_and_script(): void
    {
        $this->get('/')->assertOk()
            ->assertSee('/site.webmanifest', false)
            ->assertSee('/pwa.js', false)
            ->assertSee('data-pwa-install', false);
    }

    public function test_admin_has_app_manifest_tab_bar_and_install_banner(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin/orders')->assertOk()
            ->assertSee('/admin.webmanifest', false)
            ->assertSee('pwa-install-banner', false)
            ->assertSee('pw-tabbar', false)
            ->assertSee('New product', false);
    }

    public function test_me_tab_works_for_guests_and_customers(): void
    {
        $this->seed(ContentPageSeeder::class);

        $this->get('/me')->assertOk()->assertSee('Log in')->assertSee('Shipping policy')->assertSee('WhatsApp');

        $user = User::factory()->create(['name' => 'Asha Menon']);
        $this->actingAs($user)->get('/me')->assertOk()->assertSee('Asha Menon')->assertSee('My orders')->assertSee('Log out');
    }

    public function test_inner_pages_get_back_button_and_title_on_phones(): void
    {
        $this->get('/me')->assertSee('aria-label="Back"', false);
        $this->get('/')->assertDontSee('aria-label="Back"', false)->assertSee('aria-label="Open menu"', false);
    }

    public function test_logging_out_clears_offline_copies(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')
            ->assertRedirect()
            ->assertHeader('Clear-Site-Data', '"cache", "storage"');
    }
}
