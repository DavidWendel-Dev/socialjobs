{{--
    Template CRIATIVO — Header com gradient colorido + corpo estilizado.
    Ideal para publicidade, design, comunicação, mídias sociais.
--}}
@php
    $contactBits = array_filter([$phone ?? null, $email ?? null, $address ?? null, ($birth_date ?? '') !== '' ? 'Nasc.: '.$birth_date : null, $linkedin_url ?? null, $portfolio_url ?? null]);
@endphp
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Currículo — {{ $name }}</title>
<style>
    @page { margin: 0; }
    * { box-sizing: border-box; }
    body {
        font-family: 'DejaVu Sans', Arial, sans-serif;
        color: #111827;
        font-size: 10.5pt;
        line-height: 1.55;
        margin: 0;
    }

    /* ============ HEADER GRADIENT ============ */
    .header {
        background: linear-gradient(135deg, #a855f7 0%, #ec4899 55%, #f97316 100%);
        color: white;
        padding: 26px 30px 20px;
    }
    .name {
        font-size: 26pt;
        font-weight: bold;
        margin: 0 0 3px;
        letter-spacing: -0.5px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.12);
    }
    .headline {
        font-size: 12pt;
        margin: 0 0 12px;
        opacity: 0.95;
    }
    .contact-grid { font-size: 9.5pt; opacity: 0.92; }
    .contact-grid span { white-space: nowrap; }
    .contact-grid span + span::before { content: " • "; opacity: 0.6; }

    /* ============ BODY ============ */
    .body { padding: 22px 30px 30px; }

    .accent-bar {
        border-left: 4px solid #ec4899;
        padding: 10px 14px;
        background: #fef3f8;
        border-radius: 4px;
        margin: 0 0 16px;
        color: #831843;
        font-size: 10pt;
        font-style: italic;
    }

    /* ============ SEÇÕES ============ */
    .section { margin-bottom: 14px; page-break-inside: avoid; }
    .section-title {
        font-size: 13pt;
        font-weight: bold;
        color: #a855f7;
        margin: 0 0 8px;
        letter-spacing: 0.5px;
        position: relative;
    }
    .section-title::before {
        content: "◆ ";
        color: #ec4899;
    }
    .section-body { margin: 0; color: #374151; text-align: justify; }

    /* ============ EXPERIÊNCIA ============ */
    .exp-item, .edu-item {
        margin-bottom: 10px;
        padding-left: 14px;
        border-left: 3px solid #f3e8ff;
    }
    .exp-head { width: 100%; margin-bottom: 3px; }
    .exp-role, .edu-degree { text-align: left; vertical-align: top; }
    .exp-role-title, .edu-degree-title {
        font-weight: bold;
        font-size: 11pt;
        color: #111827;
    }
    .exp-company, .edu-institution { color: #a855f7; }
    .exp-period {
        text-align: right;
        vertical-align: top;
        color: #ec4899;
        font-size: 9pt;
        font-weight: bold;
        white-space: nowrap;
    }
    .exp-bullets { margin: 4px 0 0 12px; padding: 0; list-style: none; }
    .exp-bullets li {
        margin-bottom: 2px;
        padding-left: 12px;
        position: relative;
        color: #374151;
    }
    .exp-bullets li::before {
        content: "▸";
        color: #ec4899;
        position: absolute;
        left: 0;
    }

    /* ============ SKILLS ============ */
    .skills-block { margin-bottom: 6px; }
    .skills-label {
        font-weight: bold;
        color: #7c3aed;
        margin-right: 4px;
    }
    .chip {
        display: inline-block;
        padding: 2px 10px;
        margin: 2px 4px 2px 0;
        border-radius: 12px;
        font-size: 9pt;
    }
    .chip-hard { background: #fdf4ff; color: #86198f; border: 1px solid #f0abfc; }
    .chip-soft { background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; }

    /* ============ IDIOMAS/CERTIFICAÇÕES ============ */
    .lang-list, .cert-list { margin: 0 0 0 12px; padding: 0; list-style: none; }
    .lang-list li, .cert-list li {
        margin-bottom: 3px;
        padding-left: 12px;
        position: relative;
    }
    .lang-list li::before, .cert-list li::before {
        content: "★";
        color: #f97316;
        position: absolute;
        left: 0;
        font-size: 8pt;
    }
    .lang-name, .cert-name { font-weight: bold; color: #111827; }
    .lang-level, .cert-issuer, .cert-year { color: #6b7280; font-style: italic; }
</style>
</head>
<body>
    <div class="header">
        @if (! empty($photo))
            <img src="{{ $photo }}" alt="" style="width:85px; height:85px; border-radius:50%; object-fit:cover; margin:0 0 8px; display:block; border:3px solid rgba(255,255,255,0.6);">
        @endif
        <h1 class="name">{{ $name }}</h1>
        @if ($headline)
            <p class="headline">{{ $headline }}</p>
        @endif
        @if (count($contactBits) > 0)
            <p class="contact-grid">
                @foreach ($contactBits as $c)
                    <span>{{ $c }}</span>
                @endforeach
            </p>
        @endif
    </div>

    <div class="body">
        <div class="accent-bar">
            ✦ Currículo profissional criado com apoio de IA no SocialJobs
        </div>

        @include('pdf._resume-body', ['resume' => $resume])
    </div>
</body>
</html>
