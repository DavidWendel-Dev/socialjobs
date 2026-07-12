<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Emite certificados de conclusão de curso: gera code único, QR code e PDF.
 */
class CertificateService
{
    public function issue(User $user, Course $course): Certificate
    {
        // Se já existe, apenas devolve
        $existing = Certificate::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Código alfanumérico de 16 chars — retentar em colisão (bastante improvável)
        do {
            $code = strtoupper(Str::random(16));
        } while (Certificate::where('code', $code)->exists());

        $certificate = Certificate::create([
            'user_id'   => $user->id,
            'course_id' => $course->id,
            'code'      => $code,
            'issued_at' => now(),
        ]);

        // Gera QR code apontando para a URL pública do certificado
        $verifyUrl = url('/certificates/' . $code);
        $qrPng = Builder::create()
            ->writer(new PngWriter())
            ->data($verifyUrl)
            ->size(220)
            ->margin(8)
            ->build()
            ->getString();

        $qrBase64 = 'data:image/png;base64,' . base64_encode($qrPng);

        // Renderiza PDF do certificado
        $pdf = Pdf::loadView('certificates.template', [
            'user'      => $user,
            'course'    => $course,
            'code'      => $code,
            'qr'        => $qrBase64,
            'verifyUrl' => $verifyUrl,
        ])->setPaper('a4', 'landscape');

        $pdfPath = 'certificates/' . $code . '.pdf';
        Storage::put($pdfPath, $pdf->output());

        $certificate->pdf_path = $pdfPath;
        $certificate->save();

        return $certificate;
    }
}
