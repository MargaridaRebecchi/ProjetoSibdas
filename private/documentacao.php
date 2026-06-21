<?php
include 'includes/db.php';



/*Apagar documento */
if (isset($_GET['apagar']) && is_numeric($_GET['apagar'])) {

    $id_documento = (int) $_GET['apagar'];

    $stmt = $conn->prepare("
        DELETE FROM documentos_equipamento
        WHERE id_documento = ?
    ");

    $stmt->bind_param("i", $id_documento);
    $stmt->execute();

    header("Location: documentacao.php?apagado=1");
    exit;
}

/*Editar documento */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_documento'])) {

    $id_documento = (int) $_POST['id_documento'];
    $id_equipamento = (int) $_POST['id_equipamento'];
    $id_contrato = !empty($_POST['id_contrato']) ? (int) $_POST['id_contrato'] : null;
    $tipo_documento = $_POST['tipo_documento'];
    $nome_documento = trim($_POST['nome_documento']);
    $data_documento = $_POST['data_documento'];
    $data_validade = !empty($_POST['data_validade']) ? $_POST['data_validade'] : null;
    $entidade_responsavel = !empty($_POST['entidade_responsavel']) ? $_POST['entidade_responsavel'] : null;

    $stmt = $conn->prepare("
        UPDATE documentos_equipamento
        SET id_equipamento = ?,
            id_contrato = ?,
            tipo_documento = ?,
            nome_documento = ?,
            data_documento = ?,
            data_validade = ?
        WHERE id_documento = ?
    ");

    $stmt->bind_param(
        "iissssi",
        $id_equipamento,
        $id_contrato,
        $tipo_documento,
        $nome_documento,
        $data_documento,
        $data_validade,
        $id_documento
    );

    $stmt->execute();

    if ($tipo_documento === 'garantia' || $tipo_documento === 'contrato') {

        $tipoContrato =
            $tipo_documento === 'garantia'
            ? 'garantia'
            : 'contrato_manutencao';

        if ($id_contrato) {

            $stmtContrato = $conn->prepare("
            UPDATE contratos_garantias
            SET id_equipamento = ?,
                tipo = ?,
                entidade_responsavel = ?,
                data_inicio = ?,
                data_fim = ?
            WHERE id_contrato = ?
        ");

            $stmtContrato->bind_param(
                "issssi",
                $id_equipamento,
                $tipoContrato,
                $entidade_responsavel,
                $data_documento,
                $data_validade,
                $id_contrato
            );

            $stmtContrato->execute();
        } else {

            $stmtContrato = $conn->prepare("
            INSERT INTO contratos_garantias
            (id_equipamento, tipo, entidade_responsavel, data_inicio, data_fim, observacoes)
            VALUES (?, ?, ?, ?, ?, NULL)
        ");

            $stmtContrato->bind_param(
                "issss",
                $id_equipamento,
                $tipoContrato,
                $entidade_responsavel,
                $data_documento,
                $data_validade
            );

            $stmtContrato->execute();

            $novoContrato = $stmtContrato->insert_id;

            $stmtLigacao = $conn->prepare("
            UPDATE documentos_equipamento
            SET id_contrato = ?
            WHERE id_documento = ?
        ");

            $stmtLigacao->bind_param("ii", $novoContrato, $id_documento);
            $stmtLigacao->execute();
        }
    }

    header("Location: documentacao.php?editado=1");
    exit;
}


/*Barra de pesquisa e filtros*/
$pesquisa = $_GET['pesquisa'] ?? '';
$tipo = $_GET['tipo'] ?? '';

$where = "WHERE 1=1";

if ($pesquisa != '') {
    $p = $conn->real_escape_string(trim(mb_strtolower($pesquisa)));
    $where .= " AND (
        LOWER(d.nome_documento) LIKE '%$p%'
        OR LOWER(d.caminho_ficheiro) LIKE '%$p%'
        OR LOWER(e.designacao) LIKE '%$p%'
        OR LOWER(e.codigo_interno) LIKE '%$p%'
    )";
}

if ($tipo != '') {
    $tipoSQL = $conn->real_escape_string($tipo);
    $where .= " AND d.tipo_documento = '$tipoSQL'";
}

$sqlDocumentos = "
    SELECT 
        d.*,
        cg.entidade_responsavel,
        e.codigo_interno,
        e.designacao,
        e.marca,
        e.modelo
    FROM documentos_equipamento d
    INNER JOIN equipamentos e 
        ON d.id_equipamento = e.id_equipamento
    LEFT JOIN contratos_garantias cg
        ON d.id_contrato = cg.id_contrato
    $where
    ORDER BY e.codigo_interno ASC, d.data_upload DESC
";

$resultDocumentos = $conn->query($sqlDocumentos);

$equipamentosEditar = $conn->query("
    SELECT id_equipamento, codigo_interno, designacao
    FROM equipamentos
    ORDER BY codigo_interno
");
$fornecedoresEditar = $conn->query("
    SELECT id_fornecedor, nome_empresa
    FROM fornecedores
    ORDER BY nome_empresa
");

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="private-main">
    <section class="private-header">
        <div>
            <h1>Documentação Técnica</h1>
        </div>


        <a href="adicionar_documento.php" class="btn-primario text-decoration-none">
            <i class="fas fa-plus me-2"></i>
            Novo documento
        </a>
        </div>

        <form method="GET" class="pesquisa-documentacao-form">


            <input
                type="text"
                name="pesquisa"
                class="form-control form-control-sm"
                style="flex:1;"
                placeholder="Pesquisar documento, equipamento ou ficheiro..."
                value="<?= htmlspecialchars($pesquisa) ?>">

            <select name="tipo" class="form-select form-select-sm select-documentacao-tipo">

                <option value="">Todos os tipos</option>
                <option value="manual" <?= $tipo == 'manual' ? 'selected' : '' ?>>Manual</option>
                <option value="certificado" <?= $tipo == 'certificado' ? 'selected' : '' ?>>Certificado</option>
                <option value="contrato" <?= $tipo == 'contrato' ? 'selected' : '' ?>>Contrato</option>
                <option value="garantia" <?= $tipo == 'garantia' ? 'selected' : '' ?>>Garantia</option>
                <option value="relatorio" <?= $tipo == 'relatorio' ? 'selected' : '' ?>>Relatório</option>
                <option value="outro" <?= $tipo == 'outro' ? 'selected' : '' ?>>Outro</option>

            </select>

            <button type="submit" class="btn btn-sm btn-pesquisar-documentacao">
                Pesquisar
            </button>

            <a href="documentacao.php" class="btn btn-sm btn-outline-secondary">
                Limpar
            </a>

        </form>

        <section class="documentacao-container">
            <div class="accordion documentacao-accordion" id="accordionDocumentos">

                <?php if ($resultDocumentos && $resultDocumentos->num_rows > 0): ?>

                    <?php
                    $equipamentoAtual = null;
                    $contador = 0;
                    ?>

                    <?php while ($doc = $resultDocumentos->fetch_assoc()): ?>

                        <?php if ($equipamentoAtual !== $doc['id_equipamento']): ?>

                            <?php if ($equipamentoAtual !== null): ?>
            </div>
            </div>
            </div>
        <?php endif; ?>

        <?php
                            $equipamentoAtual = $doc['id_equipamento'];
                            $contador++;
        ?>

        <div class="accordion-item mb-3">
            <h2 class="accordion-header" id="heading<?= $contador ?>">
                <button class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapse<?= $contador ?>">

                    <?= htmlspecialchars($doc['codigo_interno']) ?> -
                    <?= htmlspecialchars($doc['designacao']) ?>
                    <?= htmlspecialchars($doc['marca']) ?>
                    <?= htmlspecialchars($doc['modelo']) ?>

                </button>
            </h2>

            <div id="collapse<?= $contador ?>"
                class="accordion-collapse collapse"
                data-bs-parent="#accordionDocumentos">

                <div class="accordion-body">
                <?php endif; ?>

                <div class="documento-item">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start mb-2">

                            <span class="badge badge-documento text-uppercase">
                                <?= htmlspecialchars($doc['tipo_documento']) ?>
                            </span>

                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item"
                                            href="<?= htmlspecialchars($doc['caminho_ficheiro']) ?>"
                                            target="_blank">
                                            <i class="fa-solid fa-eye me-2"></i>Ver ficheiro
                                        </a>
                                    </li>

                                    <li>
                                        <button type="button"
                                            class="dropdown-item btn-editar-documento"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarDocumento"
                                            data-id="<?= $doc['id_documento'] ?>"
                                            data-equipamento="<?= $doc['id_equipamento'] ?>"
                                            data-contrato="<?= $doc['id_contrato'] ?>"
                                            data-entidade="<?= htmlspecialchars($doc['entidade_responsavel'] ?? '', ENT_QUOTES) ?>"
                                            data-tipo="<?= $doc['tipo_documento'] ?>"
                                            data-nome="<?= htmlspecialchars($doc['nome_documento'], ENT_QUOTES) ?>"
                                            data-data="<?= $doc['data_documento'] ?>"
                                            data-validade="<?= $doc['data_validade'] ?>">
                                            <i class="fa-solid fa-pen me-2"></i>Editar
                                        </button>
                                    </li>

                                    <li>
                                        <button type="button"
                                            class="dropdown-item text-danger btn-apagar-documento"
                                            data-id="<?= $doc['id_documento'] ?>"
                                            data-nome="<?= htmlspecialchars($doc['nome_documento']) ?>">
                                            <i class="fa-solid fa-trash me-2"></i>Eliminar
                                        </button>
                                    </li>
                                </ul>
                            </div>

                        </div>

                        <h5 class="fw-bold mb-2">
                            <?= htmlspecialchars($doc['nome_documento']) ?>
                        </h5>

                        <p class="small mb-1">
                            <strong>Data do documento:</strong>
                            <?= htmlspecialchars($doc['data_documento']) ?>
                        </p>

                        <p class="small mb-0">
                            <strong>Validade:</strong>
                            <?= $doc['data_validade'] ? htmlspecialchars($doc['data_validade']) : 'Não aplicável' ?>
                        </p>

                    </div>
                </div>

            <?php endwhile; ?>

                </div>
            </div>
        </div>

    <?php else: ?>

        <div class="text-center text-muted py-4">
            Ainda não existem documentos registados.
        </div>

    <?php endif; ?>

    </div>
        </section>

        <!-- Modal editar documento -->
        <div class="modal fade" id="modalEditarDocumento" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">

                    <form method="POST">

                        <div class="modal-header">
                            <h5 class="modal-title">Editar documento</h5>
                        </div>

                        <div class="modal-body">

                            <input type="hidden" name="editar_documento" value="1">
                            <input type="hidden" name="id_documento" id="edit_id_documento">
                            <input type="hidden" name="id_contrato" id="edit_id_contrato">

                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label>Equipamento</label>
                                    <select name="id_equipamento" id="edit_id_equipamento" class="form-select form-select-sm" required>
                                        <?php while ($eq = $equipamentosEditar->fetch_assoc()): ?>
                                            <option value="<?= $eq['id_equipamento'] ?>">
                                                <?= htmlspecialchars($eq['codigo_interno'] . ' - ' . $eq['designacao']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Tipo de documento</label>
                                    <select name="tipo_documento" id="edit_tipo_documento" class="form-select form-select-sm" required>
                                        <option value="manual">Manual</option>
                                        <option value="certificado">Certificado</option>
                                        <option value="contrato">Contrato</option>
                                        <option value="garantia">Garantia</option>
                                        <option value="relatorio">Relatório</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Nome do documento</label>
                                    <input type="text" name="nome_documento" id="edit_nome_documento" class="form-control form-control-sm" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Data do documento</label>
                                    <input type="date" name="data_documento" id="edit_data_documento" class="form-control form-control-sm" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label>Data de validade</label>
                                    <input type="date" name="data_validade" id="edit_data_validade" class="form-control form-control-sm">
                                </div>

                                <div class="col-md-6 mb-3" id="edit_campo_entidade_responsavel" style="display:none;">
                                    <label>Entidade responsável</label>

                                    <select name="entidade_responsavel" id="edit_entidade_responsavel" class="form-select form-select-sm">
                                        <option value="">Selecionar fornecedor</option>

                                        <?php while ($f = $fornecedoresEditar->fetch_assoc()): ?>
                                            <option value="<?= htmlspecialchars($f['nome_empresa']) ?>">
                                                <?= htmlspecialchars($f['nome_empresa']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>

                                    <small class="text-muted">
                                        Apenas aplicável a contratos e garantias.
                                    </small>
                                </div>

                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>

                            <button type="submit" class="btn-primario">
                                Guardar alterações
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>


        <!--Modal para apagar documento -->
        <div class="modal fade" id="modalApagarDocumento" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Remover documento</h5>
                    </div>

                    <div class="modal-body">
                        Tem a certeza que quer remover o documento
                        <strong id="nomeDocumentoApagar"></strong>?
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <a href="#"
                            id="confirmarApagarDocumento"
                            class="btn btn-danger">
                            Remover
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <!--Modal sucesso de remoção documento -->
        <div class="modal fade" id="modalSucessoDocumento" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Sucesso</h5>
                    </div>

                    <div class="modal-body text-center">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                        <p class="mb-0">
                            Documento removido com sucesso!
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

        <!-- Modal sucesso edição documento -->
        <div class="modal fade" id="modalEditadoDocumento" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Sucesso</h5>
                    </div>

                    <div class="modal-body text-center">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                        <p class="mb-0">
                            Documento editado com sucesso!
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