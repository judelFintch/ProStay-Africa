<?php

namespace Tests\Feature\Dishes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DishesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_dishes_page_can_be_rendered(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('dishes.index'))
            ->assertOk()
            ->assertSee('Plats et recettes');
    }
}
