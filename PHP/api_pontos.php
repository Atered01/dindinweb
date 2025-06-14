<?php
// Em Dindinweb/api/api_pontos.php

// Define o cabeçalho da resposta como JSON, a linguagem das APIs
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Permite que qualquer domínio acesse esta API

// Nosso "banco de dados" de pontos de coleta em formato de array PHP
$pontosDeColeta = [
    [
        "nome" => "Supermercado Verde Faccini",
        "endereco" => "Av. Paulo Faccini, 1500, Macedo, Guarulhos, SP"
    ],
    [
        "nome" => "Parque Shopping Maia Ponto Verde",
        "endereco" => "Av. Bartolomeu de Carlos, 230, Jardim Flor da Montanha, Guarulhos, SP"
    ],
    [
        "nome" => "Bosque Maia Coleta Seletiva",
        "endereco" => "Av. Papa João XXIII, 219, Parque Renato Maia, Guarulhos, SP"
    ],
    [
        "nome" => "Carrefour Vila Rio",
        "endereco" => "Av. Benjamin Harris Hunicutt, 1999, Vila Rio de Janeiro, Guarulhos, SP"
    ],
    [
        "nome" => "Hipermercado Extra - Pimentas",
        "endereco" => "Estr. Juscelino Kubtischek de Oliveira, 5308, Jardim Albertina, Guarulhos, SP"
    ],
    [
        "nome" => "Atacadão Guarulhos",
        "endereco" => "Av. Monteiro Lobato, 1137, Macedo, Guarulhos, SP"
    ]
];

// Converte o array PHP para o formato JSON e o envia como resposta
echo json_encode($pontosDeColeta);
?>