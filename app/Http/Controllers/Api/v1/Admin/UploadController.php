<?php

namespace App\Http\Controllers\Api\v1\Admin;

use App\Services\FirebaseStorageService;
use App\Http\Controllers\Api\v1\BaseAPI;
use App\Http\Requests\UploadFileRequest;
use App\Http\Requests\DeleteFileRequest;
use Illuminate\Http\Request;

class UploadController extends BaseAPI
{
    public function store(UploadFileRequest $request, FirebaseStorageService $storage)
    {
        $validated = $request->validated();

        $result = $storage->upload($validated['file'], $validated['folder']);

        return $this->successResponse('Image uploaded successfully', $result);
    }

    // OPTIONAL: delete by storage path returned from upload response
    public function destroy(DeleteFileRequest $request, FirebaseStorageService $storage)
    {
        $validated = $request->validated();

        $storage->delete($validated['path']);

        return $this->successResponse('File deleted');
    }
}
