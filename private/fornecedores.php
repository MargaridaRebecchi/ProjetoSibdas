<?php
include 'includes/db.php';

/*ABAS*/
$tabAtiva = $_GET['tab'] ?? 'lista';

$tabsPermitidas = ['lista', 'associar', 'associacoes'];

if (!in_array($tabAtiva, $tabsPermitidas)) {
    $tabAtiva = 'lista';
}

/* REMOVER ASSOCIAÇÃO FORNECEDOR - EQUIPAMENTO */
if (isset($_GET['apagar_assoc']) && is_numeric($_GET['apagar_assoc'])) {

    $id_assoc = (int) $_GET['apagar_assoc'];

    $stmt = $conn->prepare("
        DELETE FROM equipamento_fornecedor
        WHERE id_assoc = ?
    ");

    $stmt->bind_param("i", $id_assoc);
    $stmt->execute();

    header("Location: fornecedores.php?tab=associacoes&assoc_removida=1");
    exit();
}

/* ASSOCIAR FORNECEDOR A EQUIPAMENTO */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['associar_fornecedor'])) {

    $id_fornecedor = (int) $_POST['id_fornecedor'];
    $id_equipamento = (int) $_POST['id_equipamento'];
    $papel = $_POST['papel'];

    $papeisPermitidos = [
        'fabricante',
        'distribuidor',
        'fornecedor_consumiveis',
        'assistencia_tecnica'
    ];

    if (
        $id_fornecedor <= 0 ||
        $id_equipamento <= 0 ||
        !in_array($papel, $papeisPermitidos)
    ) {
        header("Location: fornecedores.php");
        exit();
    }

    // Evitar associação duplicada
    $stmtCheck = $conn->prepare("
        SELECT id_assoc
        FROM equipamento_fornecedor
        WHERE id_equipamento = ?
          AND id_fornecedor = ?
          AND papel = ?
    ");

    $stmtCheck->bind_param("iis", $id_equipamento, $id_fornecedor, $papel);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        header("Location: fornecedores.php?duplicado=1");
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO equipamento_fornecedor
        (id_equipamento, id_fornecedor, papel)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("iis", $id_equipamento, $id_fornecedor, $papel);
    try {
    $stmt->execute();

    header("Location: fornecedores.php?adicionado=1");
    exit();

} catch (mysqli_sql_exception $e) {

    if ($e->getCode() == 1062) {
        header("Location: adicionar_fornecedor.php?erro_nif=1");
        exit();
    }

    die("Erro ao adicionar fornecedor: " . $e->getMessage());
}
}
/* BARRA DE PESQUISA FORNECDORES E ASSOCIAÇÕES*/
$pesquisa = $_GET['pesquisa'] ?? '';
$whereSQL = '';

$pesquisaAssoc = $_GET['pesquisa_assoc'] ?? '';
$whereAssocSQL = '';

if ($pesquisaAssoc != '') {

    $pesquisaAssocSQL = $conn->real_escape_string(trim(mb_strtolower($pesquisaAssoc)));

    $whereAssocSQL = "WHERE
        LOWER(e.codigo_interno) LIKE '%$pesquisaAssocSQL%'
        OR LOWER(e.designacao) LIKE '%$pesquisaAssocSQL%'
        OR LOWER(f.nome_empresa) LIKE '%$pesquisaAssocSQL%'
        OR LOWER(ef.papel) LIKE '%$pesquisaAssocSQL%'
    ";
}
if ($pesquisa != '') {

    $pesquisaNormal = trim(mb_strtolower($pesquisa));

    $pesquisaNormalSQL = $conn->real_escape_string($pesquisaNormal);

    $whereSQL = "WHERE
        LOWER(nome_empresa) LIKE '%$pesquisaNormalSQL%'
        OR LOWER(nif) LIKE '%$pesquisaNormalSQL%'
        OR LOWER(email) LIKE '%$pesquisaNormalSQL%'
        OR LOWER(pessoa_contacto) LIKE '%$pesquisaNormalSQL%'
        OR LOWER(tipo_fornecedor) LIKE '%$pesquisaNormalSQL%'
    ";
}
//MOSTRAR 10 ASSOCIAÇÕES DE CADA VEZ NA TABELA
$limiteAssociacoes = 10;

$paginaAssociacoes = isset($_GET['pagina_assoc']) && is_numeric($_GET['pagina_assoc'])
    ? (int) $_GET['pagina_assoc']
    : 1;

if ($paginaAssociacoes < 1) {
    $paginaAssociacoes = 1;
}

$offsetAssociacoes = ($paginaAssociacoes - 1) * $limiteAssociacoes;

$sqlTotalAssociacoes = "
    SELECT COUNT(*) AS total
    FROM equipamento_fornecedor ef
    INNER JOIN fornecedores f
        ON ef.id_fornecedor = f.id_fornecedor
    INNER JOIN equipamentos e
        ON ef.id_equipamento = e.id_equipamento
    $whereAssocSQL
";

$resultTotalAssociacoes = $conn->query($sqlTotalAssociacoes);
$totalAssociacoes = $resultTotalAssociacoes->fetch_assoc()['total'];

$totalPaginasAssociacoes = ceil($totalAssociacoes / $limiteAssociacoes);





//MOSTRAR 10 FORNECEDORES DE CADA VEZ NA TABELA
$limite = 10;
$queryPesquisa = http_build_query([
    'pesquisa' => $pesquisa
]);

$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina'])
    ? (int) $_GET['pagina']
    : 1;

if ($pagina < 1) {
    $pagina = 1;
}

$offset = ($pagina - 1) * $limite;

$sqlTotal = "SELECT COUNT(*) AS total 
             FROM fornecedores
             $whereSQL";

$resultTotal = $conn->query($sqlTotal);
$totalFornecedores = $resultTotal->fetch_assoc()['total'];

$totalPaginas = ceil($totalFornecedores / $limite);

$sql = "
    SELECT *
    FROM fornecedores
    $whereSQL
    ORDER BY nome_empresa ASC
    LIMIT $limite OFFSET $offset
";

$result = $conn->query($sql);

/* DADOS PARA OS SELECTS DA ABA ASSOCIAR */
$sqlFornecedoresSelect = "
    SELECT id_fornecedor, nome_empresa, tipo_fornecedor
    FROM fornecedores
    ORDER BY nome_empresa ASC
";

$resultFornecedoresSelect = $conn->query($sqlFornecedoresSelect);

$sqlEquipamentosSelect = "
    SELECT id_equipamento, codigo_interno, designacao, marca, modelo
    FROM equipamentos
    ORDER BY codigo_interno ASC
";

$resultEquipamentosSelect = $conn->query($sqlEquipamentosSelect);

/* ASSOCIAÇÕES EXISTENTES */
$sqlAssociacoes = "
    SELECT 
        ef.id_assoc,
        ef.papel,
        f.nome_empresa,
        e.codigo_interno,
        e.designacao,
        e.marca,
        e.modelo
    FROM equipamento_fornecedor ef
    INNER JOIN fornecedores f
        ON ef.id_fornecedor = f.id_fornecedor
    INNER JOIN equipamentos e
        ON ef.id_equipamento = e.id_equipamento
    $whereAssocSQL
    ORDER BY e.codigo_interno ASC, f.nome_empresa ASC
    LIMIT $limiteAssociacoes OFFSET $offsetAssociacoes
";

$resultAssociacoes = $conn->query($sqlAssociacoes);

//Modal apagar fornecedor
if (isset($_GET['apagar']) && is_numeric($_GET['apagar'])) {

    $id = (int) $_GET['apagar'];

    $stmt = $conn->prepare("
        DELETE FROM fornecedores
        WHERE id_fornecedor = ?
    ");

    $stmt->bind_param("i", $id);

    $stmt->execute();

    header("Location: fornecedores.php?apagado=1");
    exit();
}
// Editar fornecedor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_fornecedor'])) {

    $id = (int) $_POST['id_fornecedor'];

    $sqlUpdate = "UPDATE fornecedores
                  SET nome_empresa = ?, nif = ?, telefone = ?, email = ?, morada = ?, website = ?,
                      pessoa_contacto = ?, telefone_contacto = ?, tipo_fornecedor = ?, observacoes = ?
                  WHERE id_fornecedor = ?";

    $stmt = $conn->prepare($sqlUpdate);

    $stmt->bind_param(
        "ssssssssssi",
        $_POST['nome_empresa'],
        $_POST['nif'],
        $_POST['telefone'],
        $_POST['email'],
        $_POST['morada'],
        $_POST['website'],
        $_POST['pessoa_contacto'],
        $_POST['telefone_contacto'],
        $_POST['tipo_fornecedor'],
        $_POST['observacoes'],
        $id
    );

    try {
        $stmt->execute();

        header("Location: fornecedores.php?editado=1");
        exit();
    } catch (mysqli_sql_exception $e) {

        if ($e->getCode() == 1062) {
            $erroModal = "Já existe um fornecedor com esse NIF.";
        } else {
            $erroModal = "Erro ao editar fornecedor: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
include 'includes/nav.php';
?>




<body class="area-privada-body">

    <main class="private-main">

        <section class="private-header">
            <div>
                <h1>Fornecedores</h1>
            </div>
            <ul class="nav nav-tabs fornecedores-tabs" role="tablist">

                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link <?= $tabAtiva == 'lista' ? 'active' : '' ?>"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-lista-fornecedores"
                        type="button"
                        role="tab">
                        Lista de fornecedores
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link <?= $tabAtiva == 'associar' ? 'active' : '' ?>"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-associar-fornecedores"
                        type="button"
                        role="tab">
                        Associar a equipamentos
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link <?= $tabAtiva == 'associacoes' ? 'active' : '' ?>"
                        data-bs-toggle="tab"
                        data-bs-target="#tab-associacoes-fornecedores"
                        type="button"
                        role="tab">
                        Associações existentes
                    </button>
                </li>

            </ul>


        </section>
        <div class="tab-content fornecedores-tab-content">

            <div
                class="tab-pane fade <?= $tabAtiva == 'lista' ? 'show active' : '' ?>"
                id="tab-lista-fornecedores"
                role="tabpanel">


                <a href="adicionar_fornecedor.php" class="btn-primario text-decoration-none">
                    <i class="fas fa-plus me-2"></i>
                    Novo fornecedor
                </a>

                <section class="private-card">



                    <!-- BARRA DE PESQUISA -->
                    <div class="pesquisa-wrapper">

                        <form method="GET" class="pesquisa-equipamentos">

                            <input
                                type="text"
                                name="pesquisa"
                                class="form-control form-control-sm"
                                placeholder="Pesquisar fornecedor..."
                                value="<?= htmlspecialchars($pesquisa) ?>">

                            <a
                                href="fornecedores.php"
                                class="btn btn-sm btn-outline-secondary">
                                Limpar
                            </a>
                        </form>
                    </div>


                    <div class="table-responsive">
                        <table class="table tabela-medgest align-middle">
                            <thead>
                                <tr>
                                    <th>Empresa</th>
                                    <th>NIF</th>
                                    <th>Tipo</th>
                                    <th>Pessoa de contacto</th>
                                    <th>Telefone</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if ($result && $result->num_rows > 0): ?>

                                    <?php while ($row = $result->fetch_assoc()): ?>

                                        <?php
                                        $tipoTexto = str_replace('_', ' ', $row['tipo_fornecedor']);

                                        if ($tipoTexto == 'fornecedor consumiveis') {
                                            $tipoTexto = 'fornecedor de consumíveis';
                                        }

                                        if ($tipoTexto == 'assistencia tecnica') {
                                            $tipoTexto = 'assistência técnica';
                                        }
                                        ?>

                                        <tr>
                                            <td><?= htmlspecialchars($row['nome_empresa']) ?></td>
                                            <td><?= htmlspecialchars($row['nif']) ?></td>
                                            <td><?= ucfirst(htmlspecialchars($tipoTexto)) ?></td>
                                            <td><?= htmlspecialchars($row['pessoa_contacto']) ?></td>
                                            <td><?= htmlspecialchars($row['telefone']) ?></td>

                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn-acao ver"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalVerFornecedor<?= $row['id_fornecedor'] ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <div class="modal fade" id="modalVerFornecedor<?= $row['id_fornecedor'] ?>" tabindex="-1">
                                                    <div class="modal-dialog modal-dialog-centered modal-lg">
                                                        <div class="modal-content ficha-equipamento">

                                                            <div class="modal-header justify-content-center">
                                                                <div class="text-center">
                                                                    <h5 class="modal-title">Ficha do Fornecedor</h5>
                                                                    <small class="text-muted"><?= htmlspecialchars($row['nome_empresa']) ?></small>
                                                                </div>
                                                            </div>

                                                            <div class="modal-body">

                                                                <h6 class="ficha-secao ficha-identificacao">Identificação</h6>
                                                                <div class="ficha-grid">
                                                                    <div><strong>Empresa</strong><span><?= htmlspecialchars($row['nome_empresa']) ?></span></div>
                                                                    <div><strong>NIF</strong><span><?= htmlspecialchars($row['nif']) ?></span></div>
                                                                    <div><strong>Tipo</strong><span><?= ucfirst(htmlspecialchars($tipoTexto)) ?></span></div>
                                                                    <div><strong>Data de registo</strong><span><?= htmlspecialchars($row['data_registo']) ?></span></div>
                                                                </div>

                                                                <h6 class="ficha-secao ficha-identificacao">Contactos da empresa</h6>
                                                                <div class="ficha-grid">
                                                                    <div><strong>Telefone</strong><span><?= htmlspecialchars($row['telefone']) ?></span></div>
                                                                    <div><strong>Email</strong><span><?= htmlspecialchars($row['email']) ?></span></div>
                                                                    <div><strong>Website</strong><span><?= htmlspecialchars($row['website']) ?></span></div>
                                                                    <div><strong>Morada</strong><span><?= htmlspecialchars($row['morada']) ?></span></div>
                                                                </div>

                                                                <h6 class="ficha-secao ficha-identificacao">Pessoa de contacto</h6>
                                                                <div class="ficha-grid">
                                                                    <div><strong>Nome</strong><span><?= htmlspecialchars($row['pessoa_contacto']) ?></span></div>
                                                                    <div><strong>Telefone</strong><span><?= htmlspecialchars($row['telefone_contacto']) ?></span></div>
                                                                </div>

                                                                <h6 class="ficha-secao ficha-identificacao">Observações</h6>
                                                                <div class="ficha-grid">
                                                                    <div style="grid-column: 1 / -1;">
                                                                        <strong>Observações</strong>
                                                                        <span><?= htmlspecialchars($row['observacoes']) ?></span>
                                                                    </div>
                                                                </div>

                                                            </div>



                                                        </div>
                                                    </div>
                                                </div>

                                                <button
                                                    type="button"
                                                    class="btn-acao editar"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditarFornecedor"
                                                    data-id="<?= $row['id_fornecedor'] ?>"
                                                    data-nome="<?= htmlspecialchars($row['nome_empresa']) ?>"
                                                    data-nif="<?= htmlspecialchars($row['nif']) ?>"
                                                    data-telefone="<?= htmlspecialchars($row['telefone']) ?>"
                                                    data-email="<?= htmlspecialchars($row['email']) ?>"
                                                    data-morada="<?= htmlspecialchars($row['morada']) ?>"
                                                    data-website="<?= htmlspecialchars($row['website']) ?>"
                                                    data-pessoa="<?= htmlspecialchars($row['pessoa_contacto']) ?>"
                                                    data-telefone-contacto="<?= htmlspecialchars($row['telefone_contacto']) ?>"
                                                    data-tipo="<?= htmlspecialchars($row['tipo_fornecedor']) ?>"
                                                    data-observacoes="<?= htmlspecialchars($row['observacoes']) ?>">
                                                    <i class="fas fa-pen"></i>
                                                </button>

                                                <button
                                                    type="button"
                                                    class="btn-acao apagar"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalApagarFornecedor"
                                                    data-id="<?= $row['id_fornecedor'] ?>"
                                                    data-nome="<?= htmlspecialchars($row['nome_empresa']) ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>

                                    <?php endwhile; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <?php if ($pesquisa != ''): ?>
                                                Nenhum fornecedor encontrado.
                                            <?php else: ?>
                                                Não existem fornecedores registados.
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>
                        <!-- MOSTRAR 1O FORNECEDORES DE CADA VEZ NA TABELA -->
                        <?php if ($totalPaginas > 0): ?>
                            <div class="d-flex justify-content-center align-items-center gap-3 mt-3">

                                <?php if ($pagina > 1): ?>
                                    <a href="fornecedores.php?pagina=<?= $pagina - 1 ?>&<?= $queryPesquisa ?>"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>

                                <span class="text-muted">
                                    Página <?= $pagina ?> de <?= $totalPaginas ?>
                                </span>

                                <?php if ($pagina < $totalPaginas): ?>
                                    <a href="fornecedores.php?pagina=<?= $pagina + 1 ?>&<?= $queryPesquisa ?>"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>

                            </div>
                        <?php endif; ?>

                    </div>

                </section>

            </div>
            <div
                class="tab-pane fade <?= $tabAtiva == 'associar' ? 'show active' : '' ?>"
                id="tab-associar-fornecedores"
                role="tabpanel">

                <section class="private-card">


                    <p class="text-muted">
                        Selecione um fornecedor, um equipamento e o papel que esse fornecedor tem relativamente ao equipamento.
                    </p>



                    <form method="POST" class="form-associar-fornecedor">

                        <input type="hidden" name="associar_fornecedor" value="1">

                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label>Fornecedor</label>
                                <select name="id_fornecedor" id="selectFornecedorAssociar" class="form-select form-select-sm" required onchange="atualizarPapelFornecedor()">
                                    <option value="">Escolha um fornecedor</option>

                                    <?php if ($resultFornecedoresSelect && $resultFornecedoresSelect->num_rows > 0): ?>
                                        <?php while ($forn = $resultFornecedoresSelect->fetch_assoc()): ?>

                                            <?php
                                            $tipoFornecedor = str_replace('_', ' ', $forn['tipo_fornecedor']);

                                            if ($tipoFornecedor == 'fornecedor consumiveis') {
                                                $tipoFornecedor = 'fornecedor de consumíveis';
                                            }

                                            if ($tipoFornecedor == 'assistencia tecnica') {
                                                $tipoFornecedor = 'assistência técnica';
                                            }
                                            ?>

                                            <option
                                                value="<?= $forn['id_fornecedor'] ?>"
                                                data-tipo="<?= htmlspecialchars($forn['tipo_fornecedor']) ?>">
                                                <?= htmlspecialchars($forn['nome_empresa']) ?>

                                            </option>

                                        <?php endwhile; ?>
                                    <?php endif; ?>

                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>Equipamento</label>
                                <select name="id_equipamento" class="form-select form-select-sm" required>
                                    <option value="">Escolha um equipamento</option>

                                    <?php if ($resultEquipamentosSelect && $resultEquipamentosSelect->num_rows > 0): ?>
                                        <?php while ($eq = $resultEquipamentosSelect->fetch_assoc()): ?>

                                            <option value="<?= $eq['id_equipamento'] ?>">
                                                <?= htmlspecialchars($eq['codigo_interno']) ?>
                                                — <?= htmlspecialchars($eq['designacao']) ?>
                                                <?= $eq['marca'] ? ' · ' . htmlspecialchars($eq['marca']) : '' ?>
                                                <?= $eq['modelo'] ? ' ' . htmlspecialchars($eq['modelo']) : '' ?>
                                            </option>

                                        <?php endwhile; ?>
                                    <?php endif; ?>

                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label>
                                    Papel do fornecedor

                                    <i class="fas fa-question-circle ms-1"
                                        data-bs-toggle="tooltip"
                                        data-bs-html="true"
                                        title="
       <strong>Fabricante:</strong> produz o equipamento.<br><br>
       <strong>Distribuidor:</strong> vende ou fornece o equipamento.<br><br>
       <strong>Fornecedor de consumíveis:</strong> fornece acessórios e consumíveis.<br><br>
       <strong>Assistência técnica:</strong> realiza manutenção e reparações.
       ">
                                    </i>
                                </label>
                                <select id="selectPapelAssociar" class="form-select form-select-sm" required onchange="filtrarFornecedoresPorPapel()">
                                    <option value="">Escolha um papel</option>
                                    <option value="fabricante">Fabricante</option>
                                    <option value="distribuidor">Distribuidor</option>
                                    <option value="fornecedor_consumiveis">Fornecedor de consumíveis</option>
                                    <option value="assistencia_tecnica">Assistência técnica</option>
                                </select>

                                <input type="hidden" name="papel" id="inputPapelAssociar">
                            </div>

                        </div>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn-primario">
                                <i class="fas fa-link me-2"></i>
                                Associar
                            </button>
                        </div>

                    </form>


                </section>

            </div>
            <div
                class="tab-pane fade <?= $tabAtiva == 'associacoes' ? 'show active' : '' ?>"
                id="tab-associacoes-fornecedores"
                role="tabpanel">

                <section class="private-card">
                    <!-- Barra de pesquisa -->
                    <div class="pesquisa-wrapper">

                        <form method="GET" class="pesquisa-equipamentos">

                            <input type="hidden" name="tab" value="associacoes">

                            <input
                                type="text"
                                name="pesquisa_assoc"
                                class="form-control form-control-sm"
                                placeholder="Pesquisar associação..."
                                value="<?= htmlspecialchars($pesquisaAssoc) ?>">

                            <a
                                href="fornecedores.php?tab=associacoes"
                                class="btn btn-sm btn-outline-secondary">
                                Limpar
                            </a>

                        </form>

                    </div>
                    <table class="table tabela-medgest align-middle">

                        <thead>
                            <tr>
                                <th>Equipamento</th>
                                <th>Fornecedor</th>
                                <th>Papel</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($resultAssociacoes && $resultAssociacoes->num_rows > 0): ?>
                                <?php $equipamentoAnterior = ''; ?>

                                <?php while ($assoc = $resultAssociacoes->fetch_assoc()): ?>
                                    <?php
                                    $novoEquipamento = $assoc['codigo_interno'] != $equipamentoAnterior;

                                    $equipamentoAnterior = $assoc['codigo_interno'];
                                    ?>


                                    <?php
                                    $papelTexto = str_replace('_', ' ', $assoc['papel']);

                                    if ($papelTexto == 'fornecedor consumiveis') {
                                        $papelTexto = 'fornecedor de consumíveis';
                                    }

                                    if ($papelTexto == 'assistencia tecnica') {
                                        $papelTexto = 'assistência técnica';
                                    }
                                    ?>

                                    <tr class="<?= $novoEquipamento ? 'linha-novo-equipamento' : '' ?>">
                                        <td>
                                            <strong><?= htmlspecialchars($assoc['codigo_interno']) ?></strong>
                                            <br>
                                            <small><?= htmlspecialchars($assoc['designacao']) ?></small>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($assoc['nome_empresa']) ?>
                                        </td>

                                        <td>
                                            <?= ucfirst(htmlspecialchars($papelTexto)) ?>
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn-acao apagar"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalApagarAssociacao"
                                                onclick="document.getElementById('inputApagarAssociacao').value='<?= $assoc['id_assoc'] ?>';
                                                document.getElementById('textoAssociacaoApagar').textContent='<?= htmlspecialchars($assoc['nome_empresa'] . ' - ' . $assoc['designacao']) ?>';">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Ainda não existem associações registadas.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                    <!-- Paginação tabela das associações -->
                    <?php if ($totalPaginasAssociacoes > 1): ?>
                        <div class="d-flex justify-content-center align-items-center gap-3 mt-3">

                            <?php if ($paginaAssociacoes > 1): ?>
                                <a href="fornecedores.php?tab=associacoes&pagina_assoc=<?= $paginaAssociacoes - 1 ?>"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <span class="text-muted">
                                Página <?= $paginaAssociacoes ?> de <?= $totalPaginasAssociacoes ?>
                            </span>

                            <?php if ($paginaAssociacoes < $totalPaginasAssociacoes): ?>
                                <a href="fornecedores.php?tab=associacoes&pagina_assoc=<?= $paginaAssociacoes + 1 ?>"
                                    class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>
                </section>

            </div>

        </div>


    </main>

    <!-- Modal editar fornecedor -->
    <div class="modal fade" id="modalEditarFornecedor" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <form method="POST" id="formEditarFornecedor" novalid>

                    <input type="hidden" name="editar_fornecedor" value="1">
                    <input type="hidden" name="id_fornecedor" id="edit_id_fornecedor">

                    <div class="modal-header justify-content-center">
                        <h5 class="modal-title text-center">Editar fornecedor</h5>
                    </div>

                    <div class="modal-body">

                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label>Nome da empresa</label>
                                <input type="text" name="nome_empresa" id="edit_nome_empresa" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>NIF</label>
                                <input type="text" name="nif" id="edit_nif" class="form-control form-control-sm" maxlength="9" pattern="[0-9]{9}" required>
                                <div class="invalid-feedback">O NIF deve ter exatamente 9 dígitos.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Telefone</label>
                                <input type="text" name="telefone" id="edit_telefone" class="form-control form-control-sm" maxlength="9" pattern="[0-9]{9}" required>
                                <div class="invalid-feedback">O telefone deve ter exatamente 9 dígitos.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control form-control-sm" required>
                                <div class="invalid-feedback">Introduza um email válido.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Morada</label>
                                <input type="text" name="morada" id="edit_morada" class="form-control form-control-sm" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Website</label>
                                <input type="text" name="website" id="edit_website" class="form-control form-control-sm" required>
                                <div class="invalid-feedback">Introduza um website válido.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Pessoa de contacto</label>
                                <input type="text" name="pessoa_contacto" id="edit_pessoa_contacto" class="form-control form-control-sm" pattern="[A-Za-zÀ-ÿ\s]+" required>
                                <div class="invalid-feedback">A pessoa de contacto deve conter apenas letras.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Telefone da pessoa de contacto</label>
                                <input type="text" name="telefone_contacto" id="edit_telefone_contacto" class="form-control form-control-sm" maxlength="9" pattern="[0-9]{9}" required>
                                <div class="invalid-feedback">O telefone da pessoa de contacto deve ter exatamente 9 dígitos.</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label>Tipo de fornecedor</label>
                                <select name="tipo_fornecedor" id="edit_tipo_fornecedor" class="form-select form-select-sm" required>
                                    <option value="fabricante">Fabricante</option>
                                    <option value="distribuidor">Distribuidor</option>
                                    <option value="fornecedor_consumiveis">Fornecedor de consumíveis</option>
                                    <option value="assistencia_tecnica">Assistência técnica</option>
                                </select>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label>Observações</label>
                                <textarea name="observacoes" id="edit_observacoes" class="form-control form-control-sm" rows="3" required></textarea>
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

    <!-- Modal apagar fornecedor -->
    <div class="modal fade" id="modalApagarFornecedor" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Remover fornecedor</h5>

                </div>

                <div class="modal-body">
                    Tem a certeza que quer remover o fornecedor
                    <strong id="nomeFornecedorApagar"></strong>?
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <a href="#" id="confirmarApagarFornecedor" class="btn btn-danger">
                        Remover
                    </a>
                </div>

            </div>
        </div>
    </div>


    <!--MODAL SUCESSO EDIÇÃO DE FORNECEDOR -->
    <div class="modal fade" id="modalEditadoFornecedor" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header justify-content-center">
                    <h5 class="modal-title text-success text-center">
                        Fornecedor atualizado
                    </h5>
                </div>

                <div class="modal-body text-center">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                    <p class="mb-0">
                        Fornecedor atualizado com sucesso!
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


    <!--MODAL SUCESSO REMOÇÃO DE FORNECEDOR E ASSOCIAÇÃO -->
    <div class="modal fade" id="modalSucesso" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header justify-content-center">
                    <h5 class="modal-title text-success text-center">
                        Fornecedor removido
                    </h5>
                </div>

                <div class="modal-body text-center">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                    <p class="mb-0">
                        Remoção efetuada com sucesso!
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


    <!--Modal para remover associação -->
    <div class="modal fade" id="modalApagarAssociacao" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Remover associação</h5>
                </div>

                <div class="modal-body">
                    Tem a certeza que quer remover a associação
                    <strong id="textoAssociacaoApagar"></strong>?
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <form method="GET" action="fornecedores.php">
                        <input type="hidden" name="apagar_assoc" id="inputApagarAssociacao">
                        <button type="submit" class="btn btn-danger">
                            Remover
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL SUCESSO ASSOCIAÇÃO -->
    <div class="modal fade" id="modalAssociacaoSucesso" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header justify-content-center">
                    <h5 class="modal-title text-success text-center">
                        Associação criada
                    </h5>
                </div>

                <div class="modal-body text-center">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                    <p class="mb-0">
                        Fornecedor associado ao equipamento com sucesso!
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

    <!-- MODAL ASSOCIAÇÃO DUPLICADA -->
    <div class="modal fade" id="modalAssociacaoDuplicada" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header justify-content-center">
                    <h5 class="modal-title text-danger text-center">
                        Associação já existente
                    </h5>
                </div>

                <div class="modal-body text-center">
                    <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>

                    <p class="mb-0">
                        Esta associação já existe.
                    </p>
                </div>

                <div class="modal-footer justify-content-center">
                    <button
                        type="button"
                        class="btn btn-danger"
                        data-bs-dismiss="modal">
                        Fechar
                    </button>
                </div>

            </div>
        </div>
    </div>
    <?php if (isset($_GET['editado'])): ?>

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const modal = new bootstrap.Modal(
                    document.getElementById('modalEditadoFornecedor')
                );

                modal.show();

                window.history.replaceState({},
                    document.title,
                    'fornecedores.php'
                );

            });
        </script>

    <?php endif; ?>



    <?php if (isset($_GET['apagado']) || isset($_GET['assoc_removida'])): ?>

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const modalSucesso = new bootstrap.Modal(
                    document.getElementById('modalSucesso')
                );

                modalSucesso.show();

                <?php if (isset($_GET['assoc_removida'])): ?>
                    window.history.replaceState({}, document.title, 'fornecedores.php?tab=associacoes');
                <?php else: ?>
                    window.history.replaceState({}, document.title, 'fornecedores.php');
                <?php endif; ?>

            });
        </script>

    <?php endif; ?>

    <script>
        const mostrarModalAssociacao =
            <?= isset($_GET['associado']) ? 'true' : 'false' ?>;
    </script>
    <script>
        const mostrarModalDuplicado =
            <?= isset($_GET['duplicado']) ? 'true' : 'false' ?>;
    </script>

    <link rel="stylesheet" href="/SIBDAS_PROJETO_26_MEDGEST/assets/css/1230824.css">
    <style>
        .tooltip {
            --bs-tooltip-bg: #e8eef1 !important;
            --bs-tooltip-color: #212529 !important;
            --bs-tooltip-opacity: 1 !important;
        }

        .tooltip .tooltip-inner {
            background-color: #e8eef1 !important;
            color: #212529 !important;
            border: 1px solid #ced4da !important;
            max-width: 320px;
            text-align: left;
            padding: 12px;
            font-size: 0.9rem;
        }

        .tooltip .tooltip-arrow::before {
            border-top-color: #e8eef1 !important;
            border-bottom-color: #e8eef1 !important;
            border-left-color: #e8eef1 !important;
            border-right-color: #e8eef1 !important;
        }

        .fa-question-circle {
            color: #0d4e6d !important;
            cursor: pointer;
            font-size: 0.95rem;
        }
    </style>
    <?php if (isset($_GET['pagina_assoc']) || isset($_GET['pesquisa_assoc'])): ?>
        <script>
            window.history.replaceState({}, document.title, 'fornecedores.php');
        </script>
    <?php endif; ?>
</body>

<?php include 'includes/footer.php'; ?>