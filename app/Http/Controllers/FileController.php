<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UploadFileAction;
use App\CreateFileRequest;
use App\Exceptions\DuplicateFileException;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class FileController extends Controller
{
    public function create(CreateFileRequest $fileRequest, UploadFileAction $uploadAction): Response|RedirectResponse
    {
        try {
            /** @var User $user */
            $user = $fileRequest->user();

            $fragFile = $uploadAction->execute(
                $fileRequest->file('file'),
                $user,
                $fileRequest->input('expires_at')
            );

            return redirect()->back()->with('success', "File uploaded successfully: {$fragFile->path}");
        } catch (DuplicateFileException $e) {
            Log::warning('Duplicate file upload attempt', [
                'user' => $fileRequest->user()?->email,
                'filename' => $e->filename,
                'checksum' => $e->checksum,
            ]);

            return redirect()->back()->withErrors([
                'file' => 'File with this signature already has been uploaded',
            ]);
        } catch (Throwable $e) {
            Log::error('File upload failed', [
                'user' => $fileRequest->user()?->email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'File upload failed');
        }
    }
}
