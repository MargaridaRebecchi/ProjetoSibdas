<?php
session_start();
require_once 'includes/auth.php';

if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'administrador') {
    header("Location: ../public/login.php");
    exit;
}

include 'includes/db.php';

/* Atualizar conteúdo público */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_conteudo'])) {

    $id_conteudo = (int) $_POST['id_conteudo'];
    $titulo = trim($_POST['titulo']);
    $texto = trim($_POST['texto']);

    $stmt = $conn->prepare("
        UPDATE conteudos_publicos
        SET titulo = ?, texto = ?
        WHERE id_conteudo = ?
    ");

    $stmt->bind_param("ssi", $titulo, $texto, $id_conteudo);
    $stmt->execute();

    header("Location: admin.php?conteudo_atualizado=1");
    exit;
}

$conteudos = $conn->query("
    SELECT *
    FROM conteudos_publicos
    ORDER BY id_conteudo ASC
");


include 'includes/header.php';
include 'includes/nav_admin.php';
?>

<main class="private-main">

    <section class="private-header">
        <div>
            <h1>Backoffice da Área Pública</h1>
            <p>Bem-vinda, <?= htmlspecialchars($_SESSION['nome']) ?>.</p>
        </div>
    </section>

    <section class="localizacoes-container">
        <div class="admin-publico-grid">

            <?php while ($c = $conteudos->fetch_assoc()): ?>

                <form method="POST" class="admin-publico-card">

                    <input type="hidden" name="atualizar_conteudo" value="1">
                    <input type="hidden" name="id_conteudo" value="<?= $c['id_conteudo'] ?>">

                    <h5><?= ucfirst(htmlspecialchars($c['chave'])) ?></h5>

                    <label>Título</label>
                    <input
                        type="text"
                        name="titulo"
                        class="form-control form-control-sm mb-3"
                        value="<?= htmlspecialchars($c['titulo']) ?>"
                        required>

                    <label>Texto</label>
                    <textarea
                        name="texto"
                        class="form-control form-control-sm mb-3"
                        rows="5"
                        required><?= htmlspecialchars($c['texto']) ?></textarea>

                    <button type="submit" class="btn-primario">
                        Guardar alterações
                    </button>

                </form>

            <?php endwhile; ?>

        </div>
    </section>

</main>
<div class="modal fade" id="modalSucessoConteudo" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Sucesso</h5>
            </div>

            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                <p class="mb-0">
                    Conteúdo atualizado com sucesso!
                </p>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button"
                    class="btn btn-success"
                    data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>