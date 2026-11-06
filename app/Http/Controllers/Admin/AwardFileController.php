<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AwardApplicationFile;
use App\Support\Access;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * An award applicant's supporting document, from the private disk, for admins who may view nominations. Files are
 * never public: there is no URL to them outside this route. PDFs and images open inline; the name is the applicant's.
 */
class AwardFileController extends Controller
{
    public function __invoke(AwardApplicationFile $file): StreamedResponse
    {
        abort_unless(auth()->user()?->can(Access::permission('view', 'nominations')), 403);
        abort_unless(Storage::disk('private')->exists($file->path), 404);

        return Storage::disk('private')->response($file->path, $file->original_name, [
            'Content-Type' => $file->mime,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox",
        ]);
    }
}
