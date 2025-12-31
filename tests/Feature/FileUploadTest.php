<?php

declare(strict_types=1);

use App\Models\FragLink;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
});

it('can upload a file successfully', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('test.png', 100, 'image/png');

    $fileHash = hash_file('sha256', $file->getRealPath());

    $response = test()
        ->actingAs($user)
        ->withoutMiddleware()
        ->post('/share/file', [
            'file' => $file,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($user->fragFiles()->count())->toBe(1)
        ->and($user->fragFiles()->first()->checksum)->toBe($fileHash);
});

it('rejects duplicate file uploads based on checksum', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('test.png', 100, 'image/png');

    // The first upload should succeed
    $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->post('/share/file', [
            'file' => $file,
        ]);

    expect($user->fragFiles()->count())->toBe(1);

    // Second upload of the same file should fail
    $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->post('/share/file', [
            'file' => $file,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['file']);
    expect($user->fragFiles()->count())->toBe(1);
});

it('rejects files with not allowed mime types', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('tool.exe', 100, 'application/executable');

    $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->post('/share/file', [
            'file' => $file,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['file']);

    $errors = session('errors')->getBag('default')->get('file');
    expect(count($errors))->toBeGreaterThanOrEqual(1);
    // Error message changed to extensions-based validation
    expect($errors[0])->toContain('file field must');

});

it('allows different users to upload the same file', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $file = UploadedFile::fake()->create('test.png', 100, 'image/png');

    // User 1 uploads
    $this
        ->actingAs($user1)
        ->withoutMiddleware()
        ->post('/share/file', [
            'file' => $file,
        ]);

    expect($user1->fragFiles()->count())->toBe(1);

    // User 2 should be able to upload the same file
    $response = $this
        ->actingAs($user2)
        ->withoutMiddleware()
        ->post('/share/file', [
            'file' => $file,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    expect($user2->fragFiles()->count())->toBe(1);
});

it('creates a shareable link with expiration when expires_at is provided', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('test.png', 100, 'image/png');
    $expiresAt = now()->addDays(7)->format('Y-m-d\TH:i');

    $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->post('/share/file', [
            'file' => $file,
            'expires_at' => $expiresAt,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($user->fragFiles()->count())->toBe(1);

    $fragFile = $user->fragFiles()->first();
    $fragLink = $fragFile->links()->first();
    $expectedExpiresAt = Carbon::parse($expiresAt);

    expect($fragLink)->not->toBeNull()
        ->and($fragLink->frag_file_id)->toBe($fragFile->id)
        ->and($fragLink->user_id)->toBe($user->id)
        ->and($fragLink->state)->toBe('active')
        ->and($fragLink->slug)->not->toBeEmpty()
        ->and($fragLink->expires_at->format('Y-m-d H:i'))->toBe($expectedExpiresAt->format('Y-m-d H:i'));
});

it('does not create a shareable link when expires_at is not provided', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('test.png', 100, 'image/png');

    $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->post('/share/file', [
            'file' => $file,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($user->fragFiles()->count())->toBe(1);

    $fragFile = $user->fragFiles()->first();

    expect($fragFile->links()->count())->toBe(0);
    expect(FragLink::where('frag_file_id', $fragFile->id)->count())->toBe(0);
});

it('can upload obj files', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('model.obj', 100);

    $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->post('/share/file', [
            'file' => $file,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($user->fragFiles()->count())->toBe(1)
        ->and($user->fragFiles()->first()->filename)->toBe('model.obj');
});

it('can upload obj files detected as application/octet-stream', function () {
    $user = User::factory()->create();
    // Some systems detect .obj files as application/octet-stream
    $file = UploadedFile::fake()->create('model.obj', 100, 'application/octet-stream');

    $response = $this
        ->actingAs($user)
        ->withoutMiddleware()
        ->post('/share/file', [
            'file' => $file,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect($user->fragFiles()->count())->toBe(1)
        ->and($user->fragFiles()->first()->filename)->toBe('model.obj')
        ->and($user->fragFiles()->first()->mime_type->value)->toBe('application/prs.wavefront-obj');
});
