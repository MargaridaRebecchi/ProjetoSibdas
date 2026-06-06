<?php
include 'includes/db.php';

$limite = 10;

$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina'])
    ? (int) $_GET['pagina']
    : 1;

if ($pagina < 1) {
    $pagina = 1;
}

$offset = ($pagina - 1) * $limite;

$sqlTotal = "SELECT COUNT(*) AS total FROM equipamentos";
$resultTotal = $conn->query($sqlTotal);
$totalEquipamentos = $resultTotal->fetch_assoc()['total'];

$totalPaginas = ceil($totalEquipamentos / $limite);

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
// EDITAR EQUIPAMENTO// 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_equipamento'])) {

    $id = (int) $_POST['id_equipamento'];
    $id_localizacao = (int) $_POST['id_localizacao'];

    $sqlUpdateLoc = "UPDATE localizacoes_
                     SET hospital = ?, edificio = ?, piso = ?, sala = ?
                     WHERE id_localizacao = ?";

    $stmtLoc = $conn->prepare($sqlUpdateLoc);
    $stmtLoc->bind_param(
        "ssisi",
        $_POST['hospital'],
        $_POST['edificio'],
        $_POST['piso'],
        $_POST['sala'],
        $id_localizacao
    );
    $stmtLoc->execute();

    $sqlUpdateEq = "UPDATE equipamentos
                    SET designacao = ?, categoria = ?, marca = ?, modelo = ?, fabricante = ?,
                        data_aquisicao = ?, ano_fabrico = ?, custo_aquisicao = ?,
                        tipo_entrada = ?, estado_atual = ?, criticidade = ?
                    WHERE id_equipamento = ?";

    $stmtEq = $conn->prepare($sqlUpdateEq);
    $stmtEq->bind_param(
        "ssssssidsssi",
        $_POST['designacao'],
        $_POST['categoria'],
        $_POST['marca'],
        $_POST['modelo'],
        $_POST['fabricante'],
        $_POST['data_aquisicao'],
        $_POST['ano_fabrico'],
        $_POST['custo_aquisicao'],
        $_POST['tipo_entrada'],
        $_POST['estado_atual'],
        $_POST['criticidade'],
        $id
    );

    $stmtEq->execute();

    header("Location: gestao_equipamentos.php?editado=1");
    exit();
}

$sql = "SELECT e.*, l.hospital, l.edificio, l.piso, l.sala
        FROM equipamentos e
        LEFT JOIN localizacoes_ l ON e.id_localizacao = l.id_localizacao
        ORDER BY e.codigo_interno ASC
        LIMIT $limite OFFSET $offset";

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
            if ($estadoTexto == 'em manutencao') {
            $estadoTexto = 'em manutenção';
            }

        $categoriaTexto = str_replace('_', ' ', $row['categoria']);
        switch ($categoriaTexto) {
    case 'monitorizacao':
        $categoriaTexto = 'monitorização';
        break;

    case 'suporte vida':
        $categoriaTexto = 'suporte de vida';
        break;

    case 'diagnostico':
        $categoriaTexto = 'diagnóstico';
        break;

    case 'laboratorio':
        $categoriaTexto = 'laboratório';
        break;

    case 'reabilitacao':
        $categoriaTexto = 'reabilitação';
        break;

    case 'esterilizacao':
        $categoriaTexto = 'esterilização';
        break;
}
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




