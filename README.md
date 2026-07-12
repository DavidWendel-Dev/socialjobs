# SocialJobs

Plataforma social de empregos construída em **Laravel 12 + Livewire 3 + Tailwind CSS**.
Conecta candidatos e empresas em um único lugar: feed social, currículo digital, testes de skill,
cursos, mensagens em tempo real e assistente de IA.

> ⚠️ Projeto proprietário. Código disponível publicamente para consulta e portfolio.
> Uso comercial ou pessoal requer autorização por escrito. Veja [LICENSE](LICENSE).

**Autor:** [David Wendel](https://github.com/DavidWendel-Dev) · [LinkedIn](https://www.linkedin.com/in/david-wendel-10296b418)

---

## ✨ Principais recursos

- **Feed social** com posts, curtidas, comentários, menções, imagens e música (Deezer).
- **Vagas** — publicação, busca com filtros, matching por skills e Kanban de candidaturas.
- **Perfil profissional** com experiências, formação, portfólio e currículo em PDF.
- **Testes de skill** com integridade (detecção de troca de aba, tentativas de copiar, etc.).
- **Cursos internos** com módulos, matrículas por token e emissão de certificado.
- **Mensagens diretas** entre candidatos e empresas.
- **Assistente de IA** para geração de descrição de vaga, otimização de currículo, simulador
  de entrevistas etc. Compatível com qualquer provider OpenAI-like (OpenAI, Groq, Ollama…).
- **Realtime** via Laravel Reverb (WebSocket) para notificações e mensagens.
- **Storage flexível** — local, S3 ou Cloudflare R2 (via variável `MEDIA_DISK`).
- **SEO ready** — sitemap.xml dinâmico, Open Graph, Twitter Card, JSON-LD Schema.org.
- **HTML minificado** em produção via middleware próprio (`MinifyHtml`).

---

## 🧰 Stack

| Camada | Ferramenta |
|---|---|
| Backend | PHP 8.3, Laravel 12 |
| Frontend | Livewire 3, Alpine.js, Tailwind CSS |
| Banco | MySQL 8.4 |
| Cache/Fila | Redis 7 |
| Busca | Laravel Scout (Meilisearch ou database) |
| Broadcast | Laravel Reverb |
| Storage | Filesystem local, S3 ou Cloudflare R2 |
| IA | OpenAI-compatible (OpenAI, Groq, Ollama, DeepSeek, LM Studio…) |
| Container | Docker + Nginx |

---

## 🚀 Como rodar localmente

### 1. Sem Docker (mais rápido)

Pré-requisitos: PHP 8.3+, Composer, Node.js 20+, MySQL 8+.

```bash
git clone https://github.com/DavidWendel-Dev/socialjobs.git
cd socialjobs

cp .env.example .env
composer install
npm install

php artisan key:generate
php artisan migrate --seed

npm run dev              # em um terminal
php artisan serve        # em outro
```

Acesse http://localhost:8000

### 2. Com Docker Compose (mais próximo de produção)

```bash
git clone https://github.com/DavidWendel-Dev/socialjobs.git
cd socialjobs

cp .env.docker .env
# edite .env: DB_PASSWORD, REVERB_APP_KEY, MEILISEARCH_KEY, AI_API_KEY etc.

docker compose up -d --build

docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Acesse http://localhost

---

## ⚙️ Configuração essencial

| Variável | O que é | Exemplo |
|---|---|---|
| `APP_NAME` | Nome mostrado no `<title>` e no header. | `SocialJobs` |
| `APP_URL` | URL base do site. | `https://example.com` |
| `DB_*` | Conexão MySQL. | ver `.env.example` |
| `MEDIA_DISK` | Onde salvar avatares, capas e imagens de post. | `public`, `s3` ou `r2` |
| `AI_API_KEY` | Chave do provider de IA (opcional). | `sk-…` |
| `REVERB_*` | Configura WebSocket para notificações em tempo real. | ver `.env.example` |

Storage remoto (Cloudflare R2) exige `league/flysystem-aws-s3-v3` (já incluso).

---

## 🧪 Rodar testes

```bash
php artisan test
```

CI configurado em `.github/workflows/ci.yml` roda PHPUnit + PHPStan + Pint em cada PR.

---

## 📁 Estrutura resumida

```
app/
  Http/Controllers/    # controllers tradicionais (Landing, Sitemap, etc.)
  Http/Middleware/     # SecurityHeaders, MinifyHtml, EnsureUserType…
  Livewire/            # componentes Livewire (Feed, Jobs, Profile, Ai…)
  Models/              # Eloquent models
  Services/            # regras de negócio (AiService, CurriculumService…)
  Support/             # helpers (Media, etc.)
config/                # arquivos de configuração
database/
  migrations/          # migrations
  seeders/             # dados de exemplo
docker/                # Dockerfile, nginx, php-fpm, supervisord
lang/pt_BR/            # traduções PT-BR (auth, validation…)
resources/
  css/  js/            # entrypoints do Vite
  views/               # Blade + Livewire views
routes/
  web.php  api.php     # rotas
```

---

## 🤝 Contribuições

Este é um projeto proprietário e não aceita PRs externos. Se você encontrou
um bug ou tem sugestões, sinta-se à vontade para abrir uma **Issue**.

Estilo de código: PSR-12 via **Laravel Pint** (`vendor/bin/pint`).

---

## 📄 Licença

**Software proprietário.** Todos os direitos reservados a David Wendel.
Uso, cópia, modificação e distribuição são proibidos sem autorização expressa.
Veja o arquivo [LICENSE](LICENSE) para os termos completos.

Se você quer usar/adaptar comercialmente, entre em contato pelo [LinkedIn](https://www.linkedin.com/in/david-wendel-10296b418).

---

## 🙌 Autor

**David Wendel** — Full Stack Developer
[GitHub](https://github.com/DavidWendel-Dev) · [LinkedIn](https://www.linkedin.com/in/david-wendel-10296b418)

Construído sobre Laravel, Livewire, Tailwind CSS e Alpine.js. Ícones de [Heroicons](https://heroicons.com).
