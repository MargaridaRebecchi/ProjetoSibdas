<?php
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark navbar-medgest fixed-top">
    <div class="container-fluid px-4">

        <a class="navbar-brand-medgest" href="admin.php">
            <div class="logo-icone">
                <img src="../assets/images/logo.png" alt="Logo MedGest" style="height:32px;">
            </div>
            Med<span class="logo-gest">Gest</span>
        </a>

        <div class="collapse navbar-collapse">

            <ul class="navbar-nav mx-auto gap-1">

                <li class="nav-item">
                    <a class="nav-link nav-link-medgest <?= $paginaAtual == 'admin.php' ? 'ativo' : '' ?>"
                        href="admin.php">
                        Conteúdos Públicos
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-link-medgest <?= $paginaAtual == 'admin_mensagens.php' ? 'ativo' : '' ?>"
                        href="admin_mensagens.php">
                        Mensagens de Contacto
                    </a>
                </li>

            </ul>

            <a href="logout.php" class="nav-link nav-link-medgest nav-btn-entrar">
                Sair
            </a>

        </div>
    </div>
</nav>