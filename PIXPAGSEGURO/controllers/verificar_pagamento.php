<?php
/**
 * ============================================================
 * 🔍 VERIFICAR PAGAMENTO PIX - PAGBANK SANDBOX
 * ============================================================
 * → Chamado a cada 5 segundos pelo arquivo JS (checkPaymentStatus.js)
 * → Retorna o status atual da transação PIX associada ao usuário logado
 * 
 * 💡 Busca o status da tabela `transacoes` usando o reference_id
 * ou, se não for informado, retorna a última transação do usuário logado.
 * ============================================================
 */

session_start();
require_once('../config/conexao.php');

header('Content-Type: application/json; charset=utf-8');

// ============================================================
// 1️⃣ Verifica se o usuário está autenticado
// ============================================================
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'error' => true,
        'message' => 'Usuário não autenticado.'
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

// ============================================================
// 2️⃣ Pega o reference_id via GET (ou nulo se não enviado)
// ============================================================
$referenceId = $_GET['ref'] ?? $_GET['reference_id'] ?? null;

// ============================================================
// 3️⃣ Inicializa log opcional (para debug)
// ============================================================
$logPath = __DIR__ . '/logs/pix_status_log.txt';
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
}

file_put_contents($logPath, "\n\n=========================\n" . date('Y-m-d H:i:s') . " - Iniciando verificação\n", FILE_APPEND);
file_put_contents($logPath, "User ID: {$userId}\nReference ID: {$referenceId}\n", FILE_APPEND);

try {
    // ============================================================
    // 4️⃣ Consulta no banco
    // ============================================================
    if ($referenceId) {
        $stmt = $pdo->prepare("
            SELECT status, reference_id, valor, metodo_pagamento
            FROM transacoes
            WHERE reference_id = :ref
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute(['ref' => $referenceId]);
    } else {
        // Caso não tenha referência, pega a última transação do usuário logado
        $stmt = $pdo->prepare("
            SELECT t.status, t.reference_id, t.valor, t.metodo_pagamento
            FROM transacoes t
            JOIN usuarios u ON u.email = t.cliente_email
            WHERE u.id = :id
            ORDER BY t.id DESC
            LIMIT 1
        ");
        $stmt->execute(['id' => $userId]);
    }

    $transacao = $stmt->fetch(PDO::FETCH_ASSOC);

    // ============================================================
    // 5️⃣ Retorno JSON
    // ============================================================
    if ($transacao) {
        $statusNormalizado = ucfirst(strtolower(trim($transacao['status'])));
        $json = [
            'error' => false,
            'reference_id' => $transacao['reference_id'],
            'status' => $statusNormalizado,
            'valor' => number_format($transacao['valor'], 2, ',', '.'),
            'metodo' => $transacao['metodo_pagamento']
        ];

        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        file_put_contents($logPath, "Status encontrado: {$statusNormalizado}\n", FILE_APPEND);
    } else {
        echo json_encode([
            'error' => true,
            'message' => 'Nenhuma transação PIX encontrada.'
        ]);
        file_put_contents($logPath, "Nenhuma transação encontrada.\n", FILE_APPEND);
    }

} catch (Exception $e) {
    // ============================================================
    // 6️⃣ Tratamento de erro
    // ============================================================
    $errorMsg = 'Erro ao consultar pagamento: ' . $e->getMessage();
    echo json_encode([
        'error' => true,
        'message' => $errorMsg
    ]);

    file_put_contents($logPath, "❌ ERRO: {$errorMsg}\n", FILE_APPEND);
}
