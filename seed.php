<?php
/**
 * Cria o schema e popula o banco. Idempotente: dropa e recria tudo.
 * Uso:  php seed.php
 */
require_once __DIR__ . '/lib/db.php';

// garante a pasta db/
@mkdir(__DIR__ . '/db', 0777, true);

$pdo = db();

$pdo->exec('DROP TABLE IF EXISTS users');
$pdo->exec('DROP TABLE IF EXISTS products');
$pdo->exec('DROP TABLE IF EXISTS messages');

$pdo->exec('
    CREATE TABLE users (
        id           INTEGER PRIMARY KEY AUTOINCREMENT,
        username     TEXT UNIQUE NOT NULL,
        password_md5 TEXT NOT NULL,   -- A04: hash MD5 SEM salt (fraco de propósito)
        email        TEXT,
        cpf          TEXT,            -- dado sensível p/ demonstrar impacto do IDOR
        saldo        REAL,            -- idem
        role         TEXT DEFAULT "user"
    )
');

$pdo->exec('
    CREATE TABLE products (
        id          INTEGER PRIMARY KEY AUTOINCREMENT,
        name        TEXT NOT NULL,
        price       REAL,
        secret_note TEXT
    )
');

$pdo->exec('
    CREATE TABLE messages (
        id      INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        body    TEXT
    )
');

// --- Usuários (senhas fracas e comuns -> MD5 quebra instantaneamente) ---
$users = [
    // username,  senha,       email,                  cpf,             saldo,    role
    ['admin',    'admin',      'admin@demobank.test',  '000.000.000-00', 999999.0, 'admin'],
    ['alice',    '123456',     'alice@demobank.test',  '111.111.111-11', 4820.50,  'user'],
    ['bob',      'senha123',   'bob@demobank.test',    '222.222.222-22', 132.00,   'user'],
    ['carla',    'qwerty',     'carla@demobank.test',  '333.333.333-33', 75300.10, 'user'],
    ['diego',    'gatinho',    'diego@demobank.test',  '444.444.444-44', 0.0,      'user'],
];

$ins = $pdo->prepare('INSERT INTO users (username, password_md5, email, cpf, saldo, role)
                      VALUES (?, ?, ?, ?, ?, ?)');
foreach ($users as $u) {
    [$name, $pass, $email, $cpf, $saldo, $role] = $u;
    $ins->execute([$name, md5($pass), $email, $cpf, $saldo, $role]);
}

// --- Produtos (alvo da busca / SQLi) ---
$products = [
    ['Notebook Pro 15',     7999.00, 'fornecedor: ACME, margem 22%'],
    ['Mouse sem fio',         129.90, 'fornecedor: LogiX'],
    ['Teclado mecânico',      349.00, 'estoque baixo'],
    ['Monitor 27" 144Hz',    1899.00, 'promo interna terça'],
    ['Webcam Full HD',        219.90, 'fornecedor: ACME'],
    ['Headset Gamer',         399.00, 'recall lote 2024-A (confidencial)'],
];
$ip = $pdo->prepare('INSERT INTO products (name, price, secret_note) VALUES (?, ?, ?)');
foreach ($products as $p) {
    $ip->execute($p);
}

// --- Mensagens privadas (reforçam o impacto do IDOR no profile) ---
$messages = [
    [2, 'Alice: minha senha do cofre é o nome do meu gato 🐈'],
    [2, 'Alice: transferir R$ 2.000 pro Bob na sexta'],
    [3, 'Bob: pedir aumento ao gerente'],
    [4, 'Carla: PIX da herança caiu, não contar a ninguém'],
];
$im = $pdo->prepare('INSERT INTO messages (user_id, body) VALUES (?, ?)');
foreach ($messages as $m) {
    $im->execute($m);
}

echo "OK: banco criado em db/app.sqlite\n";
echo "Usuários: " . count($users) . " | Produtos: " . count($products) . " | Mensagens: " . count($messages) . "\n";
echo "Login admin -> admin / admin\n";
