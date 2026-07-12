{{--
    Partial: corpo do currículo estilizado.
    Espera receber $resume (array com objective, summary, experiences,
    education, skills, certifications, languages).
    O contêiner externo (com cor de fundo, padding, etc) fica com cada
    template pai (classic/modern/creative).
--}}

@php
    /** @var array $resume */
    $hard = $resume['skills']['hard'] ?? [];
    $soft = $resume['skills']['soft'] ?? [];
@endphp

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

@if ($hard || $soft)
    <section class="section">
        <h2 class="section-title">Habilidades</h2>
        @if ($hard)
            <div class="skills-block">
                <span class="skills-label">Técnicas:</span>
                @foreach ($hard as $s)
                    <span class="chip chip-hard">{{ $s }}</span>
                @endforeach
            </div>
        @endif
        @if ($soft)
            <div class="skills-block">
                <span class="skills-label">Comportamentais:</span>
                @foreach ($soft as $s)
                    <span class="chip chip-soft">{{ $s }}</span>
                @endforeach
            </div>
        @endif
    </section>
@endif

@if (! empty($resume['languages']))
    <section class="section">
        <h2 class="section-title">Idiomas</h2>
        <ul class="lang-list">
            @foreach ($resume['languages'] as $l)
                <li>
                    <span class="lang-name">{{ $l['language'] }}</span>
                    @if ($l['level'])
                        <span class="lang-level">— {{ $l['level'] }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>
@endif

@if (! empty($resume['certifications']))
    <section class="section">
        <h2 class="section-title">Certificações</h2>
        <ul class="cert-list">
            @foreach ($resume['certifications'] as $c)
                <li>
                    <span class="cert-name">{{ $c['name'] }}</span>
                    @if ($c['issuer'])
                        <span class="cert-issuer"> — {{ $c['issuer'] }}</span>
                    @endif
                    @if ($c['year'])
                        <span class="cert-year"> ({{ $c['year'] }})</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>
@endif
