<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Endpoint dedicado para upload da foto do currículo.
 *
 * Por que não usar WithFileUploads do Livewire?
 *  O trait tende a criar conflitos com snapshot JSON de componentes grandes.
 *  Solução: HTTP puro via fetch, retorna caminho relativo em storage/app.
 */
class ResumePhotoUploadController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        if (! $request->user()) {
            return response()->json(['ok' => false, 'msg' => 'unauthenticated'], 401);
        }

        $request->validate([
            'photo' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $file = $request->file('photo');
        $name = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = 'tmp/resume-photos/' . $name;

        // Storage padrão do laravel: storage/app/{path}
        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        return response()->json([
            'ok'   => true,
            'path' => $path,
            'name' => $file->getClientOriginalName(),
        ]);
    }
}