<!-- BOTÃO VER EQUIPAMENTO -->
            <td>
                <button 
    type="button"
    class="btn-acao ver"
    data-bs-toggle="modal"
    data-bs-target="#modalVerEquipamento"

    data-codigo="<?= htmlspecialchars($row['codigo_interno']) ?>"
    data-designacao="<?= htmlspecialchars($row['designacao']) ?>"
    data-categoria="<?= htmlspecialchars($categoriaTexto) ?>"
    data-marca="<?= htmlspecialchars($row['marca']) ?>"
    data-modelo="<?= htmlspecialchars($row['modelo']) ?>"
    data-serie="<?= htmlspecialchars($row['numero_serie']) ?>"
    data-fabricante="<?= htmlspecialchars($row['fabricante']) ?>"

    data-hospital="<?= htmlspecialchars($row['hospital']) ?>"
    data-edificio="<?= htmlspecialchars($row['edificio']) ?>"
    data-piso="<?= htmlspecialchars($row['piso']) ?>"
    data-sala="<?= htmlspecialchars($row['sala']) ?>"

    data-data-aquisicao="<?= htmlspecialchars($row['data_aquisicao']) ?>"
    data-ano-fabrico="<?= htmlspecialchars($row['ano_fabrico']) ?>"
    data-custo="<?= htmlspecialchars($row['custo_aquisicao']) ?>"
    data-tipo-entrada="<?= htmlspecialchars($row['tipo_entrada']) ?>"

    data-estado="<?= htmlspecialchars($estadoTexto) ?>"
    data-criticidade="<?= htmlspecialchars($criticidadeTexto) ?>"
    data-registo="<?= htmlspecialchars($row['data_registo']) ?>"
>
    <i class="fas fa-eye"></i>
</button>


<!-- BOTÃO EDITAR EQUIPAMENTO -->
                <button 
    type="button"
    class="btn-acao editar"
    data-bs-toggle="modal"
    data-bs-target="#modalEditarEquipamento"

    data-id="<?= $row['id_equipamento'] ?>"
    data-id-localizacao="<?= $row['id_localizacao'] ?>"
    data-codigo="<?= htmlspecialchars($row['codigo_interno']) ?>"
    data-serie="<?= htmlspecialchars($row['numero_serie']) ?>"
    data-designacao="<?= htmlspecialchars($row['designacao']) ?>"
    data-categoria="<?= htmlspecialchars($row['categoria']) ?>"
    data-marca="<?= htmlspecialchars($row['marca']) ?>"
    data-modelo="<?= htmlspecialchars($row['modelo']) ?>"
    data-fabricante="<?= htmlspecialchars($row['fabricante']) ?>"
    data-hospital="<?= htmlspecialchars($row['hospital']) ?>"
    data-edificio="<?= htmlspecialchars($row['edificio']) ?>"
    data-piso="<?= htmlspecialchars($row['piso']) ?>"
    data-sala="<?= htmlspecialchars($row['sala']) ?>"
    data-data-aquisicao="<?= htmlspecialchars($row['data_aquisicao']) ?>"
    data-ano-fabrico="<?= htmlspecialchars($row['ano_fabrico']) ?>"
    data-custo="<?= htmlspecialchars($row['custo_aquisicao']) ?>"
    data-tipo-entrada="<?= htmlspecialchars($row['tipo_entrada']) ?>"
    data-estado="<?= htmlspecialchars($row['estado_atual']) ?>"
    data-criticidade="<?= htmlspecialchars($row['criticidade']) ?>"
>
    <i class="fas fa-pen"></i>
</button>


<!--BOTÃO APAGAR EQUIPAMENTO -->
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
                <?php if ($totalPaginas > 1): ?>
    <div class="d-flex justify-content-center align-items-center gap-3 mt-3">

        <?php if ($pagina > 1): ?>
            <a href="gestao_equipamentos.php?pagina=<?= $pagina - 1 ?>"
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-chevron-left"></i>
            </a>
        <?php endif; ?>

        <span class="text-muted">
            Página <?= $pagina ?> de <?= $totalPaginas ?>
        </span>

        <?php if ($pagina < $totalPaginas): ?>
            <a href="gestao_equipamentos.php?pagina=<?= $pagina + 1 ?>"
               class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>

    </div>
<?php endif; ?>

            </div>

        </section>

    </main>
    

</body>
</html>

<!--MODAL APAGAR EQUIPAMENTO -->
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

<!--MODAL SUCESSO ADIÇÃO DE EQUIPAMENTO -->
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

