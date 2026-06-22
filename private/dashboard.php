<?php
include 'includes/db.php';

/* CARDS EQUIPAMENTOS */

$totalEquipamentos = $conn->query("
    SELECT COUNT(*) total
    FROM equipamentos
")->fetch_assoc()['total'];

$totalAtivos = $conn->query("
    SELECT COUNT(*) total
    FROM equipamentos
    WHERE estado_atual = 'ativo'
")->fetch_assoc()['total'];

$totalManutencao = $conn->query("
    SELECT COUNT(*) total
    FROM equipamentos
    WHERE estado_atual = 'em_manutencao'
")->fetch_assoc()['total'];

$totalInativos = $conn->query("
    SELECT COUNT(*) total
    FROM equipamentos
    WHERE estado_atual = 'inativo'
")->fetch_assoc()['total'];

/* GRAFICO CATEGORIAS */

$resultCategorias = $conn->query("
    SELECT categoria,
           COUNT(*) total
    FROM equipamentos
    GROUP BY categoria
");

$categorias = [];
$totaisCategorias = [];

while ($row = $resultCategorias->fetch_assoc()) {

    $categorias[] = match ($row['categoria']) {
        'monitorizacao' => 'Monitorização',
        'suporte_vida' => 'Suporte de Vida',
        'terapia' => 'Terapia',
        'diagnostico' => 'Diagnóstico',
        'laboratorio' => 'Laboratório',
        'esterilizacao' => 'Esterilização',
        'reabilitacao' => 'Reabilitação',
        default => ucfirst(str_replace('_', ' ', $row['categoria']))
    };

    $totaisCategorias[] = $row['total'];
}

/* GRAFICO CRITICIDADE */

$resultCriticidade = $conn->query("
    SELECT criticidade,
           COUNT(*) total
    FROM equipamentos
    GROUP BY criticidade

    ORDER BY FIELD(
        criticidade,
        'suporte_vida',
        'alta',
        'media',
        'baixa'
    )
");

$criticidades = [];
$totaisCriticidade = [];

while ($row = $resultCriticidade->fetch_assoc()) {

    $criticidades[] = match ($row['criticidade']) {
        'suporte_vida' => 'Suporte de Vida',
        'alta' => 'Alta',
        'media' => 'Média',
        'baixa' => 'Baixa',
        default => ucfirst(str_replace('_', ' ', $row['criticidade']))
    };
    $coresCriticidade[] =
        match ($row['criticidade']) {
            'baixa' => '#28a745',
            'media' => '#ffc107',
            'alta' => '#fd7e14',
            'suporte_vida' => '#dc3545',
            default => '#6c757d'
        };

    $totaisCriticidade[] = $row['total'];
}

/* DASHBOARD LOCALIZAÇÕES */

/* Equipamentos por zona */
$resultZonas = $conn->query("
    SELECT 
        l.zona,
        COUNT(e.id_equipamento) total
    FROM localizacoes_ l
    LEFT JOIN equipamentos e
        ON l.id_localizacao = e.id_localizacao
    WHERE l.zona IS NOT NULL
    GROUP BY l.zona
    ORDER BY FIELD(l.zona, 'Norte', 'Centro', 'Lisboa e Vale do Tejo', 'Algarve')
");

$zonas = [];
$totaisZonas = [];

while ($row = $resultZonas->fetch_assoc()) {
    $zonas[] = $row['zona'];
    $totaisZonas[] = $row['total'];
}

/* Top 5 hospitais com mais equipamentos */
$resultTopHospitais = $conn->query("
    SELECT 
        l.hospital,
        COUNT(e.id_equipamento) total
    FROM localizacoes_ l
    LEFT JOIN equipamentos e
        ON l.id_localizacao = e.id_localizacao
    GROUP BY l.hospital
    HAVING total > 0
    ORDER BY total DESC
    LIMIT 5
");

$hospitaisTop = [];
$totaisHospitaisTop = [];

while ($row = $resultTopHospitais->fetch_assoc()) {
    $hospitaisTop[] = $row['hospital'];
    $totaisHospitaisTop[] = $row['total'];
}

/* Equipamentos por serviço */
$resultServicos = $conn->query("
    SELECT 
        l.servico,
        COUNT(e.id_equipamento) total
    FROM localizacoes_ l
    LEFT JOIN equipamentos e
        ON l.id_localizacao = e.id_localizacao
    WHERE l.servico IS NOT NULL
      AND l.servico <> ''
    GROUP BY l.servico
    HAVING total > 0
    ORDER BY total DESC
");

$servicos = [];
$totaisServicos = [];

while ($row = $resultServicos->fetch_assoc()) {
    $servicos[] = $row['servico'];
    $totaisServicos[] = $row['total'];
}

/* DASHBOARD FORNECEDORES */

/* Cards fornecedores */
$totalFornecedores = $conn->query("
    SELECT COUNT(*) total
    FROM fornecedores
")->fetch_assoc()['total'];

$totalFabricantes = $conn->query("
    SELECT COUNT(*) total
    FROM fornecedores
    WHERE tipo_fornecedor = 'fabricante'
")->fetch_assoc()['total'];

$totalDistribuidores = $conn->query("
    SELECT COUNT(*) total
    FROM fornecedores
    WHERE tipo_fornecedor = 'distribuidor'
")->fetch_assoc()['total'];

$totalAssistencia = $conn->query("
    SELECT COUNT(*) total
    FROM fornecedores
    WHERE tipo_fornecedor = 'assistencia_tecnica'
")->fetch_assoc()['total'];

/* Fornecedores por tipo */
$resultTiposFornecedores = $conn->query("
    SELECT tipo_fornecedor,
           COUNT(*) total
    FROM fornecedores
    GROUP BY tipo_fornecedor
    ORDER BY FIELD(
        tipo_fornecedor,
        'fabricante',
        'distribuidor',
        'assistencia_tecnica',
        'fornecedor_consumiveis'
    )
");

$tiposFornecedores = [];
$totaisTiposFornecedores = [];
$coresFornecedores = [];

while ($row = $resultTiposFornecedores->fetch_assoc()) {

    $tiposFornecedores[] = match ($row['tipo_fornecedor']) {
        'fabricante' => 'Fabricante',
        'distribuidor' => 'Distribuidor',
        'assistencia_tecnica' => 'Assistência Técnica',
        'fornecedor_consumiveis' => 'Fornecedor de Consumíveis',
        default => ucfirst(str_replace('_', ' ', $row['tipo_fornecedor']))
    };

    $totaisTiposFornecedores[] = $row['total'];

    $coresFornecedores[] = match ($row['tipo_fornecedor']) {
        'fabricante' => '#0d6efd',
        'distribuidor' => '#20c997',
        'assistencia_tecnica' => '#fd7e14',
        'fornecedor_consumiveis' => '#6f42c1',
        default => '#6c757d'
    };
}

/* Top fornecedores mais associados */
$resultTopFornecedores = $conn->query("
    SELECT 
        f.nome_empresa,
        COUNT(ef.id_assoc) total
    FROM fornecedores f
    INNER JOIN equipamento_fornecedor ef
        ON f.id_fornecedor = ef.id_fornecedor
    GROUP BY f.id_fornecedor, f.nome_empresa
    ORDER BY total DESC
    LIMIT 5
");

$fornecedoresTop = [];
$totaisFornecedoresTop = [];

while ($row = $resultTopFornecedores->fetch_assoc()) {
    $fornecedoresTop[] = $row['nome_empresa'];
    $totaisFornecedoresTop[] = $row['total'];
}
/* DASHBOARD DOCUMENTAÇÃO */

/* Cards */

$totalDocumentos = $conn->query("
    SELECT COUNT(*) total
    FROM documentos_equipamento
")->fetch_assoc()['total'];

$totalContratos = $conn->query("
    SELECT COUNT(*) total
    FROM contratos_garantias
")->fetch_assoc()['total'];

$totalGarantias = $conn->query("
    SELECT COUNT(*) total
    FROM contratos_garantias
    WHERE tipo = 'garantia'
")->fetch_assoc()['total'];

$totalEquipamentosSemDocs = $conn->query("
    SELECT COUNT(*) total
    FROM equipamentos e
    LEFT JOIN documentos_equipamento d
        ON e.id_equipamento = d.id_equipamento
    WHERE d.id_documento IS NULL
")->fetch_assoc()['total'];

$resultTiposDocumento = $conn->query("
    SELECT tipo_documento,
           COUNT(*) total
    FROM documentos_equipamento
    GROUP BY tipo_documento
");

$tiposDocumento = [];
$totaisTiposDocumento = [];

while ($row = $resultTiposDocumento->fetch_assoc()) {

    $tiposDocumento[] = match ($row['tipo_documento']) {
        'manual' => 'Manual',
        'certificado' => 'Certificado',
        'contrato' => 'Contrato',
        'garantia' => 'Garantia',
        'relatorio' => 'Relatório',
        'outro' => 'Outro',
        default => $row['tipo_documento']
    };

    $totaisTiposDocumento[] = $row['total'];
}


$equipamentosSemDocs = $conn->query("
    SELECT
        e.codigo_interno,
        e.designacao
    FROM equipamentos e
    LEFT JOIN documentos_equipamento d
        ON e.id_equipamento = d.id_equipamento
    WHERE d.id_documento IS NULL
    ORDER BY e.codigo_interno ASC
");

/* DASHBOARD FINANCEIRO */

$totalInvestimento = $conn->query("
    SELECT SUM(custo_aquisicao) total
    FROM equipamentos
    WHERE custo_aquisicao IS NOT NULL
")->fetch_assoc()['total'] ?? 0;

$custoMedio = $conn->query("
    SELECT AVG(custo_aquisicao) total
    FROM equipamentos
    WHERE custo_aquisicao IS NOT NULL
")->fetch_assoc()['total'] ?? 0;

$resultInvestimentoAno = $conn->query("
    SELECT
        YEAR(data_aquisicao) ano,
        SUM(custo_aquisicao) total
    FROM equipamentos
    WHERE data_aquisicao IS NOT NULL
      AND custo_aquisicao IS NOT NULL
    GROUP BY YEAR(data_aquisicao)
    ORDER BY ano
");

$anosInvestimento = [];
$totaisInvestimento = [];

while ($row = $resultInvestimentoAno->fetch_assoc()) {
    $anosInvestimento[] = $row['ano'];
    $totaisInvestimento[] = round($row['total'], 2);
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="private-main">

    <section class="private-header">
        <div>
            <h1>Dashboard</h1>
        </div>
    </section>

    <section class="localizacoes-container">

        <ul class="nav nav-tabs localizacoes-tabs" role="tablist">

            <li class="nav-item">
                <button
                    class="nav-link active"
                    data-bs-toggle="tab"
                    data-bs-target="#equipamentos"
                    type="button">
                    Equipamentos
                </button>
            </li>

            <li class="nav-item">
                <button
                    class="nav-link"
                    data-bs-toggle="tab"
                    data-bs-target="#localizacoes"
                    type="button">
                    Localizações
                </button>
            </li>

            <li class="nav-item">
                <button
                    class="nav-link"
                    data-bs-toggle="tab"
                    data-bs-target="#fornecedores"
                    type="button">
                    Fornecedores
                </button>
            </li>

            <li class="nav-item">
                <button
                    class="nav-link"
                    data-bs-toggle="tab"
                    data-bs-target="#documentacao"
                    type="button">
                    Documentação
                </button>
            </li>

            <li class="nav-item">
                <button
                    class="nav-link"
                    data-bs-toggle="tab"
                    data-bs-target="#financeiro"
                    type="button">
                    Financeiro
                </button>
            </li>

        </ul>

        <div class="tab-content localizacoes-tab-content">

            <!-- EQUIPAMENTOS -->
            <div
                class="tab-pane fade show active"
                id="equipamentos">

                <div class="dashboard-cards">

                    <div class="dashboard-card">
                        <h6>Total Equipamentos</h6>
                        <span><?= $totalEquipamentos ?></span>
                    </div>

                    <div class="dashboard-card">
                        <h6>Ativos</h6>
                        <span><?= $totalAtivos ?></span>
                    </div>

                    <div class="dashboard-card">
                        <h6>Em Manutenção</h6>
                        <span><?= $totalManutencao ?></span>
                    </div>

                    <div class="dashboard-card">
                        <h6>Inativos</h6>
                        <span><?= $totalInativos ?></span>
                    </div>

                </div>

                <div class="dashboard-graficos">

                    <div class="dashboard-grafico-card">
                        <h5>Equipamentos por Categoria</h5>

                        <canvas id="graficoCategorias"></canvas>
                    </div>

                    <div class="dashboard-grafico-card">

                        <h5>Equipamentos por Criticidade</h5>

                        <div class="criticidade-wrapper">
                            <div class="criticidade-grafico">
                                <canvas id="graficoCriticidade"></canvas>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <!-- LOCALIZAÇÕES -->
            <div
                class="tab-pane fade"
                id="localizacoes">

                <div class="dashboard-graficos">

                    <div class="dashboard-grafico-card">
                        <h5>Equipamentos por Zona</h5>
                        <canvas id="graficoZonas"></canvas>
                    </div>

                    <div class="dashboard-grafico-card">
                        <h5>Equipamentos por Serviço</h5>
                        <canvas id="graficoServicos"></canvas>
                    </div>

                    <div class="dashboard-grafico-card">
                        <h5>Top 5 Hospitais com Mais Equipamentos</h5>
                        <canvas id="graficoTopHospitais"></canvas>
                    </div>


                </div>

            </div>

            <!-- FORNECEDORES -->
            <div
                class="tab-pane fade"
                id="fornecedores">

                <div class="dashboard-cards">

                    <div class="dashboard-card">
                        <h6>Total Fornecedores</h6>
                        <span><?= $totalFornecedores ?></span>
                    </div>

                    <div class="dashboard-card">
                        <h6>Fabricantes</h6>
                        <span><?= $totalFabricantes ?></span>
                    </div>

                    <div class="dashboard-card">
                        <h6>Distribuidores</h6>
                        <span><?= $totalDistribuidores ?></span>
                    </div>

                    <div class="dashboard-card">
                        <h6>Assistência Técnica</h6>
                        <span><?= $totalAssistencia ?></span>
                    </div>

                </div>

                <div class="dashboard-graficos">

                    <div class="dashboard-grafico-card">
                        <h5>Fornecedores por Tipo</h5>
                        <canvas id="graficoTiposFornecedores"></canvas>
                    </div>

                    <div class="dashboard-grafico-card">
                        <h5>Top 5 Fornecedores Mais Associados</h5>
                        <canvas id="graficoTopFornecedores"></canvas>
                    </div>

                </div>

            </div>

            <!-- DOCUMENTAÇÃO -->
            <div class="tab-pane fade" id="documentacao">

                <div class="dashboard-cards">

                    <div class="dashboard-card">
                        <h6>Total Documentos</h6>
                        <span><?= $totalDocumentos ?></span>
                    </div>

                    <div class="dashboard-card">
                        <h6>Total Contratos</h6>
                        <span><?= $totalContratos ?></span>
                    </div>

                    <div class="dashboard-card">
                        <h6>Total Garantias</h6>
                        <span><?= $totalGarantias ?></span>
                    </div>

                    <div class="dashboard-card">
                        <h6>Equipamentos sem Documentação</h6>
                        <span><?= $totalEquipamentosSemDocs ?></span>
                    </div>

                </div>

                <div class="dashboard-graficos">

                    <div class="dashboard-grafico-card">
                        <h5>Documentos por Tipo</h5>
                        <canvas id="graficoTiposDocumento"></canvas>
                    </div>

                    <div class="dashboard-grafico-card">

                        <h5>Equipamentos sem Documentação</h5>

                        <div class="dashboard-sem-docs">

                            <?php while ($eq = $equipamentosSemDocs->fetch_assoc()): ?>

                                <div class="dashboard-sem-doc-item">

                                    <strong>
                                        <?= htmlspecialchars($eq['codigo_interno']) ?>
                                    </strong>

                                    <span>
                                        <?= htmlspecialchars($eq['designacao']) ?>
                                    </span>

                                </div>

                            <?php endwhile; ?>

                        </div>

                    </div>

                </div>

            </div>

            <!-- FINANCEIRO -->
            <div class="tab-pane fade" id="financeiro">

                <div class="financeiro-container">

                    <div class="dashboard-card financeiro-card">

                        <h6>Investimento Total</h6>

                        <span>
                            <?= number_format($totalInvestimento, 0, ',', '.') ?>€
                        </span>

                    </div>

                    <div class="dashboard-grafico-card financeiro-grafico">

                        <h5>Investimento por Ano de Aquisição</h5>

                        <canvas id="graficoInvestimentoAno"></canvas>

                    </div>

                </div>

            </div>

        </div>

    </section>

</main>
<script>
    const categorias = <?= json_encode($categorias) ?>;
    const totaisCategorias = <?= json_encode($totaisCategorias) ?>;

    const criticidades = <?= json_encode($criticidades) ?>;
    const totaisCriticidade = <?= json_encode($totaisCriticidade) ?>;
    const coresCriticidade = <?= json_encode($coresCriticidade) ?>;
    const zonas = <?= json_encode($zonas) ?>;
    const totaisZonas = <?= json_encode($totaisZonas) ?>;

    const servicos = <?= json_encode($servicos) ?>;
    const totaisServicos = <?= json_encode($totaisServicos) ?>;

    const hospitaisTop = <?= json_encode($hospitaisTop) ?>;
    const totaisHospitaisTop = <?= json_encode($totaisHospitaisTop) ?>;

    const tiposFornecedores = <?= json_encode($tiposFornecedores) ?>;
    const totaisTiposFornecedores = <?= json_encode($totaisTiposFornecedores) ?>;
    const coresFornecedores = <?= json_encode($coresFornecedores) ?>;

    const fornecedoresTop = <?= json_encode($fornecedoresTop) ?>;
    const totaisFornecedoresTop = <?= json_encode($totaisFornecedoresTop) ?>;

    const tiposDocumento = <?= json_encode($tiposDocumento) ?>;
    const totaisTiposDocumento = <?= json_encode($totaisTiposDocumento) ?>;

    const anosInvestimento = <?= json_encode($anosInvestimento) ?>;
    const totaisInvestimento = <?= json_encode($totaisInvestimento) ?>;
</script>
<?php include 'includes/footer.php'; ?>