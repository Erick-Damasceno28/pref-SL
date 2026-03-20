<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return !empty($_SESSION['usuario_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function currentUser(): array {
    return [
        'id'   => $_SESSION['usuario_id']   ?? 0,
        'nome' => $_SESSION['usuario_nome'] ?? '',
    ];
}
