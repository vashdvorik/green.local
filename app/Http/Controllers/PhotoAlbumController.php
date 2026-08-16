<?php

namespace App\Http\Controllers;

use App\Models\PhotoAlbum;
use Illuminate\View\View;

class PhotoAlbumController extends Controller
{
    public function index(): View
    {
        return view('pages.media-photos', [
            'albums' => PhotoAlbum::query()
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->with('photos:id,photo_album_id,path')
                ->withCount('photos')
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function show(PhotoAlbum $album): View
    {
        abort_unless($album->isPublished(), 404);

        return view('pages.media-photo-album', [
            'album' => $album->load('photos'),
        ]);
    }
}
