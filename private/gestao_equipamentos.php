<?php
include 'includes/db.php';

if (isset($_GET['apagar']) && is_numeric($_GET['apagar'])) {

    $id = (int) $_GET['apagar'];

    $sqlDelete = "DELETE FROM equipamentos WHERE id_equipamento = ?";
    $stmtDelete = $conn->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $id);

    if (!$stmtDelete->execute()) {
        die("Erro ao apagar: " . $stmtDelete->error);
    }

    header("Location: gestao_equipamentos.php?apagado=1");
    exit();
}

$sql = "SELECT id_equipamento, codigo_interno, designacao, categoria, marca, estado_atual, criticidade 
        FROM equipamentos
        ORDER BY codigo_interno ASC";

$result = $conn->query($sql);

include 'includes/header.php';
include 'includes/nav.php';
?>

<!DOCTYPE html>
<html lang="pt">



<body class="area-privada-body">

    <main class="private-main">

        <section class="private-header">

            <div>
                
                <h1>Gestão de Equipamentos</h1>
                
            </div>

            <a href="adicionar_equipamento.php" class="btn-primario text-decoration-none">
                <i class="fas fa-plus me-2"></i>
                Novo equipamento
</a>

        </section>

        <section class="private-card">

            <div class="table-responsive">

                <table class="table tabela-medgest align-middle">

                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Designação</th>
                            <th>Categoria</th>
                            <th>Marca</th>
                            <th>Estado</th>
                            <th>Criticidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

<?php if ($result && $result->num_rows > 0): ?>

    <?php while ($row = $result->fetch_assoc()): ?>

        <?php
            $estadoClasse = $row['estado_atual'];
            $estadoTexto = str_replace('_', ' ', $row['estado_atual']);

            $categoriaTexto = str_replace('_', ' ', $row['categoria']);
            $criticidadeTexto = str_replace('_', ' ', $row['criticidade']);
            if ($criticidadeTexto == 'media') {
    $criticidadeTexto = 'média';
}
        ?>

        <tr>
            <td><?= htmlspecialchars($row['codigo_interno']) ?></td>
            <td><?= htmlspecialchars($row['designacao']) ?></td>
            <td><?= ucfirst(htmlspecialchars($categoriaTexto)) ?></td>
            <td><?= htmlspecialchars($row['marca']) ?></td>

            <td>
                <span class="badge-medgest <?= htmlspecialchars($estadoClasse) ?>">
                    <?= ucfirst(htmlspecialchars($estadoTexto)) ?>
                </span>
            </td>

            <td>
                <?= ucfirst(htmlspecialchars($criticidadeTexto)) ?>
            </td>

            <td>
                <button class="btn-acao ver">
                    <i class="fas fa-eye"></i>
                </button>

                <button class="btn-acao editar">
                    <i class="fas fa-pen"></i>
                </button>

                <button 
    type="button"
    class="btn-acao apagar"
    data-bs-toggle="modal"
    data-bs-target="#modalApagarEquipamento"
    data-id="<?= $row['id_equipamento'] ?>"
    data-nome="<?= htmlspecialchars($row['designacao']) ?>"
>
    <i class="fas fa-trash"></i>
</button>
            </td>
        </tr>

    <?php endwhile; ?>

<?php else: ?>

    <tr>
        <td colspan="7" class="text-center text-muted">
            Não existem equipamentos registados.
        </td>
    </tr>

<?php endif; ?>

</tbody>

                </table>

            </div>

        </section>

    </main>
    

</body>
</html>
<div class="modal fade" id="modalApagarEquipamento" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Remover equipamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Tem a certeza que quer remover o equipamento
                <strong id="nomeEquipamentoApagar"></strong>?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <a href="#" id="confirmarApagarEquipamento" class="btn btn-danger">
                    Remover
                </a>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="modalSucesso" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Sucesso</h5>
            </div>

            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                <p class="mb-0">
                    Equipamento removido com sucesso!
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

<script>
const modalApagar = document.getElementById('modalApagarEquipamento');

modalApagar.addEventListener('show.bs.modal', function (event) {
    const botao = event.relatedTarget;
    const id = botao.getAttribute('data-id');
    const nome = botao.getAttribute('data-nome');

    document.getElementById('nomeEquipamentoApagar').textContent = nome;
    document.getElementById('confirmarApagarEquipamento').href =
        'gestao_equipamentos.php?apagar=' + id;
});
</script>
<?php if (isset($_GET['apagado'])): ?>

<script>
document.addEventListener('DOMContentLoaded', function() {

    var modal = new bootstrap.Modal(
        document.getElementById('modalSucesso')
    );

    modal.show();

});
</script>

<?php endif; ?>
<?php include 'includes/footer.php'; ?>