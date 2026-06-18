<?php
include 'includes/db.php';

$sql = "SELECT *
        FROM fornecedores
        ORDER BY nome_empresa ASC";

$result = $conn->query($sql);

//Modal apagar fornecedor
if (isset($_GET['apagar']) && is_numeric($_GET['apagar'])) {

    $id = (int) $_GET['apagar'];

    $stmt = $conn->prepare("
        DELETE FROM fornecedores
        WHERE id_fornecedor = ?
    ");

    $stmt->bind_param("i", $id);

    $stmt->execute();

    header("Location: fornecedores.php?apagado=1");
    exit();
}
// Editar fornecedor
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_fornecedor'])) {

    $id = (int) $_POST['id_fornecedor'];

    $sqlUpdate = "UPDATE fornecedores
                  SET nome_empresa = ?, nif = ?, telefone = ?, email = ?, morada = ?, website = ?,
                      pessoa_contacto = ?, telefone_contacto = ?, tipo_fornecedor = ?, observacoes = ?
                  WHERE id_fornecedor = ?";

    $stmt = $conn->prepare($sqlUpdate);

    $stmt->bind_param(
        "ssssssssssi",
        $_POST['nome_empresa'],
        $_POST['nif'],
        $_POST['telefone'],
        $_POST['email'],
        $_POST['morada'],
        $_POST['website'],
        $_POST['pessoa_contacto'],
        $_POST['telefone_contacto'],
        $_POST['tipo_fornecedor'],
        $_POST['observacoes'],
        $id
    );

    try {
        $stmt->execute();

        header("Location: fornecedores.php?editado=1");
        exit();
    } catch (mysqli_sql_exception $e) {

        if ($e->getCode() == 1062) {
            $erroModal = "Já existe um fornecedor com esse NIF.";
        } else {
            $erroModal = "Erro ao editar fornecedor: " . $e->getMessage();
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
                <h1>Fornecedores</h1>
            </div>

            <a href="adicionar_fornecedor.php" class="btn-primario text-decoration-none">
                <i class="fas fa-plus me-2"></i>
                Novo fornecedor
            </a>
        </section>

        <section class="private-card">

            <div class="table-responsive">

                <table class="table tabela-medgest align-middle">

                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>NIF</th>
                            <th>Tipo</th>
                            <th>Pessoa de contacto</th>
                            <th>Telefone</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($result && $result->num_rows > 0): ?>

                            <?php while ($row = $result->fetch_assoc()): ?>

                                <?php
                                $tipoTexto = str_replace('_', ' ', $row['tipo_fornecedor']);

                                if ($tipoTexto == 'fornecedor consumiveis') {
                                    $tipoTexto = 'fornecedor de consumíveis';
                                }

                                if ($tipoTexto == 'assistencia tecnica') {
                                    $tipoTexto = 'assistência técnica';
                                }
                                ?>

                                <tr>
                                    <td><?= htmlspecialchars($row['nome_empresa']) ?></td>
                                    <td><?= htmlspecialchars($row['nif']) ?></td>
                                    <td><?= ucfirst(htmlspecialchars($tipoTexto)) ?></td>
                                    <td><?= htmlspecialchars($row['pessoa_contacto']) ?></td>
                                    <td><?= htmlspecialchars($row['telefone']) ?></td>

                                    <td>
                                        <button
                                            type="button"
                                            class="btn-acao ver"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalVerFornecedor<?= $row['id_fornecedor'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <div class="modal fade" id="modalVerFornecedor<?= $row['id_fornecedor'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content ficha-equipamento">

                                                    <div class="modal-header justify-content-center">
                                                        <div class="text-center">
                                                            <h5 class="modal-title">Ficha do Fornecedor</h5>
                                                            <small class="text-muted"><?= htmlspecialchars($row['nome_empresa']) ?></small>
                                                        </div>
                                                    </div>

                                                    <div class="modal-body">

                                                        <h6 class="ficha-secao ficha-identificacao">Identificação</h6>
                                                        <div class="ficha-grid">
                                                            <div><strong>Empresa</strong><span><?= htmlspecialchars($row['nome_empresa']) ?></span></div>
                                                            <div><strong>NIF</strong><span><?= htmlspecialchars($row['nif']) ?></span></div>
                                                            <div><strong>Tipo</strong><span><?= ucfirst(htmlspecialchars($tipoTexto)) ?></span></div>
                                                            <div><strong>Data de registo</strong><span><?= htmlspecialchars($row['data_registo']) ?></span></div>
                                                        </div>

                                                        <h6 class="ficha-secao ficha-identificacao">Contactos da empresa</h6>
                                                        <div class="ficha-grid">
                                                            <div><strong>Telefone</strong><span><?= htmlspecialchars($row['telefone']) ?></span></div>
                                                            <div><strong>Email</strong><span><?= htmlspecialchars($row['email']) ?></span></div>
                                                            <div><strong>Website</strong><span><?= htmlspecialchars($row['website']) ?></span></div>
                                                            <div><strong>Morada</strong><span><?= htmlspecialchars($row['morada']) ?></span></div>
                                                        </div>

                                                        <h6 class="ficha-secao ficha-identificacao">Pessoa de contacto</h6>
                                                        <div class="ficha-grid">
                                                            <div><strong>Nome</strong><span><?= htmlspecialchars($row['pessoa_contacto']) ?></span></div>
                                                            <div><strong>Telefone</strong><span><?= htmlspecialchars($row['telefone_contacto']) ?></span></div>
                                                        </div>

                                                        <h6 class="ficha-secao ficha-identificacao">Observações</h6>
                                                        <div class="ficha-grid">
                                                            <div style="grid-column: 1 / -1;">
                                                                <strong>Observações</strong>
                                                                <span><?= htmlspecialchars($row['observacoes']) ?></span>
                                                            </div>
                                                        </div>

                                                    </div>



                                                </div>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            class="btn-acao editar"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarFornecedor"
                                            data-id="<?= $row['id_fornecedor'] ?>"
                                            data-nome="<?= htmlspecialchars($row['nome_empresa']) ?>"
                                            data-nif="<?= htmlspecialchars($row['nif']) ?>"
                                            data-telefone="<?= htmlspecialchars($row['telefone']) ?>"
                                            data-email="<?= htmlspecialchars($row['email']) ?>"
                                            data-morada="<?= htmlspecialchars($row['morada']) ?>"
                                            data-website="<?= htmlspecialchars($row['website']) ?>"
                                            data-pessoa="<?= htmlspecialchars($row['pessoa_contacto']) ?>"
                                            data-telefone-contacto="<?= htmlspecialchars($row['telefone_contacto']) ?>"
                                            data-tipo="<?= htmlspecialchars($row['tipo_fornecedor']) ?>"
                                            data-observacoes="<?= htmlspecialchars($row['observacoes']) ?>">
                                            <i class="fas fa-pen"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="btn-acao apagar"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalApagarFornecedor"
                                            data-id="<?= $row['id_fornecedor'] ?>"
                                            data-nome="<?= htmlspecialchars($row['nome_empresa']) ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Não existem fornecedores registados.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

<!-- Modal editar fornecedor -->
 <div class="modal fade" id="modalEditarFornecedor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            <form method="POST" id="formEditarFornecedor" novalid>

                <input type="hidden" name="editar_fornecedor" value="1">
                <input type="hidden" name="id_fornecedor" id="edit_id_fornecedor">

                <div class="modal-header justify-content-center">
                    <h5 class="modal-title text-center">Editar fornecedor</h5>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Nome da empresa</label>
                            <input type="text" name="nome_empresa" id="edit_nome_empresa" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>NIF</label>
                            <input type="text" name="nif" id="edit_nif" class="form-control form-control-sm" maxlength="9" pattern="[0-9]{9}" required>
                            <div class="invalid-feedback">O NIF deve ter exatamente 9 dígitos.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Telefone</label>
                            <input type="text" name="telefone" id="edit_telefone" class="form-control form-control-sm" maxlength="9" pattern="[0-9]{9}" required>
                            <div class="invalid-feedback">O telefone deve ter exatamente 9 dígitos.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control form-control-sm" required>
                            <div class="invalid-feedback">Introduza um email válido.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Morada</label>
                            <input type="text" name="morada" id="edit_morada" class="form-control form-control-sm" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Website</label>
                            <input type="text" name="website" id="edit_website" class="form-control form-control-sm" required>
                            <div class="invalid-feedback">Introduza um website válido.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Pessoa de contacto</label>
                            <input type="text" name="pessoa_contacto" id="edit_pessoa_contacto" class="form-control form-control-sm" pattern="[A-Za-zÀ-ÿ\s]+" required>
                            <div class="invalid-feedback">A pessoa de contacto deve conter apenas letras.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Telefone da pessoa de contacto</label>
                            <input type="text" name="telefone_contacto" id="edit_telefone_contacto" class="form-control form-control-sm" maxlength="9" pattern="[0-9]{9}" required>
                            <div class="invalid-feedback">O telefone da pessoa de contacto deve ter exatamente 9 dígitos.</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Tipo de fornecedor</label>
                            <select name="tipo_fornecedor" id="edit_tipo_fornecedor" class="form-select form-select-sm" required>
                                <option value="fabricante">Fabricante</option>
                                <option value="distribuidor">Distribuidor</option>
                                <option value="fornecedor_consumiveis">Fornecedor de consumíveis</option>
                                <option value="assistencia_tecnica">Assistência técnica</option>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label>Observações</label>
                            <textarea name="observacoes" id="edit_observacoes" class="form-control form-control-sm" rows="3" required></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-success">
                        Guardar alterações
                    </button>

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<!-- Modal apagar fornecedor -->
    <div class="modal fade" id="modalApagarFornecedor" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Remover fornecedor</h5>

                </div>

                <div class="modal-body">
                    Tem a certeza que quer remover o fornecedor
                    <strong id="nomeFornecedorApagar"></strong>?
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <a href="#" id="confirmarApagarFornecedor" class="btn btn-danger">
                        Remover
                    </a>
                </div>

            </div>
        </div>
    </div>


<!--MODAL SUCESSO EDIÇÃO DE FORNECEDOR -->
    <div class="modal fade" id="modalEditadoFornecedor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header justify-content-center">
                <h5 class="modal-title text-success text-center">
                    Fornecedor atualizado
                </h5>
            </div>

            <div class="modal-body text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                <p class="mb-0">
                    Fornecedor atualizado com sucesso!
                </p>
            </div>

            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>


<!--MODAL SUCESSO REMOÇÃO DE FORNECEDOR -->
    <div class="modal fade" id="modalSucesso">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Sucesso</h5>
                </div>

                <div class="modal-body text-center">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>

                    <p class="mb-0">
                        Fornecedor removido com sucesso!
                    </p>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button"
                        class="btn btn-success"
                        data-bs-dismiss="modal">
                        Fechar
                    </button>
                </div>

            </div>
        </div>
    </div>

    <?php if (isset($_GET['editado'])): ?>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = new bootstrap.Modal(
        document.getElementById('modalEditadoFornecedor')
    );

    modal.show();

    window.history.replaceState(
        {},
        document.title,
        'fornecedores.php'
    );

});
</script>

<?php endif; ?>
    <?php if (isset($_GET['apagado'])): ?>

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const modalSucesso = new bootstrap.Modal(
                    document.getElementById('modalSucesso')
                );

                modalSucesso.show();
                window.history.replaceState({},
                    document.title,
                    'fornecedores.php'
                );

            });
        </script>

    <?php endif; ?>
    <script src="/SIBDAS_PROJETO_26_MEDGEST/assets/js/1230824.js"></script>
</body>

<?php include 'includes/footer.php'; ?>