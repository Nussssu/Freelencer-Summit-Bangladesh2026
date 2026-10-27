<?php

namespace App\Http\Controllers;

use App\Content\PageContent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /** Renders the shared "Coming next" page for any routed-but-unbuilt URL. */
    public function __invoke(Request $request): Response
    {
        $pattern = '/'.$request->route()->uri();

        return Inertia::render('ComingNext', [
            'page' => [...PageContent::for($pattern), 'slug' => $request->route('slug'), 'path' => '/'.$request->path()],
        ]);
    }
}
