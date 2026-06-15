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
$pesquisaLocalizacoes = $_GET['pesquisa_loc'] ?? '';
$resultadosPesquisa = [];

function normalizarTexto($texto)
{
    $texto = mb_strtolower(trim($texto));

    return str_replace(
        ['ã', 'á', 'à', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç', '_'],
        ['a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c', ' '],
        $texto
    );
}

if ($pesquisaLocalizacoes != '') {
    $pesquisaNormalizada = normalizarTexto($pesquisaLocalizacoes);

    foreach ($dados as $zona => $hospitais) {
        foreach ($hospitais as $hospital => $servicos) {
            foreach ($servicos as $servico => $linhas) {
                foreach ($linhas as $eq) {

                    if (empty($eq['id_equipamento'])) {
                        continue;
                    }

                    $textoPesquisa = normalizarTexto(
                        $eq['codigo_interno'] . ' ' .
                            $eq['designacao'] . ' ' .
                            $eq['marca'] . ' ' .
                            $eq['estado_atual'] . ' ' .
                            $zona . ' ' .
                            $hospital . ' ' .
                            $servico . ' ' .
                            $eq['sala'] . ' ' .
                            $eq['piso']
                    );

                    if (str_contains($textoPesquisa, $pesquisaNormalizada)) {
                        $resultadosPesquisa[] = $eq + [
                            'zona_nome' => $zona,
                            'hospital_nome' => $hospital,
                            'servico_nome' => $servico
                        ];
                    }
                }
            }
        }
    }
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

function mostrarHospital($dados, $zona, $hospital, $idHospital)
{
?>

    <div class="accordion-item">
        <h2 class="accordion-header" id="heading<?= $idHospital ?>">
            <?php
            $totalHospital = 0;

            if (isset($dados[$zona][$hospital])) {
                foreach ($dados[$zona][$hospital] as $servico => $linhas) {
                    foreach ($linhas as $eq) {
                        if (!empty($eq['id_equipamento'])) {
                            $totalHospital++;
                        }
                    }
                }
            }

            if ($totalHospital == 0) {
                $textoTotalHospital = 'Sem equipamentos';
            } elseif ($totalHospital == 1) {
                $textoTotalHospital = '1 equipamento';
            } else {
                $textoTotalHospital = $totalHospital . ' equipamentos';
            }
            ?>
            <button class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapse<?= $idHospital ?>">

                <?= htmlspecialchars($hospital) ?>

                <span class="total-hospital">
                    (<?= $textoTotalHospital ?>)
                </span>

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
                                            <?php
                                            $totalServico = 0;

                                            foreach ($linhas as $eq) {
                                                if (!empty($eq['id_equipamento'])) {
                                                    $totalServico++;
                                                }
                                            }

                                            if ($totalServico == 0) {
                                                $textoTotalServico = 'Sem equipamentos';
                                            } elseif ($totalServico == 1) {
                                                $textoTotalServico = '1 equipamento';
                                            } else {
                                                $textoTotalServico = $totalServico . ' equipamentos';
                                            }
                                            ?>
                                            <button class="accordion-button collapsed"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapseServico<?= $idHospital ?><?= $s ?>">

                                                <?= htmlspecialchars($servico) ?>

                                                <span class="total-servico">
                                                    (<?= $textoTotalServico ?>)
                                                </span>

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

                                                                    <?php
                                                                    $estado = $eq['estado_atual'];

                                                                    if ($estado == 'ativo') {
                                                                        $corEstado = '#198754';
                                                                    } elseif ($estado == 'em_manutencao') {
                                                                        $corEstado = '#d39e00';
                                                                    } elseif ($estado == 'inativo') {
                                                                        $corEstado = '#dc3545';
                                                                    } else {
                                                                        $corEstado = '#6c757d';
                                                                    }
                                                                    ?>

                                                                    <small>
                                                                        <?= htmlspecialchars($eq['marca']) ?> ·
                                                                        <span style="color: <?= $corEstado ?>; font-weight: 700;">
                                                                            <?= htmlspecialchars(str_replace('_', ' ', $estado)) ?>
                                                                        </span>
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
$totalZonas = count(array_filter($ordemZonas, fn($z) => $z !== 'Geral'));
$totalHospitais = 0;
$totalLocalizacoes = 0;
$totalEquipamentos = 0;
$equipamentosPorZona = [];

foreach ($dados as $zona => $hospitais) {
    $totalHospitais += count($hospitais);

    foreach ($hospitais as $hospital => $servicos) {
        foreach ($servicos as $servico => $linhas) {

            $totalLocalizacoes += count($linhas);

            foreach ($linhas as $linha) {
                if (!empty($linha['id_equipamento'])) {
                    $totalEquipamentos++;
                    $equipamentosPorZona[$zona] = ($equipamentosPorZona[$zona] ?? 0) + 1;
                }
            }
        }
    }
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

                    <?php if ($zona == 'Geral'): ?>
                        <form method="GET" class="pesquisa-localizacoes-form">
                            <!-- BARRA DE PESQUISA -->
                            <input
                                type="text"
                                name="pesquisa_loc"
                                class="form-control form-control-sm"
                                placeholder="Pesquisar equipamento, serviço, sala ou hospital..."
                                value="<?= htmlspecialchars($pesquisaLocalizacoes) ?>">

                            <button type="submit" class="btn btn-sm btn-pesquisar-localizacao">
                                Pesquisar
                            </button>

                            <a href="localizacoes.php" class="btn btn-sm btn-outline-secondary">
                                Limpar
                            </a>

                        </form>
                        <?php if ($pesquisaLocalizacoes != ''): ?>

                            <div class="resultados-localizacoes">

                                <h5 class="localizacoes-subtitulo">
                                    Resultados da pesquisa
                                </h5>

                                <?php if (!empty($resultadosPesquisa)): ?>

                                    <?php foreach ($resultadosPesquisa as $eq): ?>

                                        <div class="resultado-localizacao-card">

                                            <strong>
                                                <?= htmlspecialchars($eq['codigo_interno']) ?> —
                                                <?= htmlspecialchars($eq['designacao']) ?>
                                            </strong>

                                            <p>
                                                <?= htmlspecialchars($eq['hospital_nome']) ?> ·
                                                <?= htmlspecialchars($eq['servico_nome']) ?> ·
                                                Sala <?= htmlspecialchars($eq['sala']) ?> ·
                                                Piso <?= htmlspecialchars($eq['piso']) ?>
                                            </p>

                                            <small>
                                                Zona <?= htmlspecialchars($eq['zona_nome']) ?> ·
                                                <?= htmlspecialchars($eq['marca']) ?>
                                            </small>

                                        </div>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <p class="text-muted">
                                        Não foram encontrados equipamentos para essa pesquisa.
                                    </p>

                                <?php endif; ?>

                            </div>

                        <?php endif; ?>

                        <!-- CARDS EQ LOCALIZAÇOES RESUMO -->
                        <div class="resumo-localizacoes">

                            <div class="resumo-localizacao-card">
                                <h6>Zonas</h6>
                                <span><?= $totalZonas ?></span>
                            </div>

                            <div class="resumo-localizacao-card">
                                <h6>Hospitais</h6>
                                <span><?= $totalHospitais ?></span>
                            </div>

                            <div class="resumo-localizacao-card">
                                <h6>Localizações</h6>
                                <span><?= $totalLocalizacoes ?></span>
                            </div>

                            <div class="resumo-localizacao-card">
                                <h6>Equipamentos</h6>
                                <span><?= $totalEquipamentos ?></span>
                            </div>

                        </div>
                        <!-- CARDS EQ POR ZONAS -->
                        <div class="resumo-zonas">

                            <h5 class="localizacoes-subtitulo">Equipamentos por zona</h5>

                            <div class="resumo-localizacoes">

                                <?php foreach ($ordemZonas as $zonaResumo): ?>

                                    <?php if ($zonaResumo !== 'Geral'): ?>

                                        <div class="resumo-localizacao-card">
                                            <h6><?= htmlspecialchars($zonaResumo) ?></h6>
                                            <span><?= $equipamentosPorZona[$zonaResumo] ?? 0 ?></span>
                                        </div>

                                    <?php endif; ?>

                                <?php endforeach; ?>

                            </div>
                            <!-- GRAFICO BARRAS EQ POR ZONAS -->
                        </div>
                        <h5 class="localizacoes-subtitulo">Distribuição de equipamentos por zona</h5>
                        <div class="grafico-localizacoes-card">
                            <canvas id="graficoEquipamentosZona" height="70"></canvas>
                        </div>


                    <?php elseif ($zona !== 'Geral'): ?>

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
<style>
    /*BARRA PESQUISA*/
    .pesquisa-localizacoes-form {
        display: flex;
        gap: 8px;
        margin-bottom: 25px;
        max-width: 900px;
    }

    .pesquisa-localizacoes-form input {
        flex: 1;
    }

    .btn-pesquisar-localizacao {
        background-color: #073a52;
        border-color: #073a52;
        color: white;
    }

    .btn-pesquisar-localizacao:hover {
        background-color: #0b5779;
        border-color: #0b5779;
        color: white;
    }

    .resultados-localizacoes {
        margin-bottom: 30px;
    }

    .resultado-localizacao-card {
        background: #ffffff;
        border: 1px solid #dde7ec;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 10px;
    }

    .resultado-localizacao-card strong {
        color: #073a52;
        font-weight: 700;
    }

    .resultado-localizacao-card p {
        margin: 6px 0 2px;
        color: #1a2c35;
    }

    .resultado-localizacao-card small {
        color: #6c757d;
    }


    .resumo-localizacoes {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }

    .resumo-localizacao-card {
        background: #ffffff;
        border: 1px solid #dde7ec;
        border-radius: 12px;
        padding: 18px 20px;
    }

    .resumo-localizacao-card h6 {
        color: #6c757d;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .resumo-localizacao-card span {
        color: #073a52;
        font-size: 2rem;
        font-weight: 700;
    }

    .localizacoes-subtitulo {
        color: #073a52;
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 15px;


        display: inline-block;
    }

    .grafico-localizacoes-card {
        background: #ffffff;
        border: 1px solid #dde7ec;
        border-radius: 14px;
        padding: 20px;
        margin-top: 25px auto 0;
        max-width: 700px;
    }

    .grafico-localizacoes-card h5 {
        color: #073a52;
        font-weight: 700;
        margin-bottom: 18px;
    }

    .total-hospital {
        margin-left: 8px;
        font-weight: 600;
        color: #6c757d;
        font-size: 0.9rem;
    }
    .total-servico {
    margin-left: 8px;
    font-weight: 600;
    color: #6c757d;
    font-size: 0.85rem;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctxEquipamentosZona = document.getElementById('graficoEquipamentosZona');

    if (ctxEquipamentosZona) {
        new Chart(ctxEquipamentosZona, {
            type: 'bar',
            data: {
                labels: [
                    <?php foreach ($ordemZonas as $zonaGrafico): ?>
                        <?php if ($zonaGrafico !== 'Geral'): ?> "<?= $zonaGrafico ?>",
                        <?php endif; ?>
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Equipamentos',
                    backgroundColor: '#073a52',
                    borderColor: '#073a52',
                    borderWidth: 1,
                    borderRadius: 6,
                    data: [
                        <?php foreach ($ordemZonas as $zonaGrafico): ?>
                            <?php if ($zonaGrafico !== 'Geral'): ?>
                                <?= $equipamentosPorZona[$zonaGrafico] ?? 0 ?>,
                            <?php endif; ?>
                        <?php endforeach; ?>
                    ],
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
</script>
<?php include 'includes/footer.php'; ?>