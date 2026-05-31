<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>
<?php include 'includes/db.php'; ?>


<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $erros = [];

    // Código interno: só números e pontos
    if (!preg_match('/^[0-9]{2}\.[0-9]{3}$/', $_POST['codigo_interno'])) {
        $erros[] = "O código interno deve estar no formato xx.xxx. Exemplo: 01.022";
    }

    // Data aquisição não pode ser depois de hoje
    if ($_POST['data_aquisicao'] > date('Y-m-d')) {
        $erros[] = "A data de aquisição não pode ser posterior à data de hoje.";
    }

    // Localização obrigatória
    if (
        empty($_POST['hospital']) ||
        empty($_POST['edificio']) ||
        empty($_POST['piso']) ||
        empty($_POST['sala'])
    ) {
        $erros[] = "A localização deve ter hospital, edifício, piso e sala.";
    }

    // Piso só números
    if (!preg_match('/^[0-9]+$/', $_POST['piso'])) {
        $erros[] = "O piso deve conter apenas números.";
    }

    //Ano de fabrico com 4 numeros e inferior a 2026
    if (!preg_match('/^[0-9]{4}$/', $_POST['ano_fabrico'])) {
        $erros[] = "O ano de fabrico deve ter exatamente 4 números.";
    } elseif ((int)$_POST['ano_fabrico'] > 2026) {
        $erros[] = "O ano de fabrico não pode ser superior a 2026.";
    }
    if (empty($erros)) {

        // 1. Inserir localização
        $sqlLoc = "INSERT INTO localizacoes_ (hospital, edificio, piso, sala)
                   VALUES (?, ?, ?, ?)";

        $stmtLoc = $conn->prepare($sqlLoc);

        $stmtLoc->bind_param(
            "ssis",
            $_POST['hospital'],
            $_POST['edificio'],
            $_POST['piso'],
            $_POST['sala']
        );

        if ($stmtLoc->execute()) {

            $id_localizacao = $stmtLoc->insert_id;

            // 2. Inserir equipamento
            $sql = "INSERT INTO equipamentos 
                (codigo_interno, designacao, categoria, marca, modelo, numero_serie, fabricante, id_localizacao, data_aquisicao, ano_fabrico, custo_aquisicao, tipo_entrada, estado_atual, criticidade)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "sssssssissdsss",
                $_POST['codigo_interno'],
                $_POST['designacao'],
                $_POST['categoria'],
                $_POST['marca'],
                $_POST['modelo'],
                $_POST['numero_serie'],
                $_POST['fabricante'],
                $id_localizacao,
                $_POST['data_aquisicao'],
                $_POST['ano_fabrico'],
                $_POST['custo_aquisicao'],
                $_POST['tipo_entrada'],
                $_POST['estado_atual'],
                $_POST['criticidade']
            );

            if ($stmt->execute()) {
                header("Location: gestao_equipamentos.php");
                exit();
            } else {
                $erros[] = "Erro ao adicionar equipamento: " . $stmt->error;
            }
        } else {
            $erros[] = "Erro ao adicionar localização: " . $stmtLoc->error;
        }
    }
}
?>

<main class="private-main">

    <section class="private-header">
        <h1>Adicionar Equipamento</h1>
    </section>

    <section class="private-card">

        <?php if (!empty($erros)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($erros as $erro): ?>
                        <li><?= htmlspecialchars($erro) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST">

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Código interno</label>

                    <input
                        type="text"
                        name="codigo_interno"
                        class="form-control form-control-sm"
                        pattern="[0-9]{2}\.[0-9]{3}"
                        maxlength="6"
                        placeholder="Ex: 01.022"
                        required>

                    <div class="invalid-feedback">
                        O código deve estar no formato xx.xxx (ex: 01.022).
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Designação</label>
                    <input type="text" name="designacao" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Categoria</label>
                    <select name="categoria" class="form-select form-select-sm" required>
                        <option value="" selected disabled>Escolha uma categoria</option>
                        <option value="monitorizacao">Monitorização</option>
                        <option value="suporte_vida">Suporte de vida</option>
                        <option value="terapia">Terapia</option>
                        <option value="diagnostico">Diagnóstico</option>
                        <option value="laboratorio">Laboratório</option>
                        <option value="esterilizacao">Esterilização</option>
                        <option value="reabilitacao">Reabilitação</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Marca</label>
                    <input type="text" name="marca" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Modelo</label>
                    <input type="text" name="modelo" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Número de série</label>
                    <input type="text" name="numero_serie" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Fabricante</label>
                    <input type="text" name="fabricante" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Hospital</label>
                    <input type="text" name="hospital" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Edifício</label>
                    <input type="text" name="edificio" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Piso</label>
                    <input type="number" name="piso" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Sala</label>
                    <input type="text" name="sala" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Data de aquisição</label>

                    <input
                        type="date"
                        name="data_aquisicao"
                        class="form-control form-control-sm"
                        max="<?= date('Y-m-d') ?>"
                        required>

                    <div class="invalid-feedback">
                        A data não pode ser posterior à de hoje.
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Ano de fabrico</label>

                    <input
                        type="text"
                        name="ano_fabrico"
                        class="form-control form-control-sm"
                        pattern="[0-9]{4}"
                        maxlength="4"
                        max="2026"
                        placeholder="Ex: 2022"
                        required>

                    <div class="invalid-feedback">
                        Introduza 4 números e um ano não superior a 2026.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Custo de aquisição</label>
                    <input type="number" step="0.01" name="custo_aquisicao" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Tipo de entrada</label>
                    <select name="tipo_entrada" class="form-select form-select-sm" required>
                        <option value="" selected disabled>Escolha o tipo de entrada</option>
                        <option value="compra">Compra</option>
                        <option value="doacao">Doação</option>
                        <option value="aluguer">Aluguer</option>
                        <option value="emprestimo">Empréstimo</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Estado atual</label>
                    <select name="estado_atual" class="form-select form-select-sm" required>
                        <option value="" selected disabled>Escolha o estado</option>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                        <option value="em_manutencao">Em manutenção</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Criticidade</label>
                    <select name="criticidade" class="form-select form-select-sm" required>
                        <option value="" selected disabled>Escolha a criticidade</option>
                        <option value="baixa">Baixa</option>
                        <option value="media">Média</option>
                        <option value="alta">Alta</option>
                        <option value="suporte_vida">Suporte de vida</option>
                    </select>
                </div>

            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn-primario">
                    Adicionar equipamento
                </button>

                <a href="gestao_equipamentos.php" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>

        </form>

    </section>

</main>

<script>

document.querySelectorAll('input, select').forEach(campo => {

    campo.addEventListener('blur', function () {

        this.classList.remove('is-valid');
        this.classList.remove('is-invalid');

        // Ano de fabrico
        if (this.name === 'ano_fabrico') {

            const ano = parseInt(this.value);

            if (
                !/^[0-9]{4}$/.test(this.value) ||
                ano > 2026
            ) {
                this.setCustomValidity('erro');
            } else {
                this.setCustomValidity('');
            }
        }

        // Código interno
        if (this.name === 'codigo_interno') {

            if (!/^[0-9]{2}\.[0-9]{3}$/.test(this.value)) {
                this.setCustomValidity('erro');
            } else {
                this.setCustomValidity('');
            }
        }

        if (!this.checkValidity()) {
            this.classList.add('is-invalid');
        } else {
            this.classList.add('is-valid');
        }

    });

});

</script>
</script>

<?php include 'includes/footer.php'; ?>