<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

require_once '../db/conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Primeiro, remover a imagem associada ao produto (se existir)
    $stmt = $pdo->prepare("SELECT imagem FROM produtos WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto && $produto['imagem']) {
        $imagemPath = 'uploads/produtos/' . $produto['imagem'];
        if (file_exists($imagemPath)) {
            unlink($imagemPath);
        }
    }

    // Agora, remover o produto do banco de dados
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
}

header("Location: gerenciar_produtos.php");
exit();
?>
