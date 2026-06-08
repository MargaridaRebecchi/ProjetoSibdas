<?php
include 'includes/db.php';

$sql = "SELECT 
            l.*,
            COUNT(e.id_equipamento) AS total_equipamentos
        FROM localizacoes_ l
        LEFT JOIN equipamentos e 
            ON l.id_localizacao = e.id_localizacao
        GROUP BY l.id_localizacao
        ORDER BY l.zona, l.hospital, l.piso, l.sala";

$result = $conn->query($sql);

$localizacoesPorZona = [];

while ($row = $result->fetch_assoc()) {
    $zona = $row['zona'] ?? 'Sem zona';
    $localizacoesPorZona[$zona][] = $row;
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

        <?php
$ordemZonas = [
    'Geral',
    'Norte',
    'Centro',
    'Lisboa e Vale do Tejo',
    'Algarve'
];
?>

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

        </div>

    <?php endforeach; ?>

</div>

    </section>

</main>

<?php include 'includes/footer.php'; ?>