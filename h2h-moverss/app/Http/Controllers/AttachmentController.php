<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\{JsonResponse, Request};
use Symfony\Component\HttpFoundation\StreamedResponse;
use Auth, Storage;

/**
 * Attached files to the order.
 */
class AttachmentController extends Controller
{

    /**
     * Get a list of order attachments.
     * @param  Attachment  $attachment
     * @param  Request  $request
     * @return JsonResponse
     */
    public function records(Attachment $attachment, Request $request): JsonResponse
    {

        $validated = $request->validate([
            'type' => 'required|string|max:30',
            'id' => 'required|integer',
        ]);

        $records = $attachment
            ->where('miscs->object->type', $validated['type'])
            ->where('miscs->object->id', $validated['id'])
            ->get();

        return response()
            ->json([
                'success' => true,
                'records' => $records,
            ]);
    }

    /**
     * Send the file for stream download.
     * @param  string  $hash
     * @param  Attachment  $attachment
     * @return StreamedResponse
     */
    public function dl(string $hash, Attachment $attachment): StreamedResponse
    {
        $attach = $attachment->whereHash($hash)->firstOrFail();

        return response()
            ->streamDownload(function () use ($attach) {
                echo Storage::get($attach->miscs['file']['patch'].$attach->hash);
            }, $attach->miscs['file']['name']);
    }

    /**
     * Adding an attachment.
     * @param  Request  $request
     * @param  Attachment  $attachment
     * @return JsonResponse
     */
    public function create(Request $request, Attachment $attachment): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|max:30',
            'id' => 'required|integer',
            'description' => 'nullable|string|max:255',
            'files' => 'required|array',
            'files.*' => 'required|file|max:'.(16 * 1024),
        ], [
            'files.0.max' => 'Maximum file size to upload is 16MB',
        ]);

        foreach ($validated['files'] as $file) {
            $hash = hash_file('sha256', $file->path());

            $folder = floor($validated['id'] / 1000);
            $folder = "attachments/{$validated['type']}/$folder/";

            $miscs = [
                'patch' => $folder,
                'size' => $this->getHumanReadableFilesize($file->getSize()),
                'name' => strip_tags($file->getClientOriginalName()),
            ];
            Storage::putFileAs($folder, $file, $hash);


            $p = clone $attachment;
            $p->user_id = Auth::id();
            $p->hash = $hash;
            if (isset($validated['description'])) {
                $p->description = strip_tags($validated['description'], '<br/>');
            }
            $p->miscs = [
                'object' => [
                    'type' => $validated['type'],
                    'id' => (int) $validated['id'],
                ],
                'file' => $miscs,
            ];
            $p->save();
        }


        $records = $attachment
            ->where('miscs->object->type', $validated['type'])
            ->where('miscs->object->id', $validated['id'])
            ->get();

        return response()
            ->json([
                'success' => true,
                'records' => $records,
            ]);
    }

    /**
     * Removing an attachment.
     * @param  Request  $request
     * @param  Attachment  $attachment
     * @return JsonResponse
     * @throws \Exception
     */
    public function remove(Request $request, Attachment $attachment): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:attachments,id',
            'hash' => 'required|string|max:64',
        ]);

        $attach = $attachment->whereHash($validated['hash'])->whereId($validated['id'])->firstOrFail();
        $attach->delete();

        return response()
            ->json([
                'success' => true,
            ]);
    }

    /**
     * Get user-friendly file size.
     * @param  int  $bytes
     * @param  int  $dec  Символов после точки
     * @return string
     */
    public function getHumanReadableFilesize(int $bytes, int $dec = 2): string
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$dec}f %s", ($bytes / (1024 ** $factor)), $size[$factor]);
    }
}
