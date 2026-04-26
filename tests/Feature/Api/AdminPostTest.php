<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPostTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_admin_can_get_single_post(): void
    {
        $post = Post::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($this->admin)->getJson("/api/admin/posts/{$post->id}");

        $response->assertOk()->assertJsonFragment(['id' => $post->id]);
    }

    public function test_admin_can_get_all_posts(): void
    {
        Post::factory(3)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/admin/posts');

        $response->assertOk()->assertJsonCount(3);
    }

    public function test_guest_cannot_get_admin_posts(): void
    {
        $response = $this->getJson('/api/admin/posts');

        $response->assertUnauthorized();
    }

    public function test_admin_can_create_post(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/admin/posts', [
            'title' => 'テスト記事',
            'body' => '本文',
            'status' => 'draft',
        ]);

        $response->assertCreated()->assertJsonFragment(['title' => 'テスト記事']);
        $this->assertDatabaseHas('posts', ['title' => 'テスト記事']);
    }

    public function test_admin_can_publish_post(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/admin/posts', [
            'title' => '公開記事',
            'body' => '本文',
            'status' => 'published',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('posts', ['title' => '公開記事', 'status' => 'published']);
        $this->assertNotNull($response->json('published_at'));
    }

    public function test_admin_can_update_post(): void
    {
        $post = Post::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($this->admin)->putJson("/api/admin/posts/{$post->id}", [
            'title' => '更新後タイトル',
            'body' => '更新後本文',
            'status' => 'published',
        ]);

        $response->assertOk()->assertJsonFragment(['title' => '更新後タイトル']);
    }

    public function test_admin_can_delete_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/posts/{$post->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_admin_can_attach_tags_to_post(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/admin/posts', [
            'title' => 'タグ付き記事',
            'body' => '本文',
            'status' => 'draft',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('post_tag', ['tag_id' => $tag->id]);
    }
}
