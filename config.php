<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} // Inicia a sessão para gerenciar o estado do usuário

// Configurações básicas
$site_name = "Reservado (Nome do Estabelecimento)";
$primary_color = "#1c1c1c";
$secondary_color = "#f4f4f4";
$text_color = "#d4af37";
$banner_image_path = '../cardapio-dinamico/path/r.jpg'; // Substitua pelo caminho correto do banner

// Definição de seções com caminhos corretos
$sections = [
    "entradas" => [
        "title" => "Entradas",
        "url" => "sections/entradas.php"
    ], 
    "principais" => [
        "title" => "Pratos Principais",
        "url" => "sections/principais.php"
    ],
    "bebidas" => [
        "title" => "Bebidas",
        "url" => "sections/bebidas.php"
    ],
    "sobremesas" => [
        "title" => "Sobremesas",
        "url" => "sections/sobremesas.php"
    ],
];


