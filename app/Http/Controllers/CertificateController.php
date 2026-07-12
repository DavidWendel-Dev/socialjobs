<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class CertificateController extends Controller
{
    public function show(string $code): View
    {
        $certificate = null;
        $qrDataUri = null;

        try {
            if (class_exists(\App\Models\Certificate::class)) {
                $certificate = \App\Models\Certificate::query()
                    ->where('code', $code)
                    ->first();
            }
        } catch (\Throwable $e) {
            //
        }

        // Gera QR code em SVG data URI se a lib existir
        try {
            if (class_exists(\Endroid\QrCode\Builder\Builder::class)) {
                $result = \Endroid\QrCode\Builder\Builder::create()
                    ->writer(new \Endroid\QrCode\Writer\SvgWriter())
                    ->data(url('/certificates/'.$code))
                    ->size(220)
                    ->margin(0)
                    ->build();
                $qrDataUri = $result->getDataUri();
            }
        } catch (\Throwable $e) {
            //
        }

        return view('certificate', [
            'code'        => $code,
            'certificate' => $certificate,
            'qr'          => $qrDataUri,
        ]);
    }
}
