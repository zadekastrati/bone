<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetaPixelTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_pixel_snippet_is_absent_when_no_id_is_configured(): void
    {
        config(['services.meta_pixel.id' => null]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('connect.facebook.net', false)
            ->assertDontSee('fbq(', false);
    }

    public function test_the_pixel_snippet_renders_with_the_configured_id(): void
    {
        config(['services.meta_pixel.id' => '1582989499361671']);

        $this->get('/')
            ->assertOk()
            ->assertSee('connect.facebook.net', false)
            ->assertSee("fbq('init', '1582989499361671')", false)
            ->assertSee('facebook.com/tr?id=1582989499361671', false);
    }
}
