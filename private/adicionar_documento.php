<?php
include 'includes/db.php';
include 'includes/header.php';
include 'includes/nav.php';

$equipamentos = $conn->query("SELECT id_equipamento, codigo_interno, designacao FROM equipamentos ORDER BY codigo_interno");

$fornecedores = $conn->query("
    SELECT id_fornecedor, nome_empresa
    FROM fornecedores
    ORDER BY nome_empresa
");

function normalizarTexto($texto)
{
    $texto = strtolower($texto);
    $texto = str_replace(['_', '-', '.pdf'], ' ', $texto);
    return trim($texto);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_equipamento = $_POST['id_equipamento'];
    $id_contrato = !empty($_POST['id_contrato']) ? $_POST['id_contrato'] : null;
    $tipo_documento = $_POST['tipo_documento'];
    $nome_documento = trim($_POST['nome_documento']);
    $data_documento = $_POST['data_documento'];
    $data_validade = !empty($_POST['data_validade']) ? $_POST['data_validade'] : null;
    $entidade_responsavel = !empty($_POST['entidade_responsavel']) ? $_POST['entidade_responsavel'] : null;
    if (
        ($tipo_documento === 'garantia' || $tipo_documento === 'contrato') &&
        empty($data_validade)
    ) {
        header("Location: adicionar_documento.php?validade_obrigatoria=1");
        exit;
    }

    $pastaDocumentos = "../assets/documentos/";
    $ficheiros = glob($pastaDocumentos . "*.pdf");

    $caminho_ficheiro = null;
    $nomePesquisa = normalizarTexto($nome_documento);

    foreach ($ficheiros as $ficheiro) {
        $nomeFicheiro = normalizarTexto(basename($ficheiro));

        if ($nomeFicheiro === $nomePesquisa) {
            $caminho_ficheiro = $ficheiro;
            break;
        }
    }

    if ($caminho_ficheiro === null) {
        header("Location: adicionar_documento.php?pdf_nao_encontrado=1");
        exit;
    } else {

        /*verifica se nao vai inserir um doc repetido para o mesmo equipamento */
        $verifica = $conn->prepare("
        SELECT id_documento
        FROM documentos_equipamento
        WHERE id_equipamento = ?
        AND LOWER(nome_documento) = LOWER(?)
    ");

        $verifica->bind_param("is", $id_equipamento, $nome_documento);
        $verifica->execute();

        $resultadoVerifica = $verifica->get_result();

        if ($resultadoVerifica->num_rows > 0) {
            header("Location: adicionar_documento.php?duplicado=1");
            exit;
        }

        /* ASSOCIAR AUTOMATICAMENTE CONTRATO/GARANTIA */
        $id_contrato = null;

        if (
            $tipo_documento === 'garantia' ||
            $tipo_documento === 'contrato'
        ) {

            $tipoContrato =
                $tipo_documento === 'garantia'
                ? 'garantia'
                : 'contrato_manutencao';

            $observacoesContrato = null;

            $stmtContrato = $conn->prepare("
        INSERT INTO contratos_garantias
        (
            id_equipamento,
            tipo,
            entidade_responsavel,
            data_inicio,
            data_fim,
            observacoes
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

            $stmtContrato->bind_param(
                "isssss",
                $id_equipamento,
                $tipoContrato,
                $entidade_responsavel,
                $data_documento,
                $data_validade,
                $observacoesContrato
            );

            $stmtContrato->execute();

            $id_contrato = $stmtContrato->insert_id;
        }


        $stmt = $conn->prepare("
            INSERT INTO documentos_equipamento
            (id_equipamento, id_contrato, tipo_documento, nome_documento, data_documento, data_validade, caminho_ficheiro)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iisssss",
            $id_equipamento,
            $id_contrato,
            $tipo_documento,
            $nome_documento,
            $data_documento,
            $data_validade,
            $caminho_ficheiro
        );

        if ($stmt->execute()) {
            header("Location: documentacao.php");
            exit;
        }

        $erro = "Erro ao guardar documento.";
    }
}
?>

<main class="private-main">

    <section class="private-header">
        <h1>Adicionar Documento</h1>
    </section>

    <section class="private-card">

        <form method="POST">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Equipamento *</label>
                    <select name="id_equipamento" class="form-select form-select-sm" required>
                        <option value="" selected disabled>Selecionar equipamento</option>
                        <?php while ($eq = $equipamentos->fetch_assoc()): ?>
                            <option value="<?= $eq['id_equipamento'] ?>">
                                <?= htmlspecialchars($eq['codigo_interno'] . ' - ' . $eq['designacao']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Tipo de documento *</label>
                    <select name="tipo_documento" class="form-select form-select-sm" required>
                        <option value="" selected disabled>Escolha o tipo de documento</option>
                        <option value="manual">Manual</option>
                        <option value="certificado">Certificado</option>
                        <option value="contrato">Contrato</option>
                        <option value="garantia">Garantia</option>
                        <option value="relatorio">Relatório</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Nome do documento *</label>
                    <input
                        type="text"
                        name="nome_documento"
                        class="form-control form-control-sm"
                        placeholder="Ex: Manual_Philips_IntelliVue_MP5"
                        required>


                </div>

                <div class="col-md-6 mb-3">
                    <label>Data do documento *</label>
                    <input
                        type="date"
                        name="data_documento"
                        class="form-control form-control-sm"
                        max="<?= date('Y-m-d') ?>"
                        required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Data de validade</label>
                    <input type="date" name="data_validade" id="data_validade_documento" class="form-control form-control-sm">
                    <small class="text-muted">
                        Obrigatória apenas para contratos e garantias.
                    </small>
                </div>

                <div class="col-md-6 mb-3" id="campo_entidade_responsavel" style="display:none;">
                    <label>Entidade responsável</label>
                    <select name="entidade_responsavel" class="form-select form-select-sm">
                        <option value="">Selecionar fornecedor</option>

                        <?php while ($f = $fornecedores->fetch_assoc()): ?>
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

            <div class="mt-3 d-flex align-items-center">

                <button type="submit" class="btn-primario">
                    Adicionar documento
                </button>

                <a href="documentacao.php" class="btn btn-secondary ms-2">
                    Cancelar
                </a>

                <button type="button"
                    class="btn btn-sm btn-outline-secondary ms-auto"
                    onclick="preencherDocumentoTeste()">
                    Preencher teste
                </button>

            </div>

        </form>

    </section>

</main>

<script>
    function preencherDocumentoTeste() {
        document.querySelector('[name="id_equipamento"]').value = '1';
        document.querySelector('[name="tipo_documento"]').value = 'manual';
        document.querySelector('[name="nome_documento"]').value = 'Manual_Philips_IntelliVue_MP5';
        document.querySelector('[name="data_documento"]').value = '2024-03-10';
        document.querySelector('[name="data_validade"]').value = '2027-03-10';
    }
</script>


<!-- MODAL DOCUMENTO DUPLICADO -->
<div class="modal fade" id="modalDocumentoDuplicado" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    Documento já associado
                </h5>
            </div>

            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>

                <p class="mb-0">
                    Este equipamento já possui um documento com esse nome.
                </p>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button"
                    class="btn btn-danger"
                    data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>



<!-- MODAL PDF NÃO ENCONTRADO -->
<div class="modal fade" id="modalPdfNaoEncontrado" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header justify-content-center">
                <h5 class="modal-title text-danger">
                    Documento não encontrado
                </h5>
            </div>

            <div class="modal-body text-center">

                <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>

                <p class="mb-0">
                    Não foi encontrado nenhum documento com o nome indicado.
                </p>

            </div>

            <div class="modal-footer justify-content-center">

                <button type="button"
                    class="btn btn-danger"
                    data-bs-dismiss="modal">
                    Fechar
                </button>

            </div>

        </div>
    </div>
</div>

<!-- MODAL de validade obrig para contratos e garantias -->
<div class="modal fade" id="modalValidadeObrigatoria" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    Data de validade obrigatória
                </h5>
            </div>

            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>

                <p class="mb-0">
                    Para documentos do tipo contrato ou garantia deve indicar a data de validade.
                </p>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button"
                    class="btn btn-danger"
                    data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>