<!--MODAL PARA EDITAR O EQUIPAMENTO -->
<div class="modal fade" id="modalEditarEquipamento" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            <form method="POST">

                <input type="hidden" name="editar_equipamento" value="1">
                <input type="hidden" name="id_equipamento" id="edit_id_equipamento">
                <input type="hidden" name="id_localizacao" id="edit_id_localizacao">

                <div class="modal-header justify-content-center">
                    <h5 class="modal-title text-center">Editar equipamento</h5>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Código interno</label>
                            <input type="text" id="edit_codigo" class="form-control form-control-sm" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Número de série</label>
                            <input type="text" id="edit_serie" class="form-control form-control-sm" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Designação</label>
                            <input type="text" name="designacao" id="edit_designacao" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Categoria</label>
                            <select name="categoria" id="edit_categoria" class="form-select form-select-sm" required>
                                <option value="monitorizacao">Monitorização</option>
                                <option value="suporte_vida">Suporte de vida</option>
                                <option value="terapia">Terapia</option>
                                <option value="diagnostico">Diagnóstico</option>
                                <option value="laboratorio">Laboratório</option>
                                <option value="esterilizacao">Esterilização</option>
                                <option value="reabilitacao">Reabilitação</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Marca</label>
                            <input type="text" name="marca" id="edit_marca" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Modelo</label>
                            <input type="text" name="modelo" id="edit_modelo" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Fabricante</label>
                            <input type="text" name="fabricante" id="edit_fabricante" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Hospital</label>
                            <input type="text" name="hospital" id="edit_hospital" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Edifício</label>
                            <input type="text" name="edificio" id="edit_edificio" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Piso</label>
                            <input type="number" name="piso" id="edit_piso" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Sala</label>
                            <input type="text" name="sala" id="edit_sala" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Data de aquisição</label>
                            <input type="date" name="data_aquisicao" id="edit_data_aquisicao" class="form-control form-control-sm" max="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Ano de fabrico</label>
                            <input type="text" name="ano_fabrico" id="edit_ano_fabrico" class="form-control form-control-sm" pattern="[0-9]{4}" maxlength="4"  min="1900"
       max="2026"required>
       <div class="invalid-feedback">
    O ano de fabrico não pode ser superior a 2026.
</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Custo de aquisição</label>
                            <input type="number" step="0.01" name="custo_aquisicao" id="edit_custo" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Tipo de entrada</label>
                            <select name="tipo_entrada" id="edit_tipo_entrada" class="form-select form-select-sm" required>
                                <option value="compra">Compra</option>
                                <option value="doacao">Doação</option>
                                <option value="aluguer">Aluguer</option>
                                <option value="emprestimo">Empréstimo</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Estado atual</label>
                            <select name="estado_atual" id="edit_estado" class="form-select form-select-sm" required>
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                                <option value="em_manutencao">Em manutenção</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Criticidade</label>
                            <select name="criticidade" id="edit_criticidade" class="form-select form-select-sm" required>
                                <option value="baixa">Baixa</option>
                                <option value="media">Média</option>
                                <option value="alta">Alta</option>
                                <option value="suporte_vida">Suporte de vida</option>
                            </select>
                        </div>

                    </div>

                </div>

                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-success">
                        Guardar alterações
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    
                </div>

            </form>

        </div>
    </div>
</div>

<!-- MODAL DE SUCESSO DA EDIÇÃO DO EQUIPAMENTO -->
<div class="modal fade" id="modalEditadoSucesso" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header justify-content-center">
                <h5 class="modal-title text-success text-center">
                    Equipamento atualizado
                </h5>
            </div>

            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                <p class="mb-0">
                    Equipamento atualizado com sucesso!
                </p>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>


