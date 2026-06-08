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
        empty($_POST['zona']) ||
        empty($_POST['hospital']) ||
        empty($_POST['edificio']) ||
        empty($_POST['piso']) ||
        empty($_POST['sala'])
    ) {
        $erros[] = "A localização deve ter zona, hospital, edifício, piso e sala.";
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
        $sqlLoc = "INSERT INTO localizacoes_ (zona, hospital, edificio, piso, sala)
                   VALUES (?, ?, ?, ?, ?)";

        $stmtLoc = $conn->prepare($sqlLoc);

        $stmtLoc->bind_param(
            "ssis",
            $_POST['zona'],
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

            try {
                $stmt->execute();

                header("Location: adicionar_equipamento.php?sucesso=1");
                exit();
            } catch (mysqli_sql_exception $e) {

                if ($e->getCode() == 1062) {
                    $erroModal = "Já existe um equipamento com esse código interno ou número de série.";
                } else {
                    $erroModal = "Erro ao adicionar equipamento: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/nav.php'; ?>

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
                    <label>Zona</label>
                    <select name="zona" class="form-select form-select-sm" required>
                        <option value="" selected disabled>Escolha uma zona</option>
                        <option value="Norte">Norte</option>
                        <option value="Centro">Centro</option>
                        <option value="Lisboa e Vale do Tejo">Lisboa e Vale do Tejo</option>
                        <option value="Alentejo">Alentejo</option>
                        <option value="Algarve">Algarve</option>
                    </select>
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
                    <label>Serviço</label>
                    <input type="text" name="servico" class="form-control form-control-sm" required>
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

            <div class="mt-3 d-flex align-items-center">

                <button type="submit" class="btn-primario">
                    Adicionar equipamento
                </button>

                <a href="gestao_equipamentos.php" class="btn btn-secondary ms-2">
                    Cancelar
                </a>

                <button type="button"
                    class="btn btn-sm btn-outline-secondary ms-auto"
                    onclick="preencherTeste()">
                    Preencher teste
                </button>

            </div>


        </form>

    </section>

</main>
<div class="modal fade" id="modalErroAdicionar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header justify-content-center">
                <h5 class="modal-title text-danger text-center">
                    Erro ao adicionar equipamento
                </h5>
            </div>

            <div class="modal-body text-center">
                <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>

                <p class="mb-0">
                    <?= htmlspecialchars($erroModal ?? '') ?>
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

<div class="modal fade" id="modalSucessoAdicionar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header justify-content-center">
                <h5 class="modal-title text-success text-center">
                    Equipamento adicionado
                </h5>
            </div>

            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                <p class="mb-0">
                    Equipamento adicionado com sucesso!
                </p>
            </div>

            <div class="modal-footer justify-content-center">
                <a href="gestao_equipamentos.php"
                    class="btn btn-success">
                    Fechar
                </a>
            </div>

        </div>
    </div>
</div>

<?php if (isset($_GET['sucesso'])): ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const modalSucesso = new bootstrap.Modal(
                document.getElementById('modalSucessoAdicionar')
            );

            modalSucesso.show();

        });
    </script>

<?php endif; ?>

<?php if (isset($erroModal)): ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalErro = new bootstrap.Modal(
                document.getElementById('modalErroAdicionar')
            );

            modalErro.show();
        });
    </script>

<?php endif; ?>

<script>
    document.querySelectorAll('input, select').forEach(campo => {

        campo.addEventListener('blur', function() {

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

    function preencherTeste() {

        document.querySelector('[name="codigo_interno"]').value = '99.999';
        document.querySelector('[name="designacao"]').value = 'Equipamento de Teste';
        document.querySelector('[name="categoria"]').value = 'monitorizacao';
        document.querySelector('[name="marca"]').value = 'Philips';
        document.querySelector('[name="modelo"]').value = 'Modelo Teste';
        document.querySelector('[name="numero_serie"]').value = 'TESTE-' + Date.now();
        document.querySelector('[name="fabricante"]').value = 'Philips';

        document.querySelector('[name="hospital"]').value = 'Hospital São João';
        document.querySelector('[name="edificio"]').value = 'Edifício Principal';
        document.querySelector('[name="piso"]').value = '2';
        document.querySelector('[name="sala"]').value = '201';

        document.querySelector('[name="data_aquisicao"]').value = '2024-01-15';
        document.querySelector('[name="ano_fabrico"]').value = '2023';
        document.querySelector('[name="custo_aquisicao"]').value = '1500.00';

        document.querySelector('[name="tipo_entrada"]').value = 'compra';
        document.querySelector('[name="estado_atual"]').value = 'ativo';
        document.querySelector('[name="criticidade"]').value = 'media';
    }
</script>


<?php include 'includes/footer.php'; ?>