<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Migra arquivos do disco local `public` para o R2.
 * Copia mantendo o mesmo path (avatars/*, covers/*, posts/*, etc.),
 * verifica se o upload deu certo e opcionalmente remove do local.
 *
 * Uso:
 *   php artisan media:migrate-to-r2                 → dry-run
 *   php artisan media:migrate-to-r2 --do            → executa
 *   php artisan media:migrate-to-r2 --do --delete   → executa e apaga local após sucesso
 */
class MigrateMediaToR2 extends Command
{
    protected $signature = 'media:migrate-to-r2
                            {--do : Executa o upload de verdade (sem essa flag é dry-run)}
                            {--delete : Após upload confirmado, apaga o arquivo local}
                            {--prefix=* : Só migra estes prefixos (ex: --prefix=avatars --prefix=covers). Padrão: avatars, covers, posts}';

    protected $description = 'Migra arquivos do disco público local para o Cloudflare R2';

    public function handle(): int
    {
        $prefixes = (array) $this->option('prefix');
        if (empty($prefixes)) {
            $prefixes = ['avatars', 'covers', 'posts'];
        }

        $do     = (bool) $this->option('do');
        $delete = (bool) $this->option('delete');

        $local = Storage::disk('public');
        $r2    = Storage::disk('r2');

        $totalOk = 0;
        $totalSkip = 0;
        $totalFail = 0;

        if (! $do) {
            $this->warn('DRY-RUN: nenhum arquivo será enviado. Use --do para executar.');
        }

        foreach ($prefixes as $prefix) {
            $files = $local->allFiles($prefix);
            $count = count($files);
            $this->info("[$prefix] {$count} arquivos");

            $bar = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($files as $path) {
                // Já existe no R2? Pula
                if ($r2->exists($path)) {
                    $totalSkip++;
                    $bar->advance();
                    continue;
                }

                if (! $do) {
                    $totalOk++;
                    $bar->advance();
                    continue;
                }

                try {
                    $stream = $local->readStream($path);
                    $ok = $r2->writeStream($path, $stream);
                    if (is_resource($stream)) fclose($stream);

                    if ($ok && $r2->exists($path)) {
                        $totalOk++;
                        if ($delete) {
                            $local->delete($path);
                        }
                    } else {
                        $totalFail++;
                        $this->newLine();
                        $this->error("FALHA: $path");
                    }
                } catch (\Throwable $e) {
                    $totalFail++;
                    $this->newLine();
                    $this->error("ERRO em $path: " . $e->getMessage());
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $this->info("Enviados/planejados: $totalOk");
        $this->info("Já existentes (pulados): $totalSkip");
        if ($totalFail > 0) {
            $this->error("Falhas: $totalFail");
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
