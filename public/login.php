<?php
session_start();

require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../private/includes/db.php';

$erro = '';

if (isset($_GET['erro'])) {
    $erro = $_GET['erro'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (strlen($password) < 6) {
        header("Location: login.php?erro=" . urlencode("A palavra-passe deve ter pelo menos 6 caracteres."));
        exit;
    }

    $stmt = $conn->prepare("
        SELECT id_utilizador, nome, email, password, perfil
        FROM utilizadores
        WHERE email = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {

        $user = $resultado->fetch_assoc();

        if ($password === $user['password']) {

            $_SESSION['id_utilizador'] = $user['id_utilizador'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['perfil'] = $user['perfil'];

            if ($user['perfil'] === 'administrador') {
                header("Location: ../private/admin.php");
                exit;
            }

            if ($user['perfil'] === 'gestor') {
                header("Location: ../private/gestao_equipamentos.php");
                exit;
            }

            header("Location: login.php?erro=" . urlencode("Este perfil não tem acesso autorizado."));
            exit;
        }

        header("Location: login.php?erro=" . urlencode("Email ou palavra-passe incorretos."));
        exit;
    }

    header("Location: login.php?erro=" . urlencode("Email ou palavra-passe incorretos."));
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>MedGest</title>

    <link rel="icon" href="../assets/images/logo.png" type="image/png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/1230824.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="login-page">

    <div class="login-page-card">

        <h2 class="login-logo">

            <img src="../assets/images/logo.png" alt="Logo MedGest" class="login-logo-img">
            Med<span>Gest</span>
        </h2>

        <p class="login-subtitulo">
            Aceda à área reservada
        </p>

        <?php if ($erro): ?>
            <div class="alert alert-danger py-2">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>


        <form method="POST" autocomplete="off">

            <label>Email</label>
            <div class="login-input">
                <i class="fas fa-user"></i>
                <input type="email" id="email" name="email" placeholder="Insira o email" autocomplete="off" required>
            </div>

            <label>Palavra-passe</label>
            <div class="login-input">
                <i class="fas fa-key"></i>
                <input type="password" id="password" name="password" placeholder="Insira a palavra-passe" autocomplete="new-password" minlength="6" required>
            </div>

            <!--Botões para preencher login -->
            <div class="login-demo-buttons">

                <button type="button" class="btn-login-demo" onclick="preencherAdmin()">
                    Administrador Teste
                </button>

                <button type="button" class="btn-login-demo" onclick="preencherGestor()">
                    Gestor Teste
                </button>

            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-right-to-bracket me-2"></i>
                Entrar
            </button>

            <a href="index.php" class="btn-voltar-login">
                Voltar à página inicial
            </a>
        </form>

    </div>
    <script src="../assets/js/1230824.js"></script>
    <script>
        if (window.location.search.includes('erro=')) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
</body>


</html>