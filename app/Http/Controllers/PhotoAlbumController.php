<?php

namespace App\Http\Controllers;

use App\Models\PhotoAlbum;
use Illuminate\View\View;

class PhotoAlbumController extends Controller
{
    public function index(): View
    {
        $albums = PhotoAlbum::query()
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->with('photos:id,photo_album_id,path')
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->paginate(6)
                ->withQueryString();

        $nextUrl = $albums->nextPageUrl();
        if ($nextUrl && ! request()->boolean('fragment')) {
            $nextUrl .= str_contains($nextUrl, '?') ? '&fragment=1' : '?fragment=1';
        }

        $viewData = compact('albums', 'nextUrl');

        return request()->boolean('fragment')
            ? view('partials.photo-albums', $viewData)
            : view('pages.media-photos', $viewData);
    }

    public function show(PhotoAlbum $album): View
    {
        abort_unless($album->isPublished(), 404);

        return view('pages.media-photo-album', [
            'album' => $album->load('photos'),
        ]);
    }
}
