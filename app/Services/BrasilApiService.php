<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Cliente da BrasilAPI para consulta pública de CNPJ.
 * Sem chave, sem cadastro, sem limite formal.
 * https://brasilapi.com.br/api/cnpj/v1/{cnpj}
 *
 * Este service serve como PROXY do backend (evita problemas de CORS) +
 * cache de 24h por CNPJ (dados cadastrais mudam pouco).
 */
class BrasilApiService
{
    private const CNPJ_URL = 'https://brasilapi.com.br/api/cnpj/v1/';

    /**
     * Limpa a máscara do CNPJ (ex.: "12.345.678/0001-99" → "12345678000199").
     */
    public static function stripCnpj(string $cnpj): string
    {
        return (string) preg_replace('/\D+/', '', $cnpj);
    }

    /**
     * Valida o CNPJ pelo algoritmo dos dígitos verificadores.
     */
    public static function isValidCnpj(string $cnpj): bool
    {
        $c = self::stripCnpj($cnpj);
        if (strlen($c) !== 14) {
            return false;
        }
        // Rejeita sequências repetidas (00000000000000, 11111111111111, ...)
        if (preg_match('/^(\d)\1{13}$/', $c)) {
            return false;
        }

        for ($t = 12; $t < 14; $t++) {
            $sum = 0;
            $mult = $t - 7;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $c[$i] * $mult;
                $mult = ($mult === 2) ? 9 : $mult - 1;
            }
            $digit = ($sum % 11 < 2) ? 0 : 11 - ($sum % 11);
            if ((int) $c[$t] !== $digit) {
                return false;
            }
        }
        return true;
    }

    /**
     * Consulta os dados públicos de um CNPJ na BrasilAPI.
     * Retorna array simplificado ou null se não encontrado / inválido / falha.
     *
     * @return array{
     *     cnpj:string, razao_social:string, nome_fantasia:string,
     *     situacao:string, porte:string, natureza_juridica:string,
     *     cnae_principal:string, capital_social:float, telefone:string,
     *     email:string, address:array, opcao_simples:?bool
     * }|null
     */
    public function lookupCnpj(string $cnpj): ?array
    {
        $clean = self::stripCnpj($cnpj);
        if (! self::isValidCnpj($clean)) {
            return null;
        }

        return Cache::remember('brasilapi:cnpj:' . $clean, now()->addHours(24), function () use ($clean) {
            try {
                $response = Http::timeout(6)
                    ->acceptJson()
                    ->withHeaders(['User-Agent' => 'SocialJobs/1.0'])
                    ->get(self::CNPJ_URL . $clean);

                if (! $response->successful()) {
                    return null;
                }

                $d = (array) $response->json();
                if (empty($d) || empty($d['cnpj'] ?? null)) {
                    return null;
                }

                return [
                    'cnpj'              => (string) ($d['cnpj'] ?? $clean),
                    'razao_social'      => (string) ($d['razao_social'] ?? ''),
                    'nome_fantasia'     => (string) ($d['nome_fantasia'] ?? ''),
                    'situacao'          => (string) ($d['descricao_situacao_cadastral'] ?? ''),
                    'porte'             => (string) ($d['porte'] ?? $d['descricao_porte'] ?? ''),
                    'natureza_juridica' => (string) ($d['natureza_juridica'] ?? ''),
                    'cnae_principal'    => trim(
                        (string) ($d['cnae_fiscal_descricao'] ?? '')
                    ),
                    'capital_social'    => (float) ($d['capital_social'] ?? 0),
                    'telefone'          => (string) ($d['ddd_telefone_1'] ?? ''),
                    'email'             => (string) ($d['email'] ?? ''),
                    'opcao_simples'     => isset($d['opcao_pelo_simples']) ? (bool) $d['opcao_pelo_simples'] : null,
                    'address'           => [
                        'logradouro' => (string) ($d['logradouro'] ?? ''),
                        'numero'     => (string) ($d['numero'] ?? ''),
                        'complemento'=> (string) ($d['complemento'] ?? ''),
                        'bairro'     => (string) ($d['bairro'] ?? ''),
                        'municipio'  => (string) ($d['municipio'] ?? ''),
                        'uf'         => (string) ($d['uf'] ?? ''),
                        'cep'        => (string) ($d['cep'] ?? ''),
                    ],
                ];
            } catch (\Throwable $e) {
                report($e);
                return null;
            }
        });
    }
}
