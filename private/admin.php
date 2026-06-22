<?php
session_start();

if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'administrador') {
    header("Location: ../public/login.php");
    exit;
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="private-main">

    <section class="private-header">
        <div>
            <h1>Backoffice Área Pública</h1>
            <p>Bem-vinda, <?= htmlspecialchars($_SESSION['nome']) ?>.</p>
        </div>
    </section>

    <section class="private-card">
        <h5>Área do Administrador</h5>
        <p>Aqui será feita a gestão da área pública do site.</p>
    </section>

</main>

<?php include 'includes/footer.php'; ?>