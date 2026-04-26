<?php

namespace Tests\Feature\Api;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_published_posts(): void
    {
        Post::factory()->create(['status' => 'published', 'published_at' => now()]);
        Post::factory()->create(['status' => 'draft']);

        $response = $this->getJson('/api/posts');

        $response->assertOk()->assertJsonCount(1);
    }

    public function test_can_search_posts_by_keyword(): void
    {
        Post::factory()->create(['title' => 'Laravel入門', 'status' => 'published', 'published_at' => now()]);
        Post::factory()->create(['title' => 'Next.js入門', 'status' => 'published', 'published_at' => now()]);

        $response = $this->getJson('/api/posts?keyword=Laravel');

        $response->assertOk()->assertJsonCount(1);
    }

    public function test_can_filter_posts_by_tag(): void
    {
        $tag = Tag::factory()->create(['slug' => 'laravel']);
        $post = Post::factory()->create(['status' => 'published', 'published_at' => now()]);
        $post->tags()->attach($tag);
        Post::factory()->create(['status' => 'published', 'published_at' => now()]);

        $response = $this->getJson('/api/posts?tag=laravel');

        $response->assertOk()->assertJsonCount(1);
    }

    public function test_can_get_published_post_by_slug(): void
    {
        $post = Post::factory()->create(['status' => 'published', 'published_at' => now()]);

        $response = $this->getJson("/api/posts/{$post->slug}");

        $response->assertOk()->assertJsonFragment(['slug' => $post->slug]);
    }

    public function test_cannot_get_draft_post_by_slug(): void
    {
        $post = Post::factory()->create(['status' => 'draft']);

        $response = $this->getJson("/api/posts/{$post->slug}");

        $response->assertNotFound();
    }
}
