<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Contract\Storage;

class FirebaseStorageService
{
    public function __construct(private Storage $storage) {}

    /**
     * ENV you can add (examples):
     * UPLOAD_MAX_MB=10
     * FIREBASE_STORAGE_PUBLIC=false
     * FIREBASE_STORAGE_CACHE_CONTROL="public,max-age=31536000,immutable"
     * FIREBASE_STORAGE_RESUMABLE=true
     */

    /**
     * Upload image to Firebase Storage with size enforcement.
     *
     * @return array{
     *   path:string,
     *   bucket:string,
     *   public_url:string,
     *   download_url:string,
     *   token:string|null,
     *   content_type:string
     * }
     */
    public function upload(UploadedFile $file, ?string $folder = 'image'): array
    {
      // ---- 1) Enforce size limit (defense-in-depth; you already validate in FormRequest)
      $maxBytes = (int) env('UPLOAD_MAX_MB', 10) * 1024 * 1024; // MB -> bytes
      $size = (int) $file->getSize();
      if ($size > $maxBytes) {
          throw ValidationException::withMessages([
              'file' => ['The file may not be greater than ' . env('UPLOAD_MAX_MB', 10) . ' MB.'],
          ]);
      }

      // ---- 2) Build storage path, content-type, and access mode
      $bucket = $this->storage->getBucket();
      $bucketName = $bucket->name();

      $safeFolder = trim($folder ?: 'image', '/');

      // Keep original extension if present; fallback to mime-derived subtype
      $extension = strtolower($file->getClientOriginalExtension() ?: ($this->extensionFromMime($file->getMimeType()) ?: 'bin'));
      $contentType = $file->getMimeType() ?: 'application/octet-stream';

      $path = sprintf('%s/%s.%s', $safeFolder, (string) Str::uuid(), $extension);

      $isPublic = filter_var(env('FIREBASE_STORAGE_PUBLIC', false), FILTER_VALIDATE_BOOLEAN);
      $cacheControl = env('FIREBASE_STORAGE_CACHE_CONTROL', 'public, max-age=31536000, immutable');
      $useResumable = filter_var(env('FIREBASE_STORAGE_RESUMABLE', true), FILTER_VALIDATE_BOOLEAN);

      // Private objects need a download token to build firebase-style download URLs
      $token = $isPublic ? null : (string) Str::uuid();

      // ---- 3) Open stream from PHP temp file
      $stream = fopen($file->getPathname(), 'rb');
      if ($stream === false) {
          throw new \RuntimeException('Unable to open uploaded file for reading.');
      }

      try {
          $options = [
              'name' => $path,
              'metadata' => [
                  'contentType'  => $contentType,
                  'cacheControl' => $cacheControl,
              ],
          ];

          if (!$isPublic) {
              // Needed for tokenized download URL when bucket/object is private
              $options['metadata']['firebaseStorageDownloadTokens'] = $token;
          } else {
              // If you want objects to be publicly readable
              $options['predefinedAcl'] = 'publicRead';
          }

          if ($useResumable) {
              // Google Cloud Storage PHP client supports resumable uploads
              $options['resumable'] = true;
          }

          $bucket->upload($stream, $options);
      } finally {
          if (is_resource($stream)) {
              fclose($stream);
          }
      }

      // ---- 4) Build URLs
      $publicUrl   = "https://storage.googleapis.com/{$bucketName}/{$path}";
      $downloadUrl = $isPublic
          ? $publicUrl
          : "https://firebasestorage.googleapis.com/v0/b/{$bucketName}/o/" . rawurlencode($path) . "?alt=media&token={$token}";

      return [
          'path'         => $path,
          'bucket'       => $bucketName,
          'public_url'   => $publicUrl,
          'download_url' => $downloadUrl,
          'token'        => $token,        // null if public
          'content_type' => $contentType,
      ];
    }

    public function delete(string $path): void
    {
        $this->storage->getBucket()->object($path)->delete();
    }

    /**
     * Minimal helper to guess extension from MIME if original extension is missing.
     */
    private function extensionFromMime(?string $mime): ?string
    {
        if (!$mime) return null;
        static $map = [
            'image/jpeg' => 'jpg',
            'image/jpg'  => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
        ];
        return $map[strtolower($mime)] ?? null;
    }
}
