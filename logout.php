<?php
session_start(); // Localiza a sessão atual
session_unset(); // Limpa todas as variáveis da sessão
session_destroy(); // Destrói a sessão completamente

// Redireciona o usuário de volta para a tela de login
header("Location: login.php");
exit;
?>