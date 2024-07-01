<?php

class PostController
{
    public function index()
    {
        $posts = Post::all();

        return view('posts.index')->with('posts', $posts)->with('text', __('translated text'));
    }

    public function create()
    {
        return view('posts.create')->with('text', trans('translated_text'));
    }

    public function store(Request $request)
    {
        $messages = [
            'title.required' => __('The title field is required for create'),
            'title.max' => __('The title may not be greater than :max characters.', ['max' => 50]),
            'content.required' => trans('validation.required', ['attribute' => 'content']),
        ];

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ], $messages);

        $post = new Post($validatedData);
        $post->slug = str_slug($request->title);
        $post->save();

        Session::flash('success', lang('messages.post_created_successfully'));

        return redirect()->route('posts.index');
    }

    public function update(Request $request, Post $post)
    {
        $messages = [
            'title.required' => __('The title field is required for update'),
            'title.max' => __('The title may not be greater than :max characters.', ['max' => 50]),
            'content.required' => trans('validation.required', ['attribute' => 'content']),
        ];

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ], $messages);

        $post->update($validatedData);

        Session::flash('info', __('messages.post_updated_successfully'));

        return redirect()->route('posts.index');
    }
}
