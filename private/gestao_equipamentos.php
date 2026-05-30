<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Gestão de Equipamentos - MedGest</title>

    <link rel="stylesheet" href="../assets/css/1230824.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>

<body class="area-privada-body">

    <main class="private-main">

        <section class="private-header">

            <div>
                
                <h1>Gestão de Equipamentos</h1>
                <p>Inventário hospitalar e gestão de equipamentos médicos.</p>
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