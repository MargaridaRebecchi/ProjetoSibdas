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

    $categorias[] = match($row['categoria']) {
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

    $criticidades[] = match($row['criticidade']) {
    'suporte_vida' => 'Suporte de Vida',
    'alta' => 'Alta',
    'media' => 'Média',
    'baixa' => 'Baixa',
    default => ucfirst(str_replace('_', ' ', $row['criticidade']))
    };
    $coresCriticidade[] =
    match($row['criticidade']) {
        'baixa' => '#28a745',
        'media' => '#ffc107',
        'alta' => '#fd7e14',
        'suporte_vida' => '#dc3545',
        default => '#6c757d'
    };

    $totaisCriticidade[] = $row['total'];
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

                AQUI VÃO OS GRÁFICOS

            </div>

            <!-- FORNECEDORES -->
            <div
                class="tab-pane fade"
                id="fornecedores">

                AQUI VÃO OS CARDS E GRÁFICOS

            </div>

            <!-- DOCUMENTAÇÃO -->
            <div
                class="tab-pane fade"
                id="documentacao">

                AQUI VÃO OS CARDS E GRÁFICOS

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

</script>
<?php include 'includes/footer.php'; ?>