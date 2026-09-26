<?php

namespace Tests\Feature;

use App\Models\DiscountCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateDiscountCodesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_requested_number_of_unique_unused_codes(): void
    {
        $this->artisan('discount-codes:generate', ['count' => 30, '--label' => 'CORE Pilates'])
            ->assertSuccessful();

        $this->assertSame(30, DiscountCode::query()->count());
        $this->assertSame(30, DiscountCode::query()->distinct()->count('code'));
        $this->assertSame(0, DiscountCode::query()->whereNotNull('used_at')->count());
        $this->assertTrue(DiscountCode::query()->where('label', 'CORE Pilates')->where('percent_off', 30)->count() === 30);
    }

    public function test_every_generated_code_starts_with_the_given_prefix_and_has_no_ambiguous_characters(): void
    {
        $this->artisan('discount-codes:generate', ['count' => 10, '--prefix' => 'CORE'])
            ->assertSuccessful();

        $codes = DiscountCode::query()->pluck('code');

        $this->assertCount(10, $codes);
        foreach ($codes as $code) {
            $this->assertStringStartsWith('CORE-', $code);
            $this->assertMatchesRegularExpression('/^CORE-[ABCDEFGHJKMNPQRSTUVWXYZ23456789]{6}$/', $code);
        }
    }

    public function test_running_it_twice_never_produces_a_duplicate_code(): void
    {
        $this->artisan('discount-codes:generate', ['count' => 20])->assertSuccessful();
        $this->artisan('discount-codes:generate', ['count' => 20])->assertSuccessful();

        $this->assertSame(40, DiscountCode::query()->count());
        $this->assertSame(40, DiscountCode::query()->distinct()->count('code'));
    }
}
