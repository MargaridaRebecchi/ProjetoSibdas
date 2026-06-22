<?php
require_once 'includes/auth.php';
include 'includes/db.php';

$total = $conn->query("SELECT COUNT(*) AS total FROM contratos_garantias")->fetch_assoc()['total'];

$ativos = $conn->query("
    SELECT COUNT(*) AS total 
    FROM contratos_garantias 
    WHERE data_fim >= CURDATE()
")->fetch_assoc()['total'];

$aExpirar = $conn->query("
    SELECT COUNT(*) AS total 
    FROM contratos_garantias 
    WHERE data_fim BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
")->fetch_assoc()['total'];

$expirados = $conn->query("
    SELECT COUNT(*) AS total 
    FROM contratos_garantias 
    WHERE data_fim < CURDATE()
")->fetch_assoc()['total'];

$alertas = $conn->query("
    SELECT 
        cg.*,
        e.codigo_interno,
        e.designacao,
        e.marca,
        e.modelo,
        DATEDIFF(cg.data_fim, CURDATE()) AS dias_restantes
    FROM contratos_garantias cg
    INNER JOIN equipamentos e
        ON cg.id_equipamento = e.id_equipamento
    WHERE cg.data_fim <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY cg.data_fim ASC
");

$contratos = $conn->query("
    SELECT 
        cg.*,
        e.codigo_interno,
        e.designacao,
        e.marca,
        e.modelo,
       d.nome_documento,
       d.caminho_ficheiro,
(
    SELECT COUNT(*)
    FROM contratos_garantias cg2
    WHERE cg2.id_equipamento = e.id_equipamento
) AS total_contratos_equipamento
    FROM contratos_garantias cg
    INNER JOIN equipamentos e
        ON cg.id_equipamento = e.id_equipamento
    LEFT JOIN documentos_equipamento d
        ON d.id_contrato = cg.id_contrato
    ORDER BY e.codigo_interno ASC, cg.data_fim ASC
");

include 'includes/header.php';
include 'includes/nav.php';
?>

<main class="private-main">

    <section class="private-header">
        <div>
            <h1>Contratos e Garantias</h1>
        </div>
    </section>

    <section class="contratos-container">

        <div class="resumo-contratos">

            <div class="resumo-contrato-card">
                <span><?= $total ?></span>
                <h6>Total registado</h6>
            </div>

            <div class="resumo-contrato-card">
                <span><?= $ativos ?></span>
                <h6>Ativos</h6>
            </div>

            <div class="resumo-contrato-card">
                <span><?= $aExpirar ?></span>
                <h6>A expirar em menos de 30 dias</h6>
            </div>

            <div class="resumo-contrato-card">
                <span><?= $expirados ?></span>
                <h6>Expirados</h6>
            </div>

        </div>

        <h5 class="contratos-subtitulo">Equipamentos com atenção necessária</h5>

        <div class="alertas-contratos">

            <?php if ($alertas && $alertas->num_rows > 0): ?>

                <?php while ($a = $alertas->fetch_assoc()): ?>

                    <?php
                    $dias = (int)$a['dias_restantes'];

                    if ($dias < 0) {
                        $textoEstado = 'Expirado há ' . abs($dias) . ' dias';
                        $classeEstado = 'contrato-expirado';
                    } elseif ($dias == 0) {
                        $textoEstado = 'Expira hoje';
                        $classeEstado = 'contrato-expirar';
                    } else {
                        $textoEstado = 'Expira em ' . $dias . ' dias';
                        $classeEstado = 'contrato-expirar';
                    }
                    ?>

                    <div class="alerta-contrato-card">

                        <div>
                            <strong>
                                <?= htmlspecialchars($a['codigo_interno']) ?> -
                                <?= htmlspecialchars($a['designacao']) ?>
                            </strong>
                            <small>
                                <?= htmlspecialchars($a['marca']) ?>
                                <?= htmlspecialchars($a['modelo']) ?>
                            </small>
                        </div>

                        <span class="badge badge-contrato">
                            <?php
                            $tipoFormatado = match ($a['tipo']) {
                                'contrato_manutencao' => 'Contrato Manutenção',
                                'garantia' => 'Garantia',
                                'aluguer' => 'Aluguer',
                                default => 'Outro'
                            };
                            ?>

                            <?= $tipoFormatado ?>
                        </span>

                        <span class="<?= $classeEstado ?>">
                            <?= $textoEstado ?>
                        </span>

                        <small>
                            Fim: <?= htmlspecialchars($a['data_fim']) ?>
                        </small>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="text-muted py-3">
                    Não existem contratos ou garantias em situação crítica.
                </div>

            <?php endif; ?>

        </div>

        <h5 class="contratos-subtitulo mt-4">Contratos e garantias por equipamento</h5>

        <div class="filtros-contratos">
            <button type="button" class="filtro-contrato ativo" data-filtro="todos">
                Todos
            </button>

            <button type="button" class="filtro-contrato" data-filtro="ativo">
                Ativos
            </button>

            <button type="button" class="filtro-contrato" data-filtro="expirar">
                A expirar
            </button>

            <button type="button" class="filtro-contrato" data-filtro="expirado">
                Expirados
            </button>
        </div>
        <div class="accordion contratos-accordion" id="accordionContratos">

            <?php if ($contratos && $contratos->num_rows > 0): ?>

                <?php
                $equipamentoAtual = null;
                $contador = 0;
                ?>

                <?php while ($c = $contratos->fetch_assoc()): ?>

                    <?php if ($equipamentoAtual !== $c['id_equipamento']): ?>

                        <?php if ($equipamentoAtual !== null): ?>
        </div>
        </div>
        </div>
    <?php endif; ?>

    <?php
                        $equipamentoAtual = $c['id_equipamento'];
                        $contador++;
    ?>

    <div class="accordion-item mb-3 contrato-equipamento-grupo">
        <h2 class="accordion-header" id="headingContrato<?= $contador ?>">
            <button class="accordion-button collapsed"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#collapseContrato<?= $contador ?>">

                <div class="contrato-accordion-titulo">
                    <span>
                        <?= htmlspecialchars($c['codigo_interno']) ?> -
                        <?= htmlspecialchars($c['designacao']) ?>
                        <?= htmlspecialchars($c['marca']) ?>
                        <?= htmlspecialchars($c['modelo']) ?>
                    </span>

                    <small>
                        <?= $c['total_contratos_equipamento'] ?>
                        documento<?= $c['total_contratos_equipamento'] == 1 ? '' : 's' ?> associado<?= $c['total_contratos_equipamento'] == 1 ? '' : 's' ?>
                    </small>
                </div>

            </button>
        </h2>

        <div id="collapseContrato<?= $contador ?>"
            class="accordion-collapse collapse"
            data-bs-parent="#accordionContratos">

            <div class="accordion-body">
            <?php endif; ?>

            <?php
                    if ($c['data_fim'] < date('Y-m-d')) {
                        $estado = 'Expirado';
                        $classeEstado = 'contrato-expirado';
                    } elseif ($c['data_fim'] <= date('Y-m-d', strtotime('+30 days'))) {
                        $estado = 'A expirar';
                        $classeEstado = 'contrato-expirar';
                    } else {
                        $estado = 'Ativo';
                        $classeEstado = 'contrato-ativo';
                    }
            ?>

            <?php

                    $dias = floor(
                        (strtotime($c['data_fim']) - strtotime(date('Y-m-d')))
                            / 86400
                    );

                    if ($dias < 0) {
                        $textoPrazo = 'Expirado há ' . abs($dias) . ' dias';
                    } elseif ($dias == 0) {
                        $textoPrazo = 'Expira hoje';
                    } else {
                        $textoPrazo = 'Expira em ' . $dias . ' dias';
                    }

            ?>

           <div class="contrato-card" data-estado="<?= $classeEstado ?>">

                <div class="contrato-card-topo">

                    <?php
                    $tipoFormatado = match ($c['tipo']) {
                        'contrato_manutencao' => 'Contrato Manutenção',
                        'garantia' => 'Garantia',
                        'aluguer' => 'Aluguer',
                        default => 'Outro'
                    };
                    ?>

                    <span class="badge badge-contrato">
                        <?= $tipoFormatado ?>
                    </span>

                </div>
                <div class="contrato-card-entidade">

                    <?= $c['entidade_responsavel']
                        ? htmlspecialchars($c['entidade_responsavel'])
                        : 'Sem entidade responsável' ?>

                </div>

                <div class="contrato-card-datas">

                    <div>
                        <small>Início</small>
                        <strong><?= htmlspecialchars($c['data_inicio']) ?></strong>
                    </div>

                    <div>
                        <small>Fim</small>
                        <strong><?= htmlspecialchars($c['data_fim']) ?></strong>
                    </div>

                </div>

                <div class="contrato-card-prazo">
                    <?= $textoPrazo ?>
                </div>

                <div class="contrato-card-acoes">

                    <span class="<?= $classeEstado ?>">
                        <?= $estado ?>
                    </span>

                    <?php if (!empty($c['caminho_ficheiro'])): ?>

                        <a href="<?= htmlspecialchars($c['caminho_ficheiro']) ?>"
                            target="_blank"
                            class="btn btn-sm btn-outline-secondary">

                            Ver documento

                        </a>

                    <?php else: ?>

                        <span class="text-muted small">Sem documento</span>

                    <?php endif; ?>

                </div>

            </div>

        <?php endwhile; ?>

            </div>
        </div>
    </div>

<?php else: ?>

    <div class="text-center text-muted py-4">
        Ainda não existem contratos ou garantias registados.
    </div>

<?php endif; ?>

</div>

    </section>

</main>

<?php include 'includes/footer.php'; ?>