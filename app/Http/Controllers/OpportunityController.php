<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    public function index(): View
    {
        return view('pages.stories', [
            'opportunities' => Opportunity::query()
                ->with('tag')
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function show(Opportunity $opportunity): View
    {
        abort_unless($opportunity->isPublished(), 404);

        $opportunity->load('tag');

        return view('pages.story-show', compact('opportunity'));
    }
}
