<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ForumThread;
use App\Models\ForumPost;

class ForumController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $threads = ForumThread::with('posts.user')->latest()->get();

        return view('forum.index', compact('threads'));
    }

    public function storePost(Request $request, ForumThread $thread)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $post = new ForumPost();
        $post->content = $request->input('content');
        $post->user_id = auth()->id();

        $thread->posts()->save($post);

        // Devolver JSON para AJAX
        return response()->json([
            'message' => 'Missatge enviat correctament',
            'post' => [
                'id' => $post->id,
                'content' => e($post->content),
                'user' => [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                ],
                'created_at_human' => $post->created_at->diffForHumans(),
            ],
        ]);
    }


    public function getPosts(ForumThread $thread)
    {
        $posts = $thread->posts()->with('user')->latest()->get()->reverse()->values();

        // Mapear para devolver formato con tiempo humano
        $data = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'content' => e($post->content),
                'user' => [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                ],
                'created_at_human' => $post->created_at->diffForHumans(),
            ];
        });

        return response()->json($data);
    }
}
