<?php include 'includes/header.php'; ?> 
<?php include 'includes/nav.php'; ?> 
<?php
include 'includes/db.php';

$sql = "SELECT codigo_interno, designacao, categoria, marca, estado_atual, criticidade 
        FROM equipamentos
        ORDER BY codigo_interno ASC";
$result = $conn->query($sql);
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

                <button class="btn-acao apagar">
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

<?php include 'includes/footer.php'; ?>