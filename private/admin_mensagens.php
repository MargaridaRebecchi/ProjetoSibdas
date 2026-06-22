<?php
session_start();
require_once 'includes/auth.php';

if (!isset($_SESSION['id_utilizador']) || $_SESSION['perfil'] !== 'administrador') {
    header("Location: ../public/login.php");
    exit;
}

include 'includes/db.php';

/* Marcar mensagem como lida/respondida */
if (isset($_GET['marcar']) && isset($_GET['id'])) {

    $id_mensagem = (int) $_GET['id'];
    $estado = $_GET['marcar'];

    if (in_array($estado, ['nova', 'lida', 'respondida'])) {

        $stmt = $conn->prepare("
            UPDATE mensagens_contacto
            SET estado = ?
            WHERE id_mensagem = ?
        ");

        $stmt->bind_param("si", $estado, $id_mensagem);
        $stmt->execute();
    }

    header("Location: admin_mensagens.php?mensagem_atualizada=1");
    exit;
}

/* Apagar mensagem */
if (isset($_GET['apagar']) && is_numeric($_GET['apagar'])) {

    $id_mensagem = (int) $_GET['apagar'];

    $stmt = $conn->prepare("
        DELETE FROM mensagens_contacto
        WHERE id_mensagem = ?
    ");

    $stmt->bind_param("i", $id_mensagem);
    $stmt->execute();

    header("Location: admin_mensagens.php?mensagem_apagada=1");
    exit;
}

$mensagens = $conn->query("
    SELECT *
    FROM mensagens_contacto
    ORDER BY data_envio DESC
");

$totalMensagens = $conn->query("
    SELECT COUNT(*) total
    FROM mensagens_contacto
")->fetch_assoc()['total'];

$totalNovas = $conn->query("
    SELECT COUNT(*) total
    FROM mensagens_contacto
    WHERE estado = 'nova'
")->fetch_assoc()['total'];

$totalLidas = $conn->query("
    SELECT COUNT(*) total
    FROM mensagens_contacto
    WHERE estado = 'lida'
")->fetch_assoc()['total'];

$totalRespondidas = $conn->query("
    SELECT COUNT(*) total
    FROM mensagens_contacto
    WHERE estado = 'respondida'
")->fetch_assoc()['total'];

include 'includes/header.php';
include 'includes/nav_admin.php';
?>

<main class="private-main">

    <section class="private-header">
        <div>
            <h1>Mensagens de Contacto</h1>
            <p>Mensagens enviadas através do formulário da página pública.</p>
        </div>
    </section>

    <section class="localizacoes-container">

        <div class="dashboard-cards">

            <div class="dashboard-card">
                <h6>Total Mensagens</h6>
                <span><?= $totalMensagens ?></span>
            </div>

            <div class="dashboard-card">
                <h6>Novas</h6>
                <span><?= $totalNovas ?></span>
            </div>

            <div class="dashboard-card">
                <h6>Lidas</h6>
                <span><?= $totalLidas ?></span>
            </div>


        </div>

        <div class="admin-mensagens-lista">

            <?php if ($mensagens && $mensagens->num_rows > 0): ?>

                <?php while ($m = $mensagens->fetch_assoc()): ?>

                    <div class="admin-mensagem-card">

                        <div class="admin-mensagem-topo">

                            <div class="admin-remetente">
                                <div class="admin-avatar">
                                    <?= strtoupper(substr($m['nome'], 0, 1)) ?>
                                </div>

                                <div>
                                    <strong><?= htmlspecialchars($m['nome']) ?></strong>
                                    <small><?= htmlspecialchars($m['email']) ?></small>
                                </div>
                            </div>

                            <span class="admin-estado estado-<?= $m['estado'] ?>">
                                <?= htmlspecialchars($m['estado']) ?>
                            </span>

                        </div>

                        <div class="admin-mensagem-info">

                            <span>
                                <i class="fas fa-building me-1"></i>
                                <?= htmlspecialchars($m['instituicao']) ?>
                            </span>

                            <?php if (!empty($m['telefone'])): ?>
                                <span>
                                    <i class="fas fa-phone me-1"></i>
                                    <?= htmlspecialchars($m['telefone']) ?>
                                </span>
                            <?php endif; ?>

                            <span>
                                <i class="fas fa-clock me-1"></i>
                                <?= htmlspecialchars($m['data_envio']) ?>
                            </span>

                        </div>

                        <div class="admin-assunto">
                            <strong>Assunto:</strong>
                            <?= htmlspecialchars($m['assunto']) ?>
                        </div>

                        <div class="admin-mensagem-texto">
                            <?= nl2br(htmlspecialchars($m['mensagem'])) ?>
                        </div>

                        <div class="admin-mensagem-acoes">

                            <?php if ($m['estado'] == 'nova'): ?>

                                <a href="admin_mensagens.php?marcar=lida&id=<?= $m['id_mensagem'] ?>"
                                    class="btn-admin-mensagem btn-admin-lida">
                                    Marcar como lida
                                </a>

                            <?php elseif ($m['estado'] == 'lida'): ?>

                                <a href="admin_mensagens.php?marcar=nova&id=<?= $m['id_mensagem'] ?>"
                                    class="btn-admin-mensagem btn-admin-lida">
                                    Marcar como não lida
                                </a>

                            <?php endif; ?>


                            <button type="button" class="btn-admin-mensagem btn-admin-apagar" data-bs-toggle="modal" data-bs-target="#modalApagarMensagem<?= $m['id_mensagem'] ?>">
                                Apagar
                            </button>

                        </div>
                        <div class="modal fade"
                            id="modalApagarMensagem<?= $m['id_mensagem'] ?>"
                            tabindex="-1">

                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Remover mensagem</h5>
                                    </div>

                                    <div class="modal-body">
                                        Tem a certeza que quer remover a mensagem de
                                        <strong><?= htmlspecialchars($m['nome']) ?></strong>?
                                    </div>

                                    <div class="modal-footer">

                                        <button type="button"
                                            class="btn btn-secondary"
                                            data-bs-dismiss="modal">
                                            Cancelar
                                        </button>

                                        <a href="admin_mensagens.php?apagar=<?= $m['id_mensagem'] ?>"
                                            class="btn btn-danger">
                                            Remover
                                        </a>

                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <p class="text-muted">Ainda não existem mensagens de contacto.</p>

            <?php endif; ?>

        </div>

    </section>

    <!-- Modal sucesso remoção mensagem -->
    <div class="modal fade" id="modalSucessoMensagem" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Sucesso</h5>
                </div>

                <div class="modal-body text-center">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                    <p class="mb-0">
                        Mensagem removida com sucesso!
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
</main>

<?php include 'includes/footer.php'; ?>