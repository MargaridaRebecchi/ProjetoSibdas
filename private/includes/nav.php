<!-- NAVBAR PRIVADA -->
<?php
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-medgest fixed-top">
    <div class="container-fluid px-4">

        <!-- LOGO -->
        <a class="navbar-brand-medgest" href="#inicio">
            <div class="logo-icone">
                <img src="../assets/images/logo.png" alt="Logo MedGest" style="height:32px;">
            </div>
            Med<span class="logo-gest">Gest</span>
        </a>

        <!-- LINKS -->
        <div class="collapse navbar-collapse" id="navbarMedGest">

            <ul class="navbar-nav mx-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link nav-link-medgest <?= $paginaAtual == 'gestao_equipamentos.php' ? 'ativo' : '' ?>"
                        href="gestao_equipamentos.php">
                        Equipamentos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-medgest <?= $paginaAtual == 'localizacoes.php' ? 'ativo' : '' ?>"
                        href="localizacoes.php">
                        Localizações
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-medgest <?= $paginaAtual == 'fornecedores.php' ? 'ativo' : '' ?>"
                        href="fornecedores.php">
                        Fornecedores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-medgest <?= $paginaAtual == 'documentacao.php' ? 'ativo' : '' ?>"
                        href="documentacao.php">
                        Documentação
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-link-medgest <?= $paginaAtual == 'contratos_garantias.php' ? 'ativo' : '' ?>"
                        href="contratos_garantias.php">
                        Garantias e Contratos
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-medgest <?= $paginaAtual == 'dashboard.php' ? 'ativo' : '' ?>"
                        href="dashboard.php">
                        Dashboard
                    </a>
                </li>
            </ul>

            <!-- LOGOUT-->
            <a href="/SIBDAS_PROJETO_26_MEDGEST/public/index.php" class="nav-link nav-link-medgest nav-btn-entrar" data-bs-toggle="modal" data-bs-target="#loginModal">
                Sair
            </a>

        </div>
    </div>
</nav>