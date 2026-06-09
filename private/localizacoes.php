<?php
include 'includes/db.php';

$sql = "SELECT 
            l.id_localizacao,
            l.zona,
            l.hospital,
            l.edificio,
            l.piso,
            l.servico,
            l.sala,
            e.id_equipamento,
            e.codigo_interno,
            e.designacao,
            e.marca,
            e.estado_atual,
            e.criticidade
        FROM localizacoes_ l
        LEFT JOIN equipamentos e 
            ON l.id_localizacao = e.id_localizacao
        ORDER BY l.zona, l.hospital, l.servico, e.codigo_interno";

$result = $conn->query($sql);

$dados = [];

while ($row = $result->fetch_assoc()) {
    $zona = $row['zona'] ?? 'Sem zona';
    $hospital = $row['hospital'] ?? 'Sem hospital';
    $servico = $row['servico'] ?? 'Sem serviço';

    $dados[$zona][$hospital][$servico][] = $row;
}

$ordemZonas = [
    'Geral',
    'Norte',
    'Centro',
    'Lisboa e Vale do Tejo',
    'Algarve'
];

$hospitaisPorZona = [
    'Norte' => [
        'Hospital São João',
        'Hospital Santo António',
        'Hospital de Braga',
        'Hospital de Viseu',
        'IPO Porto'
    ],
    'Centro' => [
        'Centro Hospitalar e Universitário de Coimbra',
        'Hospital Distrital de Leiria'
    ],
    'Lisboa e Vale do Tejo' => [
        'Centro Hospitalar Lisboa Norte',
        'Hospital Santa Maria',
        'Hospital Garcia de Orta'
    ],
    'Algarve' => [
        'Hospital de Faro'
    ]
];

function mostrarHospital($dados, $zona, $hospital, $idHospital) {
?>

    <div class="accordion-item">
        <h2 class="accordion-header" id="heading<?= $idHospital ?>">
            <button class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapse<?= $idHospital ?>">
                <?= htmlspecialchars($hospital) ?>
            </button>
        </h2>

        <div id="collapse<?= $idHospital ?>"
             class="accordion-collapse collapse"
             data-bs-parent="#accordion<?= str_replace(' ', '', $zona) ?>">

            <div class="accordion-body">

                <?php if (isset($dados[$zona][$hospital])): ?>

                    <div class="servicos-grid">

                        <?php $s = 0; ?>
                        <?php foreach ($dados[$zona][$hospital] as $servico => $linhas): ?>

                            <div class="servico-coluna">

                                <div class="accordion servicos-accordion" id="servicos<?= $idHospital ?><?= $s ?>">

                                    <div class="accordion-item">

                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapseServico<?= $idHospital ?><?= $s ?>">
                                                <?= htmlspecialchars($servico) ?>
                                            </button>
                                        </h2>

                                        <div id="collapseServico<?= $idHospital ?><?= $s ?>"
                                             class="accordion-collapse collapse">

                                            <div class="accordion-body">

                                                <?php
                                                $temEquipamentos = false;

                                                foreach ($linhas as $eq) {
                                                    if (!empty($eq['id_equipamento'])) {
                                                        $temEquipamentos = true;
                                                        break;
                                                    }
                                                }
                                                ?>

                                                <?php if ($temEquipamentos): ?>

                                                    <div class="equipamentos-servico-lista">

                                                        <?php foreach ($linhas as $eq): ?>

                                                            <?php if (!empty($eq['id_equipamento'])): ?>

                                                                <div class="equipamento-servico-item">

                                                                    <strong>
                                                                        <?= htmlspecialchars($eq['codigo_interno']) ?>
                                                                    </strong>

                                                                    <div class="equipamento-info">

                                                                        <div class="equipamento-nome">
                                                                            <?= htmlspecialchars($eq['designacao']) ?>
                                                                        </div>

                                                                        <div class="equipamento-localizacao">
                                                                            Sala <?= htmlspecialchars($eq['sala']) ?>
                                                                            · Piso <?= htmlspecialchars($eq['piso']) ?>
                                                                        </div>

                                                                    </div>

                                                                    <small>
                                                                        <?= htmlspecialchars($eq['marca']) ?> ·
                                                                        <?= htmlspecialchars($eq['estado_atual']) ?>
                                                                    </small>

                                                                </div>

                                                            <?php endif; ?>

                                                        <?php endforeach; ?>

                                                    </div>

                                                <?php else: ?>

                                                    <p class="text-muted mb-0">
                                                        Não existem equipamentos neste serviço.
                                                    </p>

                                                <?php endif; ?>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                            <?php $s++; ?>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <p class="text-muted mb-0">
                        Não existem serviços registados neste hospital.
                    </p>

                <?php endif; ?>

            </div>

        </div>
    </div>

<?php
}

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="private-main">

    <section class="private-header">
        <div>
            <h1>Localizações</h1>
        </div>
    </section>

    <section class="localizacoes-container">

        <ul class="nav nav-tabs localizacoes-tabs" role="tablist">

            <?php foreach ($ordemZonas as $i => $zona): ?>

                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link <?= $i === 0 ? 'active' : '' ?>"
                        data-bs-toggle="tab"
                        data-bs-target="#zona-<?= $i ?>"
                        type="button"
                        role="tab">
                        <?= htmlspecialchars($zona) ?>
                    </button>
                </li>

            <?php endforeach; ?>

        </ul>

        <div class="tab-content localizacoes-tab-content">

            <?php foreach ($ordemZonas as $i => $zona): ?>

                <div
                    class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>"
                    id="zona-<?= $i ?>"
                    role="tabpanel">

                    <?php if ($zona !== 'Geral'): ?>

                        <div class="accordion localizacoes-accordion" id="accordion<?= str_replace(' ', '', $zona) ?>">

                            <?php foreach ($hospitaisPorZona[$zona] ?? [] as $hospital): ?>

                                <?php
                                    $idHospital = preg_replace('/[^A-Za-z0-9]/', '', $hospital);
                                    mostrarHospital($dados, $zona, $hospital, $idHospital);
                                ?>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        </div>

    </section>

</main>
<script src="../assets/js/1230824.js"></script>
<?php include 'includes/footer.php'; ?>