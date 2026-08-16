<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        return view('pages.news', [
            'news' => News::query()
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function show(News $news): View
    {
        abort_unless($news->isPublished(), 404);

        return view('pages.news-show', compact('news'));
    }
}
