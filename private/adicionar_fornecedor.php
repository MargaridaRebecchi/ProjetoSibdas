<?php
include 'includes/db.php';

$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome_empresa = trim($_POST['nome_empresa']);
    $nif = trim($_POST['nif']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);
    $morada = trim($_POST['morada']);
    $website = trim($_POST['website']);
    $pessoa_contacto = trim($_POST['pessoa_contacto']);
    $telefone_contacto = trim($_POST['telefone_contacto']);
    $tipo_fornecedor = $_POST['tipo_fornecedor'];
    $observacoes = trim($_POST['observacoes']);

    if (
        $nome_empresa == '' || $nif == '' || $telefone == '' || $email == '' ||
        $morada == '' || $website == '' || $pessoa_contacto == '' ||
        $telefone_contacto == '' || $tipo_fornecedor == '' 
    ) {
        
    } elseif (!preg_match('/^[0-9]{9}$/', $nif)) {
        $erro = "O NIF deve ter exatamente 9 dígitos.";
    } elseif (!preg_match('/^[0-9]{9}$/', $telefone)) {
        $erro = "O telefone deve ter exatamente 9 dígitos.";
    } elseif (!preg_match('/^[0-9]{9}$/', $telefone_contacto)) {
        $erro = "O telefone da pessoa de contacto deve ter exatamente 9 dígitos.";
    } elseif (!preg_match('/^[A-Za-zÀ-ÿ\s]+$/', $pessoa_contacto)) {
        $erro = "A pessoa de contacto deve conter apenas letras.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "O email não é válido.";
    } else {

        $sql = "INSERT INTO fornecedores
                (nome_empresa, nif, telefone, email, morada, website,
                 pessoa_contacto, telefone_contacto, tipo_fornecedor, observacoes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssssssssss",
            $nome_empresa,
            $nif,
            $telefone,
            $email,
            $morada,
            $website,
            $pessoa_contacto,
            $telefone_contacto,
            $tipo_fornecedor,
            $observacoes
        );

        try {
            $stmt->execute();

            header("Location: adicionar_fornecedor.php?sucesso=1");
            exit();
        } catch (mysqli_sql_exception $e) {

            if ($e->getCode() == 1062) {
                $erroModal = "Esse fornecedor já existe.";
            } else {
                $erroModal = "Erro ao adicionar fornecedor: " . $e->getMessage();
            }
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
            <h1>Adicionar Fornecedor</h1>
        </div>
    </section>

    <section class="private-card">

        <?php if ($erro != ''): ?>
            <div class="alert alert-danger text-center">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="formFornecedor" novalidate>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label>Nome da empresa</label>
                    <input type="text" name="nome_empresa" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>NIF</label>
                    <input type="text" name="nif" class="form-control form-control-sm" maxlength="9" required>
                    <div class="invalid-feedback">
                        O NIF deve ter exatamente 9 dígitos.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Telefone</label>
                    <input type="text" name="telefone" class="form-control form-control-sm" maxlength="9" required>
                    <div class="invalid-feedback">
                        O telefone deve ter exatamente 9 dígitos.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Email</label>
                    <input type="text" name="email" class="form-control form-control-sm" required>
                    <div class="invalid-feedback">
                        Introduza um email válido.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Morada</label>
                    <input type="text" name="morada" class="form-control form-control-sm" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Website</label>
                  <input type="text" name="website" class="form-control form-control-sm" required>
                <div class="invalid-feedback">
                    Introduza um website válido.
                </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Pessoa de contacto</label>
                    <input type="text" name="pessoa_contacto" class="form-control form-control-sm" required>
                    <div class="invalid-feedback">
                        A pessoa de contacto deve conter apenas letras.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Telefone da pessoa de contacto</label>
                    <input type="text" name="telefone_contacto" class="form-control form-control-sm" maxlength="9" required>
                    <div class="invalid-feedback">
                        O telefone da pessoa de contacto deve ter exatamente 9 dígitos.
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Tipo de fornecedor</label>
                    <select name="tipo_fornecedor" class="form-select form-select-sm" required>
                        <option value="" selected disabled>Escolha o tipo</option>
                        <option value="fabricante">Fabricante</option>
                        <option value="distribuidor">Distribuidor</option>
                        <option value="fornecedor_consumiveis">Fornecedor de consumíveis</option>
                        <option value="assistencia_tecnica">Assistência técnica</option>
                    </select>
                </div>

                <div class="col-md-12 mb-3">
                    <label>Observações</label>
                    <textarea name="observacoes" class="form-control form-control-sm" rows="3" ></textarea>
                </div>

            </div>

            <div class="mt-3 d-flex align-items-center">

                <button type="submit" class="btn-primario">
                    Adicionar fornecedor
                </button>

                <a href="fornecedores.php" class="btn btn-secondary ms-2">
                    Cancelar
                </a>

                <button type="button"
                    class="btn btn-sm btn-outline-secondary ms-auto"
                    onclick="preencherTesteFornecedor()">
                    Preencher teste
                </button>

            </div>

        </form>

    </section>

</main>

</body>
<!--Modal sucesso -->
<div class="modal fade" id="modalSucessoFornecedor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header justify-content-center">
                <h5 class="modal-title text-success text-center">
                    Fornecedor adicionado
                </h5>
            </div>

            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                <p class="mb-0">
                    Fornecedor adicionado com sucesso!
                </p>
            </div>

            <div class="modal-footer justify-content-center">
                <a href="fornecedores.php" class="btn btn-success">
                    Fechar
                </a>
            </div>

        </div>
    </div>
</div>

<?php if (isset($_GET['sucesso'])): ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalSucesso = new bootstrap.Modal(
        document.getElementById('modalSucessoFornecedor')
    );

    modalSucesso.show();
});
</script>

<?php endif; ?>
<!-- Modal erro -->
 <div class="modal fade" id="modalErroFornecedor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header justify-content-center">
                <h5 class="modal-title text-danger text-center">
                    Erro ao adicionar fornecedor
                </h5>
            </div>

            <div class="modal-body text-center">
                <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>

                <p class="mb-0">
                    <?= htmlspecialchars($erroModal ?? '') ?>
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

<?php if (isset($erroModal)): ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalErro = new bootstrap.Modal(
        document.getElementById('modalErroFornecedor')
    );

    modalErro.show();
});
</script>

<?php endif; ?>

<script src="/SIBDAS_PROJETO_26_MEDGEST/assets/js/1230824.js"></script>
<?php include 'includes/footer.php'; ?>