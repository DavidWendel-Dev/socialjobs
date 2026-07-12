{{--
    Template CLÁSSICO — Currículo em 1 coluna, tipografia serif elegante,
    ideal para vagas corporativas tradicionais (jurídico, financeiro, RH).
    Cabeçalho centralizado com nome + contato do PERFIL do usuário.
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
    @page { margin: 20mm 18mm 18mm; }
    * { box-sizing: border-box; }
    body {
        font-family: 'DejaVu Serif', Georgia, serif;
        color: #1f2937;
        font-size: 10.5pt;
        line-height: 1.5;
        margin: 0;
    }

    /* ============ HEADER ============ */
    .header { text-align: center; margin-bottom: 12px; }
    .name {
        font-size: 22pt;
        font-weight: bold;
        margin: 0 0 3px;
        letter-spacing: 1.5px;
        color: #111827;
    }
    .headline {
        color: #6b7280;
        font-style: italic;
        margin: 0 0 8px;
        font-size: 11pt;
    }
    .contact-line {
        color: #4b5563;
        font-size: 9pt;
        margin: 0;
    }
    .contact-line span { white-space: nowrap; }
    .contact-line span + span::before { content: " · "; color: #9ca3af; }
    hr.divider {
        border: none;
        border-top: 1.5px solid #111827;
        margin: 8px 0 14px;
    }

    /* ============ SEÇÕES ============ */
    .section { margin-bottom: 12px; page-break-inside: avoid; }
    .section-title {
        font-size: 12pt;
        font-weight: bold;
        color: #111827;
        margin: 0 0 6px;
        padding-bottom: 3px;
        border-bottom: 1px solid #9ca3af;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .section-body {
        margin: 0 0 4px;
        text-align: justify;
    }

    /* ============ EXPERIÊNCIA ============ */
    .exp-item, .edu-item { margin-bottom: 8px; }
    .exp-head { width: 100%; margin-bottom: 2px; }
    .exp-role, .edu-degree { text-align: left; vertical-align: top; }
    .exp-role-title, .edu-degree-title { font-weight: bold; font-size: 11pt; }
    .exp-company, .edu-institution { color: #4b5563; }
    .exp-period {
        text-align: right;
        vertical-align: top;
        color: #6b7280;
        font-size: 9.5pt;
        font-style: italic;
        white-space: nowrap;
    }
    .exp-bullets { margin: 4px 0 0 18px; padding: 0; }
    .exp-bullets li { margin-bottom: 2px; }

    /* ============ SKILLS ============ */
    .skills-block { margin-bottom: 5px; }
    .skills-label { font-weight: bold; margin-right: 4px; }
    .chip {
        display: inline-block;
        padding: 1px 8px;
        margin: 2px 4px 2px 0;
        border-radius: 10px;
        font-size: 9pt;
        border: 1px solid #d1d5db;
    }
    .chip-hard { background: #f3f4f6; color: #111827; }
    .chip-soft { background: #fff; color: #4b5563; font-style: italic; }

    /* ============ IDIOMAS/CERTIFICAÇÕES ============ */
    .lang-list, .cert-list { margin: 0 0 0 18px; padding: 0; }
    .lang-list li, .cert-list li { margin-bottom: 3px; }
    .lang-name, .cert-name { font-weight: bold; }
    .lang-level, .cert-issuer, .cert-year { color: #6b7280; font-style: italic; }
</style>
</head>
<body>
    <header class="header">
        @if (! empty($photo))
            <img src="{{ $photo }}" alt="" style="width:70px; height:70px; border-radius:50%; object-fit:cover; margin:0 auto 8px; display:block;">
        @endif
        <h1 class="name">{{ $name }}</h1>
        @if ($headline)
            <p class="headline">{{ $headline }}</p>
        @endif
        @if (count($contactBits) > 0)
            <p class="contact-line">
                @foreach ($contactBits as $c)
                    <span>{{ $c }}</span>
                @endforeach
            </p>
        @endif
    </header>
    <hr class="divider">

    @include('pdf._resume-body', ['resume' => $resume])
</body>
</html>
