<?php
return [
    'db' => [
        'host' => 'localhost', // O l'IP del database fornito da OVH
        'port' => 3306,
        'name' => 'nome_database_ovh', // Inserisci il nome del DB creato su OVH
        'user' => 'utente_database_ovh', // Inserisci l'utente del DB
        'pass' => 'password_database_ovh', // Inserisci la password
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => '/', // Se il sito è nella root del dominio
        'server_port' => 80, // Solitamente 80 o 443 su produzione
        'upload_dir' => __DIR__ . '/../public/uploads',
    ]
];