<!-- MODAL VER EQUIPAMENTO - FICHA TÉCNICA -->
<div class="modal fade" id="modalVerEquipamento" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content ficha-equipamento">

            <div class="modal-header justify-content-center">
                <div class="text-center">
                    <h5 class="modal-title">Ficha Técnica do Equipamento</h5>
                    <small class="text-muted" id="ver_subtitulo"></small>
                </div>
            </div>

            <div class="modal-body">

                <h6 class="ficha-secao ficha-identificacao">Identificação</h6>

                <div class="ficha-grid">
                    <div><strong>Código interno</strong><span id="ver_codigo"></span></div>
                    <div><strong>Designação</strong><span id="ver_designacao"></span></div>
                    <div><strong>Categoria</strong><span id="ver_categoria"></span></div>
                    <div><strong>Marca</strong><span id="ver_marca"></span></div>
                    <div><strong>Modelo</strong><span id="ver_modelo"></span></div>
                    <div><strong>Número de série</strong><span id="ver_serie"></span></div>
                    <div><strong>Fabricante</strong><span id="ver_fabricante"></span></div>
                </div>

                <h6 class="ficha-secao ficha-localizacao">Localização</h6>

                <div class="ficha-grid">
                    <div><strong>Hospital</strong><span id="ver_hospital"></span></div>
                    <div><strong>Edifício</strong><span id="ver_edificio"></span></div>
                    <div><strong>Piso</strong><span id="ver_piso"></span></div>
                    <div><strong>Sala</strong><span id="ver_sala"></span></div>
                </div>

                <h6 class="ficha-secao ficha-aquisicao">Aquisição</h6>

                <div class="ficha-grid">
                    <div><strong>Data de aquisição</strong><span id="ver_data_aquisicao"></span></div>
                    <div><strong>Ano de fabrico</strong><span id="ver_ano_fabrico"></span></div>
                    <div><strong>Custo de aquisição</strong><span id="ver_custo"></span></div>
                    <div><strong>Tipo de entrada</strong><span id="ver_tipo_entrada"></span></div>
                </div>

                <h6 class="ficha-secao ficha-estado">Estado</h6>

                <div class="ficha-grid">
                    <div><strong>Estado atual</strong><span id="ver_estado"></span></div>
                    <div><strong>Criticidade</strong><span id="ver_criticidade"></span></div>
                    <div><strong>Data de registo</strong><span id="ver_data_registo"></span></div>
                </div>

            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>


<?php if (isset($_GET['editado'])): ?>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = new bootstrap.Modal(
        document.getElementById('modalEditadoSucesso')
    );

    modal.show();

    // Remove ?editado=1 do URL
    window.history.replaceState(
        {},
        document.title,
        'gestao_equipamentos.php'
    );

});
</script>

<?php endif; ?>

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

    window.history.replaceState(
        {},
        document.title,
        'gestao_equipamentos.php'
    );

});
</script>

<?php endif; ?>


<script>
const modalEditar = document.getElementById('modalEditarEquipamento');

modalEditar.addEventListener('show.bs.modal', function (event) {
    const botao = event.relatedTarget;

    document.getElementById('edit_id_equipamento').value = botao.getAttribute('data-id');
    document.getElementById('edit_id_localizacao').value = botao.getAttribute('data-id-localizacao');

    document.getElementById('edit_codigo').value = botao.getAttribute('data-codigo');
    document.getElementById('edit_serie').value = botao.getAttribute('data-serie');
    document.getElementById('edit_designacao').value = botao.getAttribute('data-designacao');
    document.getElementById('edit_categoria').value = botao.getAttribute('data-categoria');
    document.getElementById('edit_marca').value = botao.getAttribute('data-marca');
    document.getElementById('edit_modelo').value = botao.getAttribute('data-modelo');
    document.getElementById('edit_fabricante').value = botao.getAttribute('data-fabricante');

    document.getElementById('edit_hospital').value = botao.getAttribute('data-hospital');
    document.getElementById('edit_edificio').value = botao.getAttribute('data-edificio');
    document.getElementById('edit_piso').value = botao.getAttribute('data-piso');
    document.getElementById('edit_sala').value = botao.getAttribute('data-sala');

    document.getElementById('edit_data_aquisicao').value = botao.getAttribute('data-data-aquisicao');
    document.getElementById('edit_ano_fabrico').value = botao.getAttribute('data-ano-fabrico');
    document.getElementById('edit_custo').value = botao.getAttribute('data-custo');

    document.getElementById('edit_tipo_entrada').value = botao.getAttribute('data-tipo-entrada');
    document.getElementById('edit_estado').value = botao.getAttribute('data-estado');
    document.getElementById('edit_criticidade').value = botao.getAttribute('data-criticidade');
});
const editAno = document.getElementById('edit_ano_fabrico');

