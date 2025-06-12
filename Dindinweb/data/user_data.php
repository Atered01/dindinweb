<?php
// Simulação de dados do usuário logado
$user = [
    'name' => 'João Sustentável',
    'member_since' => 'Jun/2022',
    'recycled_items' => 250,
    'markets' => 15,
    'consecutive_months' => 3,
    'level' => 'Reciclador Bronze',
    'progress_to_next_level' => 60,
    'balance' => 375.00,
    'processing_balance' => 45.00,
    'total_balance' => 1230.00
];

// Simulação de histórico de transações
$transactions = [
    [
        'type' => 'received',
        'description' => 'Recebido de Maria Silva',
        'date' => '15/06/2023 • 14:30',
        'amount' => 25.00,
        'icon' => 'fa-arrow-down',
        'color' => 'green'
    ],
    [
        'type' => 'sent',
        'description' => 'Enviado para Recicla Fácil',
        'date' => '10/06/2023 • 09:15',
        'amount' => 45.00,
        'icon' => 'fa-arrow-up',
        'color' => 'red'
    ],
    [
        'type' => 'received',
        'description' => 'Bônus de reciclagem',
        'date' => '05/06/2023 • 16:42',
        'amount' => 15.00,
        'icon' => 'fa-arrow-down',
        'color' => 'green'
    ]
];
?>