<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        return response()->json(Post::with('tags')->latest()->get());
    }

    public function show(Post $post)
    {
        return response()->json($post->load('tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:30',
            'body' => 'required|string',
            'status' => 'required|in:published,draft',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $data['slug'] = Str::slug($data['title']) . '-' . Str::random(6);
        $data['published_at'] = $data['status'] === 'published' ? now() : null;

        $post = Post::create($data);
        $post->tags()->sync($data['tag_ids'] ?? []);

        return response()->json($post->load('tags'), 201);
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => 'required|string|max:30',
            'body' => 'required|string',
            'status' => 'required|in:published,draft',
            'tag_ids' => 'array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        if ($data['status'] === 'published' && $post->published_at === null) {
            $data['published_at'] = now();
        } elseif ($data['status'] === 'draft') {
            $data['published_at'] = null;
        }

        $post->update($data);
        $post->tags()->sync($data['tag_ids'] ?? []);

        return response()->json($post->load('tags'));
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json(null, 204);
    }
}
