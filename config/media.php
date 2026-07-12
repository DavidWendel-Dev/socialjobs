<?php

/*
| Configuração central de mídia — permite trocar o disco (public local ↔ R2)
| via ENV sem alterar código. Todos os pontos de upload devem ler daqui.
|
| MEDIA_DISK=public (padrão em dev) OU MEDIA_DISK=r2 (produção)
*/

return [
    /*
     * Nome do disco (config/filesystems.php) usado para armazenar
     * avatars, capas de perfil, imagens de posts, anexos etc.
     */
    'disk' => env('MEDIA_DISK', 'public'),

    /*
     * URL base pública (opcional). Se null, usa a `url` do próprio disco.
     * Ex.: https://cdn.example.com
     */
    'public_url' => env('MEDIA_PUBLIC_URL'),
];
