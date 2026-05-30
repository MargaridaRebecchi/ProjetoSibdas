<?php include 'includes/header.php'; ?> 
<?php include 'includes/nav.php'; ?> 
<!DOCTYPE html>
<html lang="pt">



<body class="area-privada-body">

    <main class="private-main">

        <section class="private-header">

            <div>
                
                <h1>Gestão de Equipamentos</h1>
                
            </div>

            <button class="btn-primario">
                <i class="fas fa-plus me-2"></i>
                Novo equipamento
            </button>

        </section>

        <section class="private-card">

            <div class="table-responsive">

                <table class="table tabela-medgest align-middle">

                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Designação</th>
                            <th>Categoria</th>
                            <th>Marca</th>
                            <th>Estado</th>
                            <th>Criticidade</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td>04.002</td>
                            <td>Monitor multiparamétrico</td>
                            <td>Monitorização</td>
                            <td>Philips</td>
                            <td>
                                <span class="badge-medgest ativo">
                                    Ativo
                                </span>
                            </td>
                            <td>
                                <span class="badge-medgest suporte">
                                    Suporte de vida
                                </span>
                            </td>

                            <td>
                                <button class="btn-acao ver">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <button class="btn-acao editar">
                                    <i class="fas fa-pen"></i>
                                </button>

                                <button class="btn-acao apagar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <tr>
                            <td>05.001</td>
                            <td>Ventilador pulmonar</td>
                            <td>Suporte de vida</td>
                            <td>Dräger</td>

                            <td>
                                <span class="badge-medgest ativo">
                                    Ativo
                                </span>
                            </td>

                            <td>
                                <span class="badge-medgest suporte">
                                    Suporte de vida
                                </span>
                            </td>

                            <td>
                                <button class="btn-acao ver">
                                    <i class="fas fa-eye"></i>
                                </button>

                                <button class="btn-acao editar">
                                    <i class="fas fa-pen"></i>
                                </button>

                                <button class="btn-acao apagar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</body>
</html>

<?php include 'includes/footer.php'; ?>