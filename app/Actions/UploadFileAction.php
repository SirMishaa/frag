<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\DuplicateFileException;
use App\Models\FragFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class UploadFileAction
{
    /**
     * Upload a file for the given user.
     *
     * @throws DuplicateFileException
     * @throws RuntimeException
     * @throws Throwable
     */
    public function execute(UploadedFile $file, User $user): FragFile
    {
        $filename = $file->getClientOriginalName();
        $checksum = $this->computeChecksum($file);

        $this->ensureUnique($checksum, $filename, $user);

        return DB::transaction(function () use ($file, $user, $filename, $checksum, &$fragFile): FragFile {
            $filePath = $this->storeFile($file, $user);

            try {
                $fragFile = $user->fragFiles()->create([
                    'filename' => $filename,
                    'path' => $filePath,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'checksum' => $checksum,
                ]);

                Log::info(sprintf('File uploaded successfully for user %s', $user->email), [
                    'filename' => $filename,
                    'path' => $filePath,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'checksum' => $checksum,
                ]);

                return $fragFile;
            } catch (RuntimeException $e) {
                Log::critical('Failed to create FragFile record', [
                    'user' => $user->email,
                    'error' => $e->getMessage(),
                ]);

                // Rollback file storage
                Storage::disk('public')->delete($filePath);

                throw $e;
            }

        });

    }

    /**
     * Compute SHA-256 checksum of the uploaded file.
     *
     * @throws RuntimeException
     */
    private function computeChecksum(UploadedFile $file): string
    {
        $checksum = hash_file('sha256', $file->getRealPath());

        if (! $checksum) {
            throw new RuntimeException('Failed to compute file checksum');
        }

        return $checksum;
    }

    /**
     * Ensure the file is unique for this user.
     *
     * @throws DuplicateFileException
     */
    private function ensureUnique(string $checksum, string $filename, User $user): void
    {
        if ($user->fragFiles()->where('checksum', $checksum)->exists()) {
            throw new DuplicateFileException($checksum, $filename);
        }
    }

    /**
     * Store the file in the user's directory.
     *
     * @throws RuntimeException
     */
    private function storeFile(UploadedFile $file, User $user): string
    {
        $folderName = sprintf('user_%s', $user->id);
        $filename = $file->getClientOriginalName();

        $filePath = Storage::disk('public')->putFileAs($folderName, $file, $filename);

        if (! $filePath) {
            Log::warning(sprintf('File upload failed for user %s', $user->email), [
                'filename' => $filename,
                'user' => $user->email,
            ]);

            throw new RuntimeException('Failed to store file');
        }

        return $filePath;
    }
}
