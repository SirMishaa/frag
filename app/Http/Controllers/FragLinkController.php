<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FragLinkRequest;
use App\Models\FragLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class FragLinkController extends Controller
{
    public function show(string $slug): RedirectResponse
    {
        $fragLink = FragLink::where('slug', $slug)->firstOrFail();

        if ($fragLink->state !== 'active') {
            abort(Response::HTTP_FORBIDDEN, 'This link has been revoked.');
        }

        if ($fragLink->expires_at && $fragLink->expires_at->isPast()) {
            abort(Response::HTTP_GONE, 'This link has expired.');
        }

        return redirect('storage/'.$fragLink->fragFile->path);
    }

    public function store(FragLinkRequest $request): RedirectResponse
    {
        $fragLink = FragLink::firstOrCreate(
            [
                'frag_file_id' => $request->validated('frag_file_id'),
                'user_id' => $request->user()?->id,
            ],
            [
                'slug' => $this->generateUniqueSlug(),
                'state' => 'active',
            ]
        );

        if ($fragLink->wasRecentlyCreated) {
            Log::info('Frag link created', [
                'frag_link_id' => $fragLink->id,
                'user_id' => $request->user()?->id,
                'slug' => $fragLink->slug,
            ]);
        }

        return back()->with('generatedLink', [
            'slug' => $fragLink->slug,
            'url' => url("/l/{$fragLink->slug}"),
        ]);
    }

    private function generateUniqueSlug(int $length = 8): string
    {
        do {
            $slug = Str::random($length);
        } while (FragLink::where('slug', $slug)->exists());

        return $slug;
    }
}
