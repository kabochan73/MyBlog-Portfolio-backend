<?php

namespace Tests\Feature\Api;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTagTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_admin_can_create_tag(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/admin/tags', [
            'name' => 'Laravel',
        ]);

        $response->assertCreated()->assertJsonFragment(['name' => 'Laravel', 'slug' => 'laravel']);
        $this->assertDatabaseHas('tags', ['name' => 'Laravel']);
    }

    public function test_cannot_create_duplicate_tag(): void
    {
        Tag::factory()->create(['name' => 'Laravel']);

        $response = $this->actingAs($this->admin)->postJson('/api/admin/tags', [
            'name' => 'Laravel',
        ]);

        $response->assertUnprocessable();
    }

    public function test_admin_can_delete_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/tags/{$tag->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_guest_cannot_create_tag(): void
    {
        $response = $this->postJson('/api/admin/tags', ['name' => 'Laravel']);

        $response->assertUnauthorized();
    }
}
