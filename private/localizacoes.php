<?php
include 'includes/db.php';

/*Inserir localizações */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_localizacao'])) {

    $zona = trim($_POST['zona']);
    $hospital = trim($_POST['hospital']);
    $edificio = trim($_POST['edificio']);
    $piso = trim($_POST['piso']);
    $servico = trim($_POST['servico']);
    $sala = trim($_POST['sala']);

    if ($zona !== '' && $hospital !== '') {

        /* Verificar se a localização já existe */
        $stmtExiste = $conn->prepare("
        SELECT id_localizacao
        FROM localizacoes_
        WHERE LOWER(TRIM(zona)) = LOWER(TRIM(?))
          AND LOWER(TRIM(hospital)) = LOWER(TRIM(?))
          AND LOWER(TRIM(COALESCE(edificio, ''))) = LOWER(TRIM(?))
          AND LOWER(TRIM(COALESCE(piso, ''))) = LOWER(TRIM(?))
          AND LOWER(TRIM(COALESCE(servico, ''))) = LOWER(TRIM(?))
          AND LOWER(TRIM(COALESCE(sala, ''))) = LOWER(TRIM(?))
        LIMIT 1
    ");

        $stmtExiste->bind_param(
            "ssssss",
            $zona,
            $hospital,
            $edificio,
            $piso,
            $servico,
            $sala
        );

        $stmtExiste->execute();
        $resultadoExiste = $stmtExiste->get_result();

        if ($resultadoExiste->num_rows > 0) {
            header("Location: localizacoes.php?localizacao_duplicada=1");
            exit;
        }

        /* Verificar se o hospital já existe noutra zona */
        $stmtHospital = $conn->prepare("
        SELECT zona
        FROM localizacoes_
        WHERE LOWER(TRIM(hospital)) = LOWER(TRIM(?))
          AND LOWER(TRIM(zona)) <> LOWER(TRIM(?))
        LIMIT 1
    ");

        $stmtHospital->bind_param("ss", $hospital, $zona);
        $stmtHospital->execute();

        $resultadoHospital = $stmtHospital->get_result();

        if ($resultadoHospital->num_rows > 0) {
            header("Location: localizacoes.php?hospital_zona_incorreta=1");
            exit;
        }

        /* Inserir localização */
        $stmt = $conn->prepare("
        INSERT INTO localizacoes_
        (zona, hospital, edificio, piso, servico, sala)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

        $stmt->bind_param(
            "ssssss",
            $zona,
            $hospital,
            $edificio,
            $piso,
            $servico,
            $sala
        );

        $stmt->execute();

        header("Location: localizacoes.php?localizacao_adicionada=1");
        exit;
    }
}
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

$hospitaisPorZona = [];

foreach ($dados as $zonaDados => $hospitais) {
    if ($zonaDados !== 'Geral') {
        $hospitaisPorZona[$zonaDados] = array_keys($hospitais);
        sort($hospitaisPorZona[$zonaDados]);
    }
}

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

            <li class="nav-item" role="presentation">
                <button
                    class="nav-link"
                    data-bs-toggle="tab"
                    data-bs-target="#adicionar-localizacao"
                    type="button"
                    role="tab">
                    Adicionar localização
                </button>

            </li>

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

                                <h5 class="contratos-subtitulo mt-4">Resultados da pesquisa</h5>

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

                            <h5 class="contratos-subtitulo mt-4">Equipamentos por zona</h5>

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
                        <h5 class="contratos-subtitulo mt-4">Distribuição de equipamentos por zona</h5>
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
            <!--Adicionar Localização -->
            <div
                class="tab-pane fade"
                id="adicionar-localizacao"
                role="tabpanel">

                <form method="POST" class="form-adicionar-localizacao">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Zona *</label>
                            <select name="zona" class="form-select form-select-sm" required>
                                <option value="">Selecionar zona</option>
                                <option value="Norte">Norte</option>
                                <option value="Centro">Centro</option>
                                <option value="Lisboa e Vale do Tejo">Lisboa e Vale do Tejo</option>
                                <option value="Algarve">Algarve</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Hospital *</label>
                            <input type="text" name="hospital" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Edifício *</label>
                            <input type="text" name="edificio" class="form-control form-control-sm required">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Piso *</label>
                            <input type="text" name="piso" class="form-control form-control-sm required">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Serviço *</label>
                            <input type="text" name="servico" class="form-control form-control-sm required">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Sala *</label>
                            <input type="text" name="sala" class="form-control form-control-sm required">
                        </div>

                    </div>

                    <button type="submit" name="adicionar_localizacao" class="btn-primario">
                        Adicionar localização
                    </button>

                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="preencherTesteLocalizacao()">
                        Preencher teste
                    </button>

                </form>

            </div>
        </div>

    </section>


</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const dadosGraficoZona = {
        labels: [
            <?php foreach ($ordemZonas as $zonaGrafico): ?>
                <?php if ($zonaGrafico !== 'Geral'): ?> "<?= $zonaGrafico ?>",
                <?php endif; ?>
            <?php endforeach; ?>
        ],
        valores: [
            <?php foreach ($ordemZonas as $zonaGrafico): ?>
                <?php if ($zonaGrafico !== 'Geral'): ?>
                    <?= $equipamentosPorZona[$zonaGrafico] ?? 0 ?>,
                <?php endif; ?>
            <?php endforeach; ?>
        ]
    };
</script>

<!-- Modal localização adicionada -->
<div class="modal fade" id="modalLocalizacaoAdicionada" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Sucesso</h5>
            </div>

            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p class="mb-0">Localização adicionada com sucesso!</p>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Modal localização duplicada -->
<div class="modal fade" id="modalLocalizacaoDuplicada" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-danger">Localização já existente</h5>
            </div>

            <div class="modal-body text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <p class="mb-0">Já existe uma localização com os mesmos dados.</p>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Modal erro hospital noutra zona -->
<div class="modal fade" id="modalHospitalZonaIncorreta" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-danger">Zona incorreta</h5>
            </div>

            <div class="modal-body text-center">
                <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                <p class="mb-0">
                    Este hospital já está registado noutra zona. Confirme a zona antes de adicionar a localização.
                </p>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
const hospitaisZonas = {
    <?php
    $paresHospitalZona = [];

    foreach ($dados as $zonaDados => $hospitais) {
        foreach ($hospitais as $hospitalNome => $servicos) {
            $paresHospitalZona[$hospitalNome] = $zonaDados;
        }
    }

    foreach ($paresHospitalZona as $hospitalNome => $zonaNome):
    ?>
        "<?= htmlspecialchars($hospitalNome, ENT_QUOTES) ?>": "<?= htmlspecialchars($zonaNome, ENT_QUOTES) ?>",
    <?php endforeach; ?>
};
</script>
<?php include 'includes/footer.php'; ?>