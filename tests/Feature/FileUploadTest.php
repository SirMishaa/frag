<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
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
    expect(count($errors))->toBe(1)
        ->and($errors[0])->toStartWith('The file field must be a file of type:');

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
