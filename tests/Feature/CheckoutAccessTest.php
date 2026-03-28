<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_checkout(): void
    {
        $this->get(route('checkout.create'))
            ->assertRedirect(route('login'));
    }

    public function test_unverified_user_is_redirected_from_checkout(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('checkout.create'))
            ->assertRedirect(route('verification.notice'));
    }
}
