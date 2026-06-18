# DemoBank — Laboratório OWASP Top 10:2025

App **propositalmente vulnerável** (PHP + SQLite) para demonstrar, ao vivo, 4 riscos da
[OWASP Top 10:2025](https://owasp.org/Top10/2025/). Acompanha um roteiro de slides (reveal.js).

> ## ⚠️ AVISO DE SEGURANÇA
> Este código contém vulnerabilidades **de propósito** (SQL Injection, hashes fracos, IDOR…).
> **NUNCA** publique na internet, em servidor compartilhado ou rede não confiável.
> Use **apenas localmente / offline**, em ambiente isolado, para fins educacionais.

---

## Vulnerabilidades demonstradas

| OWASP 2025 | Onde | O que mostra |
|---|---|---|
| **A01** Broken Access Control | `profile.php`, `admin.php` | IDOR via `?id=` + painel admin sem checar papel |
| **A04** Cryptographic Failures | `login.php`, seed | senhas em MD5 sem salt (quebráveis na web) |
| **A05** Injection (SQLi) | `search.php` | termo de busca concatenado na SQL (UNION dump) |
| **A07** Authentication Failures | `login.php`, `register.php` | sem rate limit/lockout + senha fraca aceita |

---

## Pré-requisitos

- **PHP 8+** com a extensão **pdo_sqlite** (já vem por padrão na maioria das distros).
- Ubuntu/Debian: `sudo apt install -y php-cli php-sqlite3`
- Conferir: `php -m | grep sqlite`

---

## Como rodar

```bash
cd owasp-top10-demo

# 1) cria e popula o banco (db/app.sqlite)
php seed.php

# 2) sobe o app
php -S localhost:8000

# 3) abra no navegador
#    http://localhost:8000
```

Logins do seed:

```
admin / admin     (administrador)
alice / 123456    bob / senha123
carla / qwerty    diego / gatinho
```

## Como limpar / reexecutar

```bash
bash reset.sh          # apaga o banco e recria do zero (estado inicial)
```

> Só isso. Todo o estado vive em `db/app.sqlite`; apagar o arquivo zera tudo.

---

## Roteiro das demos (ordem sugerida = cadeia de ataque)

Payloads prontos para colar: [`attack/payloads.md`](attack/payloads.md).

### 1. A05 — SQL Injection (extrai os hashes)
- Acesse `http://localhost:8000/search.php`
- Busque `mouse` (normal), depois `' OR '1'='1` (lista tudo)
- Cole: `%' UNION SELECT id,username,password_md5 FROM users -- `
- → aparecem os hashes de senha de todos os usuários.

### 2. A04 — Cryptographic Failures (quebra os hashes)
- Copie um hash (ex.: `e10adc3949ba59abbe56e057f20f883e`)
- Cole em <https://crackstation.net> → resultado: `123456`
- Offline: `printf '%s' 123456 | md5sum` para conferir; ou john/hashcat com `attack/wordlist.txt`

### 3. A07 — Authentication Failures (brute force)
```bash
bash attack/bruteforce.sh admin http://localhost:8000
```
- Percorre a wordlist inteira sem nenhum bloqueio e encontra a senha.

### 4. A01 — Broken Access Control
- Faça login como `alice / 123456`
- **IDOR:** abra `http://localhost:8000/profile.php?id=1` (admin), `?id=3`, `?id=4`
  → CPF, saldo, hash e mensagens privadas de outras pessoas.
- **Forced browsing:** abra `http://localhost:8000/admin.php`
  → painel de admin abre mesmo você sendo usuário comum.

---

## Slides da apresentação

reveal.js via CDN — precisa ser servido por HTTP (não abra como `file://`):

```bash
# em outro terminal:
php -S localhost:8001 -t slides
# abra http://localhost:8001
```

- Setas ← → navegam; tecla **S** abre a *speaker view* com as falas do apresentador.
- Conteúdo editável em [`slides/slides.md`](slides/slides.md).
- Sem internet? Baixe o reveal.js e troque os links CDN do `slides/index.html` por caminhos locais.

---

## Estrutura

```
owasp-top10-demo/
├── README.md            # este arquivo
├── reset.sh             # apaga o DB e reexecuta o seed
├── seed.php             # cria schema + popula dados
├── index.php            # home / login
├── login.php  logout.php  register.php
├── dashboard.php        # área logada
├── profile.php          # A01 — IDOR
├── admin.php            # A01 — forced browsing
├── search.php           # A05 — SQLi
├── lib/{db.php,layout.php}
├── attack/{bruteforce.sh,wordlist.txt,payloads.md}
└── slides/{index.html,slides.md}
```

## Mitigações (resumo)
- **A05:** prepared statements / parâmetros vinculados; nunca exibir erro SQL.
- **A04:** `password_hash()` com Argon2/bcrypt; TLS; nada de MD5/SHA1 para senha.
- **A07:** rate limit, lockout, MFA, política de senha + checagem de senhas vazadas.
- **A01:** identidade pela sessão (não pela URL); checagem server-side de dono e papel; deny by default.
