<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\DuplicateFileException;
use App\Models\FragFile;
use App\Models\FragLink;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
    public function execute(UploadedFile $file, User $user, ?string $expiresAt = null): FragFile
    {
        $filename = $file->getClientOriginalName();
        $checksum = $this->computeChecksum($file);

        $this->ensureUnique($checksum, $filename, $user);

        return DB::transaction(function () use ($file, $user, $filename, $checksum, $expiresAt, &$fragFile): FragFile {
            $filePath = $this->storeFile($file, $user);

            try {
                $fragFile = $user->fragFiles()->create([
                    'filename' => $filename,
                    'path' => $filePath,
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'checksum' => $checksum,
                ]);

                /**
                 * Create a shareable link if an expiration date is provided.
                 */
                if ($expiresAt !== null) {
                    $expiresAt = Carbon::parse($expiresAt);
                    $fragLink = FragLink::create([
                        'frag_file_id' => $fragFile->id,
                        'user_id' => $user->id,
                        'slug' => $this->generateUniqueSlug(),
                        'state' => 'active',
                        'expires_at' => $expiresAt,
                    ]);
                    Log::info('File upload successfully with creation of shareable url', [
                        'filename' => $filename,
                        'path' => $filePath,
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                        'checksum' => $checksum,
                        'frag_link_slug' => $fragLink?->slug,
                        'expires_at' => $expiresAt,
                    ]);
                } else {
                    Log::info('File uploaded successfully', [
                        'filename' => $filename,
                        'path' => $filePath,
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                        'checksum' => $checksum,
                    ]);
                }

                return $fragFile;
            } catch (RuntimeException $e) {
                Log::critical('Failed to create FragFile record', [
                    'user' => $user->email,
                    'error' => $e->getMessage(),
                ]);

                // Rollback file storage
                Storage::disk('local')->delete($filePath);

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

        $filePath = Storage::disk('local')->putFileAs($folderName, $file, $filename);

        if (! $filePath) {
            Log::warning(sprintf('File upload failed for user %s', $user->email), [
                'filename' => $filename,
                'user' => $user->email,
            ]);

            throw new RuntimeException('Failed to store file');
        }

        return $filePath;
    }

    /**
     * Generate a unique slug for the FragLink.
     */
    private function generateUniqueSlug(int $length = 8): string
    {
        do {
            $slug = Str::random($length);
        } while (FragLink::where('slug', $slug)->exists());

        return $slug;
    }
}