editAno.addEventListener('blur', function () {

    const ano = parseInt(this.value);

    if (
        !/^[0-9]{4}$/.test(this.value) ||
        ano > 2026
    ) {
        this.setCustomValidity('erro');
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
    } else {
        this.setCustomValidity('');
        this.classList.add('is-valid');
        this.classList.remove('is-invalid');
    }

});
</script>

<script>
const modalVer = document.getElementById('modalVerEquipamento');

modalVer.addEventListener('show.bs.modal', function (event) {
    const botao = event.relatedTarget;

    function texto(id, valor) {
        document.getElementById(id).textContent = valor || '—';
    }

    const codigo = botao.getAttribute('data-codigo');
    const designacao = botao.getAttribute('data-designacao');

    texto('ver_subtitulo', designacao + ' · Código ' + codigo);

    texto('ver_codigo', codigo);
    texto('ver_designacao', designacao);
    texto('ver_categoria', capitalizar(botao.getAttribute('data-categoria')));
    texto('ver_marca', botao.getAttribute('data-marca'));
    texto('ver_modelo', botao.getAttribute('data-modelo'));
    texto('ver_serie', botao.getAttribute('data-serie'));
    texto('ver_fabricante', botao.getAttribute('data-fabricante'));

    texto('ver_hospital', botao.getAttribute('data-hospital'));
    texto('ver_edificio', botao.getAttribute('data-edificio'));
    texto('ver_piso', botao.getAttribute('data-piso'));
    texto('ver_sala', botao.getAttribute('data-sala'));

    texto('ver_data_aquisicao', formatarData(botao.getAttribute('data-data-aquisicao')));
    texto('ver_ano_fabrico', botao.getAttribute('data-ano-fabrico'));
    texto('ver_custo', formatarEuro(botao.getAttribute('data-custo')));
    texto('ver_tipo_entrada', formatarTexto(botao.getAttribute('data-tipo-entrada')));

    texto('ver_estado', capitalizar(botao.getAttribute('data-estado')));
    texto('ver_criticidade', capitalizar(botao.getAttribute('data-criticidade')));
    texto('ver_data_registo', formatarDataHora(botao.getAttribute('data-registo')));
});

function capitalizar(texto) {
    if (!texto) return '—';
    return texto.charAt(0).toUpperCase() + texto.slice(1);
}

function formatarTexto(texto) {
    if (!texto) return '—';

    texto = texto.replaceAll('_', ' ');

    if (texto === 'doacao') texto = 'doação';
    if (texto === 'emprestimo') texto = 'empréstimo';

    return capitalizar(texto);
}

function formatarData(data) {
    if (!data) return '—';

    const partes = data.split('-');

    if (partes.length !== 3) return data;

    return partes[2] + '/' + partes[1] + '/' + partes[0];
}

function formatarDataHora(dataHora) {
    if (!dataHora) return '—';

    const partes = dataHora.split(' ');
    const data = formatarData(partes[0]);

    if (!partes[1]) return data;

    return data + ' ' + partes[1].substring(0, 5);
}

function formatarEuro(valor) {
    if (!valor) return '—';

    return Number(valor).toLocaleString('pt-PT', {
        style: 'currency',
        currency: 'EUR'
    });
}
</script>

<?php include 'includes/footer.php'; ?>