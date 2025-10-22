<?php
// Configurações do PagSeguro
define('TOKEN', 'e7a07e98-4444-4327-806f-d4f71a863b60a17c1e8f43afb7fd0ff6a5683c371ae3c30c-1fc9-4dce-a27b-6ba4fd4cdde2'); // Seu token PagSeguro
define('URL_PAGSEGURO', 'https://sandbox.api.pagseguro.com/orders'); // URL para consulta do pagamento

// Verifica se o parâmetro reference_id foi passado
if (!isset($_GET['reference_id'])) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Referência não fornecida.']);
    exit;
}

$referenceId = $_GET['reference_id'];

// Faz a requisição à API do PagSeguro para verificar o status do pagamento
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => URL_PAGSEGURO . '/' . $referenceId,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . TOKEN,
        'Content-Type: application/json'
    ],
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($curl);
curl_close($curl);

if ($response === false) {
    echo json_encode(['status' => 'erro', 'mensagem' => 'Falha na comunicação com a API do PagSeguro.']);
    exit;
}

$pagamento = json_decode($response, true);

// Verifica o status do pagamento e retorna o status correto
if (isset($pagamento['status']) && $pagamento['status'] == 'PAID') {
    echo json_encode(['status' => 'pago']);
} else {
    echo json_encode(['status' => 'aguardando']);
}
?>