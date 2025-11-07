<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\CreateFileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function create(CreateFileRequest $fileRequest): Response|RedirectResponse
    {
        $user = $fileRequest->user();

        $folderName = sprintf('user_%s', $user?->id);
        $filename = $fileRequest->file('file')->getClientOriginalName();
        $file = $fileRequest->file('file');

        $filePath = Storage::disk('public')->putFileAs($folderName, $file, $filename);

        if (! $filePath) {
            Log::warning(sprintf('File upload failed for user ID %s', $user?->id), [
                'filename' => $filename,
            ]);

            return redirect()->back()->with('error', 'File upload failed');
        }

        $user?->fragFiles()->create([
            'filename' => $filename,
            'path' => $filePath,
            /** Guaranteed to have an acceptable mime type for database by request validation */
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        Log::info(sprintf('File uploaded successfully for user %s', $user?->email), [
            'filename' => $filename,
            'path' => $filePath,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return redirect()->back()->with('success', 'File uploaded successfully: '.$filePath);

    }
}
