<?php
/**
 * ==============================================================
 * 🔔 NOTIFICAÇÕES PAGBANK - PIX e CARTÃO (SANDBOX + PRODUÇÃO)
 * ==============================================================
 * Este script é acionado automaticamente pelo PagBank (via webhook)
 * quando há mudança de status em uma cobrança PIX ou cartão.
 *
 * Ele atualiza as tabelas:
 *   - `transacoes`
 *   - `vendas`
 *
 * Funciona com o fluxo usado em PaymentControllerPix.php e
 * verificar_pagamento.php.
 * ==============================================================
 */

require_once('../config/conexao.php');

// ============================================================
// 🪵 LOG DE ENTRADA
// ============================================================
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) mkdir($logDir, 0777, true);
$logFile = $logDir . '/notificacoes_pix_log.txt';

// Captura o payload enviado pelo PagBank
$payload = file_get_contents('php://input');

// Loga o conteúdo recebido
file_put_contents(
    $logFile,
    "\n\n==============================\n" .
    "📅 " . date('Y-m-d H:i:s') . "\n" .
    "📩 PAYLOAD RECEBIDO:\n" . $payload . "\n" .
    "==============================\n",
    FILE_APPEND
);

// ============================================================
// 🔍 DECODIFICA JSON
// ============================================================
$data = json_decode($payload, true);
if (!$data) {
    http_response_code(400);
    echo "❌ Payload inválido ou vazio.";
    exit;
}

// Extrai dados principais
$referenceId  = $data['reference_id'] ?? null;
$status       = $data['charges'][0]['status'] ?? null;
$paymentType  = strtoupper($data['charges'][0]['payment_method']['type'] ?? 'DESCONHECIDO');

if (!$referenceId || !$status) {
    http_response_code(400);
    echo "❌ Campos insuficientes no payload (sem reference_id ou status).";
    file_put_contents($logFile, "⚠️ Erro: Payload incompleto.\n", FILE_APPEND);
    exit;
}

// ============================================================
// 🧭 NORMALIZA STATUS PAGBANK → SISTEMA LOCAL
// ============================================================
switch (strtoupper($status)) {
    case 'PAID':
        $novoStatus = 'Pago';
        break;
    case 'WAITING':
    case 'PENDING':
        $novoStatus = 'Pendente';
        break;
    case 'CANCELED':
    case 'CANCELLED':
        $novoStatus = 'Cancelado';
        break;
    case 'REFUNDED':
        $novoStatus = 'Estornado';
        break;
    default:
        $novoStatus = ucfirst(strtolower($status));
        break;
}

// ============================================================
// 💾 ATUALIZA TABELA TRANSACOES
// ============================================================
try {
    // Atualiza o status da transação principal
    $stmt = $pdo->prepare("
        UPDATE transacoes
        SET status = :status
        WHERE reference_id = :ref
    ");
    $stmt->execute([
        'status' => $novoStatus,
        'ref'    => $referenceId
    ]);

    // Busca informações da transação
    $stmtDados = $pdo->prepare("SELECT cliente_email, valor FROM transacoes WHERE reference_id = :ref");
    $stmtDados->execute(['ref' => $referenceId]);
    $transacao = $stmtDados->fetch(PDO::FETCH_ASSOC);

    if ($transacao) {
        // Busca o cliente pelo e-mail
        $stmtCliente = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
        $stmtCliente->execute(['email' => $transacao['cliente_email']]);
        $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

        if ($cliente) {
            $clienteId = $cliente['id'];
            $metodo = ($paymentType === 'PIX') ? 'Pago (Pix)' : 'Pago (Cartão de Crédito)';

            // Verifica se já existe venda
            $stmtVerifica = $pdo->prepare("SELECT id FROM vendas WHERE reference_id = :ref LIMIT 1");
            $stmtVerifica->execute(['ref' => $referenceId]);

            if ($stmtVerifica->rowCount() > 0) {
                // Atualiza venda existente
                $stmtUpdate = $pdo->prepare("
                    UPDATE vendas 
                    SET status = :status,
                        status_pedido = :status_pedido
                    WHERE reference_id = :ref
                ");
                $stmtUpdate->execute([
                    'status' => $metodo,
                    'status_pedido' => $novoStatus === 'Pago' ? 'Pedido Confirmado' : $novoStatus,
                    'ref' => $referenceId
                ]);
            } else {
                // Cria nova venda
                $stmtInsert = $pdo->prepare("
                    INSERT INTO vendas (reference_id, cliente_id, total, status, status_pedido, data_venda)
                    VALUES (:ref, :cliente_id, :total, :status, :status_pedido, NOW())
                ");
                $stmtInsert->execute([
                    'ref' => $referenceId,
                    'cliente_id' => $clienteId,
                    'total' => $transacao['valor'],
                    'status' => $metodo,
                    'status_pedido' => $novoStatus
                ]);
            }
        }
    }

    // ============================================================
    // ✅ LOG DE SUCESSO
    // ============================================================
    file_put_contents(
        $logFile,
        "✅ Atualizado com sucesso | REF={$referenceId} | STATUS={$novoStatus} | MÉTODO={$paymentType}\n",
        FILE_APPEND
    );

    http_response_code(200);
    echo "✅ Notificação processada com sucesso.";

} catch (Exception $e) {
    // ============================================================
    // ❌ ERRO AO ATUALIZAR
    // ============================================================
    $erroMsg = "❌ Erro ao atualizar banco: " . $e->getMessage();
    file_put_contents($logFile, $erroMsg . "\n", FILE_APPEND);

    http_response_code(500);
    echo $erroMsg;
}
