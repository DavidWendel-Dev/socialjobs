#!/usr/bin/env bash
# =============================================================================
# entrypoint.sh — inicialização do container PHP-FPM (SocialJobs)
# =============================================================================
set -euo pipefail

cd /var/www

# Garante que os diretórios de escrita existam
mkdir -p storage/framework/{cache,sessions,testing,views} \
         storage/logs \
         bootstrap/cache

# APP_KEY: se não estiver definido no .env, gera uma
if [ -f .env ] && ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force --ansi || true
fi

# Cache de configuração/rotas/views (idempotente)
if [ "${SKIP_LARAVEL_CACHE:-false}" != "true" ]; then
    php artisan config:cache  || true
    # route:cache desabilitado — Volt + closures em routes/web.php impedem
    # a serialização em produção. `config:cache` já ajuda bastante.
    # php artisan route:cache  || true
    php artisan view:cache    || true
    php artisan event:cache   || true
fi

# Symlink de storage → public/storage
php artisan storage:link --force >/dev/null 2>&1 || true

exec "$@"
