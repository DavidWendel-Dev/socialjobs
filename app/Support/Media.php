<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Helper único para lidar com uploads e URLs públicas de mídia
 * (avatar, capa, imagens de posts…). Permite trocar disk `public` ↔ `r2`
 * sem tocar nos models nem controllers.
 *
 * Uso:
 *   Media::disk()->exists($path)
 *   Media::disk()->delete($path)
 *   Media::store($uploadedFile, 'avatars')  // retorna path relativo
 *   Media::url($path)                       // retorna URL pública absoluta
 */
class Media
{
    /** Nome do disco configurado (config/media.php → MEDIA_DISK). */
    public static function diskName(): string
    {
        return (string) config('media.disk', 'public');
    }

    /** Instância do Filesystem configurada. */
    public static function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk(self::diskName());
    }

    /**
     * Armazena um arquivo enviado (UploadedFile ou TemporaryUploadedFile)
     * em uma pasta lógica. Retorna o path relativo dentro do disco.
     */
    public static function store($file, string $directory): string
    {
        return $file->store($directory, self::diskName());
    }

    /**
     * URL pública do arquivo.
     * Sempre serve pelo domínio configurado em MEDIA_PUBLIC_URL (CDN).
     * Todos os arquivos (avatars/covers/posts) vivem no R2 após a
     * migração via `artisan media:migrate-to-r2`.
     */
    public static function url(?string $path): ?string
    {
        if (! $path) return null;

        // URL absoluta já — devolve como está (útil para avatares externos)
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $path = ltrim($path, '/');

        $base = (string) config('media.public_url', '');
        if ($base !== '') {
            return rtrim($base, '/') . '/' . $path;
        }

        return self::disk()->url($path);
    }

    /** Verifica se um path existe no disco de mídia. */
    public static function exists(?string $path): bool
    {
        if (! $path) return false;
        return self::disk()->exists($path);
    }

    /** Remove um arquivo do disco de mídia (silencioso se não existir). */
    public static function delete(?string $path): void
    {
        if (! $path) return;
        if (self::disk()->exists($path)) {
            self::disk()->delete($path);
        }
    }
}
