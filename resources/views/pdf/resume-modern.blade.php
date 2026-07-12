{{--
    Template MODERNO — Sidebar escura à esquerda com contato/skills e coluna
    principal com conteúdo. Ideal para tech, design, marketing, engenharia.
--}}
@php
    $hard = $resume['skills']['hard'] ?? [];
    $soft = $resume['skills']['soft'] ?? [];
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
        color: #1f2937;
        font-size: 10pt;
        line-height: 1.5;
        margin: 0;
    }

    /* Layout de 2 colunas usando table (dompdf-friendly) */
    .layout { width: 100%; }
    .row { display: table; width: 100%; min-height: 297mm; }
    .side, .main { display: table-cell; vertical-align: top; }
    .side {
        width: 34%;
        background: #1e293b;
        color: #f1f5f9;
        padding: 26px 18px;
    }
    .main { padding: 26px 22px; background: #ffffff; }

    /* ============ SIDEBAR ============ */
    .name {
        font-size: 20pt;
        font-weight: bold;
        color: #ffffff;
        line-height: 1.15;
        margin: 0 0 4px;
    }
    .headline {
        color: #cbd5e1;
        font-size: 10pt;
        margin: 0 0 20px;
        line-height: 1.3;
    }
    .side-title {
        color: #93c5fd;
        font-size: 9pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1.4px;
        margin: 18px 0 6px;
        padding-bottom: 3px;
        border-bottom: 1px solid #334155;
    }
    .contact-item {
        font-size: 9pt;
        color: #e2e8f0;
        margin: 0 0 5px;
        word-wrap: break-word;
    }
    .contact-item .lbl {
        color: #94a3b8;
        font-size: 8pt;
        display: block;
        margin-bottom: 1px;
    }
    .side-skill {
        display: inline-block;
        padding: 2px 8px;
        margin: 2px 3px 2px 0;
        background: #334155;
        color: #f1f5f9;
        border-radius: 3px;
        font-size: 9pt;
    }
    .side-soft {
        color: #e2e8f0;
        font-size: 9pt;
        margin: 0 0 3px;
    }
    .side-lang {
        color: #e2e8f0;
        font-size: 9pt;
        margin: 0 0 4px;
    }
    .side-lang b { color: #ffffff; }
    .side-lang .lvl { color: #94a3b8; font-style: italic; }

    /* ============ MAIN ============ */
    .section { margin-bottom: 14px; page-break-inside: avoid; }
    .section-title {
        font-size: 12pt;
        font-weight: bold;
        color: #1e293b;
        margin: 0 0 8px;
        padding-bottom: 4px;
        border-bottom: 2px solid #3b82f6;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .section-body { margin: 0; text-align: justify; color: #374151; }

    .exp-item, .edu-item { margin-bottom: 10px; }
    .exp-head { width: 100%; margin-bottom: 3px; }
    .exp-role, .edu-degree { text-align: left; vertical-align: top; }
    .exp-role-title, .edu-degree-title {
        font-weight: bold;
        font-size: 10.5pt;
        color: #111827;
    }
    .exp-company, .edu-institution { color: #3b82f6; }
    .exp-period {
        text-align: right;
        vertical-align: top;
        color: #6b7280;
        font-size: 9pt;
        font-weight: bold;
        white-space: nowrap;
    }
    .exp-bullets { margin: 4px 0 0 16px; padding: 0; }
    .exp-bullets li { margin-bottom: 2px; color: #374151; }

    /* Ocultar seções do partial que já foram para a sidebar */
    .hide-in-main { display: none; }
</style>
</head>
<body>
<div class="layout">
    <div class="row">
        {{-- ============ SIDEBAR ============ --}}
        <div class="side">
            @if (! empty($photo))
                <img src="{{ $photo }}" alt="" style="width:100px; height:100px; border-radius:50%; object-fit:cover; margin:0 0 12px; display:block; border:3px solid #93c5fd;">
            @endif
            <div class="name">{{ $name }}</div>
            @if ($headline)
                <div class="headline">{{ $headline }}</div>
            @endif

            <div class="side-title">Contato</div>
            @if ($phone)
                <div class="contact-item"><span class="lbl">Telefone</span>{{ $phone }}</div>
            @endif
            @if ($email)
                <div class="contact-item"><span class="lbl">E-mail</span>{{ $email }}</div>
            @endif
            @if ($address)
                <div class="contact-item"><span class="lbl">Endereço</span>{{ $address }}</div>
            @endif
            @if (($birth_date ?? '') !== '')
                <div class="contact-item"><span class="lbl">Nascimento</span>{{ $birth_date }}</div>
            @endif
            @if ($linkedin_url)
                <div class="contact-item"><span class="lbl">LinkedIn</span>{{ $linkedin_url }}</div>
            @endif
            @if ($portfolio_url)
                <div class="contact-item"><span class="lbl">Portfólio</span>{{ $portfolio_url }}</div>
            @endif

            @if ($hard)
                <div class="side-title">Técnicas</div>
                @foreach ($hard as $s)
                    <span class="side-skill">{{ $s }}</span>
                @endforeach
            @endif

            @if ($soft)
                <div class="side-title">Comportamentais</div>
                @foreach ($soft as $s)
                    <p class="side-soft">• {{ $s }}</p>
                @endforeach
            @endif

            @if (! empty($resume['languages']))
                <div class="side-title">Idiomas</div>
                @foreach ($resume['languages'] as $l)
                    <div class="side-lang">
                        <b>{{ $l['language'] }}</b>
                        @if ($l['level'])
                            <span class="lvl">— {{ $l['level'] }}</span>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>

        {{-- ============ MAIN ============ --}}
        <div class="main">
            @if (! empty($resume['objective']))
                <section class="section">
                    <h2 class="section-title">Objetivo Profissional</h2>
                    <p class="section-body">{{ $resume['objective'] }}</p>
                </section>
            @endif

            @if (! empty($resume['summary']))
                <section class="section">
                    <h2 class="section-title">Resumo Profissional</h2>
                    <p class="section-body">{{ $resume['summary'] }}</p>
                </section>
            @endif

            @if (! empty($resume['experiences']))
                <section class="section">
                    <h2 class="section-title">Experiência Profissional</h2>
                    @foreach ($resume['experiences'] as $exp)
                        <div class="exp-item">
                            <table class="exp-head" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="exp-role">
                                        <span class="exp-role-title">{{ $exp['role'] ?: 'Cargo' }}</span>
                                        @if ($exp['company'])
                                            <span class="exp-company"> — {{ $exp['company'] }}</span>
                                        @endif
                                    </td>
                                    @if ($exp['period'])
                                        <td class="exp-period">{{ $exp['period'] }}</td>
                                    @endif
                                </tr>
                            </table>
                            @if (! empty($exp['bullets']))
                                <ul class="exp-bullets">
                                    @foreach ($exp['bullets'] as $b)
                                        <li>{{ $b }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </section>
            @endif

            @if (! empty($resume['education']))
                <section class="section">
                    <h2 class="section-title">Formação Acadêmica</h2>
                    @foreach ($resume['education'] as $ed)
                        <div class="edu-item">
                            <table class="exp-head" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="edu-degree">
                                        <span class="edu-degree-title">{{ $ed['degree'] ?: 'Formação' }}</span>
                                        @if ($ed['institution'])
                                            <span class="edu-institution"> — {{ $ed['institution'] }}</span>
                                        @endif
                                    </td>
                                    @if ($ed['period'])
                                        <td class="exp-period">{{ $ed['period'] }}</td>
                                    @endif
                                </tr>
                            </table>
                        </div>
                    @endforeach
                </section>
            @endif

            @if (! empty($resume['certifications']))
                <section class="section">
                    <h2 class="section-title">Certificações</h2>
                    <ul style="margin:0 0 0 16px;padding:0;">
                        @foreach ($resume['certifications'] as $c)
                            <li style="margin-bottom:3px;">
                                <b style="color:#111827;">{{ $c['name'] }}</b>
                                @if ($c['issuer'])<span style="color:#6b7280;"> — {{ $c['issuer'] }}</span>@endif
                                @if ($c['year'])<span style="color:#6b7280;font-style:italic;"> ({{ $c['year'] }})</span>@endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </div>
</div>
</body>
</html>
