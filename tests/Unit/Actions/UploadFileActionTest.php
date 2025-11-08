<?php

declare(strict_types=1);

use App\Actions\UploadFileAction;
use App\Exceptions\DuplicateFileException;
use App\Models\FragFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseHas;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->action = new UploadFileAction;
});

it('successfully uploads a file and creates a FragFile record', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('test-image.png', 100, 'image/png');

    $result = $this->action->execute($file, $user);

    expect($result)->toBeInstanceOf(FragFile::class)
        ->and($result->filename)->toBe('test-image.png')
        ->and($result->mime_type->value)->toBe('image/png')
        ->and($result->size)->toBe(100 * 1024)
        ->and($result->checksum)->not->toBeEmpty()
        ->and($result->user_id)->toBe($user->id);

    Storage::disk('public')->assertExists($result->path);

    assertDatabaseHas('frag_files', [
        'id' => $result->id,
        'filename' => 'test-image.png',
        'user_id' => $user->id,
    ]);
});

it('stores the file in the correct user directory', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('image.png', 50, 'image/png');

    $result = $this->action->execute($file, $user);

    expect($result->path)->toBe(sprintf('user_%s/image.png', $user->id));
    Storage::disk('public')->assertExists(sprintf('user_%s/image.png', $user->id));
});

it('computes and stores the correct SHA-256 checksum', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('test.jpg', 10, 'image/jpeg');

    $expectedChecksum = hash_file('sha256', $file->getRealPath());

    $result = $this->action->execute($file, $user);

    expect($result->checksum)->toBe($expectedChecksum);
});

it('throws DuplicateFileException when uploading a file with existing checksum', function () {
    $user = User::factory()->create();

    // Create a specific file content to ensure consistent checksum
    $content = 'test file content for duplicate detection';
    $file1 = UploadedFile::fake()->createWithContent('original.png', $content);

    $firstUpload = $this->action->execute($file1, $user);

    expect($user->fragFiles()->count())->toBe(1);

    // Same content, different filename
    $file2 = UploadedFile::fake()->createWithContent('duplicate.png', $content);

    expect(hash_file('sha256', $file1->getRealPath()))->toBe(hash_file('sha256', $file2->getRealPath()));

    $this->action->execute($file2, $user);
})->throws(DuplicateFileException::class, 'Duplicate file detected');

it('allows different users to upload files with the same checksum', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $file = UploadedFile::fake()->create('shared-file.png', 100, 'image/png');

    $result1 = $this->action->execute($file, $user1);

    // Create a new file with identical content for user2
    $file2 = UploadedFile::fake()->create('shared-file.png', 100, 'image/png');
    $result2 = $this->action->execute($file2, $user2);

    expect($user1->fragFiles()->count())->toBe(1)
        ->and($user2->fragFiles()->count())->toBe(1)
        ->and($result1->user_id)->toBe($user1->id)
        ->and($result2->user_id)->toBe($user2->id);
});

it('throws RuntimeException when file storage fails', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('test.png', 100, 'image/png');

    // Mock Storage to fail
    Storage::shouldReceive('disk')
        ->with('public')
        ->andReturnSelf();

    Storage::shouldReceive('putFileAs')
        ->andReturn(false);

    Log::shouldReceive('warning')
        ->once()
        ->with(
            sprintf('File upload failed for user %s', $user->email),
            \Mockery::type('array')
        );

    $this->action->execute($file, $user);
})->throws(RuntimeException::class, 'Failed to store file');

it('stores all file metadata correctly', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('video.mp4', 250, 'video/mp4');

    $result = $this->action->execute($file, $user);

    expect($result->filename)->toBe('video.mp4')
        ->and($result->mime_type->value)->toBe('video/mp4')
        ->and($result->size)->toBe(250 * 1024)
        ->and($result->path)->toBe(sprintf('user_%s/video.mp4', $user->id))
        ->and($result->checksum)->toHaveLength(64) // SHA-256 is 64 chars
        ->and($result->user_id)->toBe($user->id);

    assertDatabaseHas('frag_files', [
        'filename' => 'video.mp4',
        'mime_type' => 'video/mp4',
        'size' => 250 * 1024,
        'user_id' => $user->id,
    ]);
});

it('handles files with special characters in the filename and keeps the original filename', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('test image (1) - copy.png', 50, 'image/png');

    $result = $this->action->execute($file, $user);

    expect($result->filename)->toBe('test image (1) - copy.png')
        ->and($result->path)->toBe(sprintf('user_%s/test image (1) - copy.png', $user->id));

    Storage::disk('public')->assertExists($result->path);
});

it('handles multiple file types correctly in a database transaction', function ($filename, $mimeType) {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create($filename, 50, $mimeType);

    $transactionCalled = false;

    DB::shouldReceive('transaction')
        ->once()
        ->andReturnUsing(function ($callback) use (&$transactionCalled) {
            $transactionCalled = true;

            return $callback();
        });

    $result = $this->action->execute($file, $user);

    expect($result->filename)->toBe($filename)
        ->and($result->mime_type->value)->toBe($mimeType)
        ->and($transactionCalled)->toBeTrue();
})->with([
    ['image.png', 'image/png'],
    ['image.jpg', 'image/jpeg'],
    ['image.gif', 'image/gif'],
    ['video.mp4', 'video/mp4'],
    ['image.webp', 'image/webp'],
]);
