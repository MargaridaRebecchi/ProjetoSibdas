<?php
session_start();
require_once __DIR__ . '/../config/config.php';
include __DIR__ . '/../private/includes/db.php';

/*Alteração dos conteudos publicos */
$conteudosPublicos = [];

$resultConteudos = $conn->query("
    SELECT chave, titulo, texto
    FROM conteudos_publicos
");

while ($c = $resultConteudos->fetch_assoc()) {
  $conteudosPublicos[$c['chave']] = [
    'titulo' => $c['titulo'],
    'texto' => $c['texto']
  ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_contacto'])) {

  $nome = trim($_POST['nome']);
  $email = trim($_POST['email']);
  $telefone = trim($_POST['telefone']);
  $instituicao = trim($_POST['instituicao']);
  $assunto = trim($_POST['assunto']);
  $mensagem = trim($_POST['mensagem']);

  if ($nome && $email && $instituicao && $assunto && $mensagem) {

    $stmt = $conn->prepare("
            INSERT INTO mensagens_contacto
            (nome, email, telefone, instituicao, assunto, mensagem)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

    $stmt->bind_param(
      "ssssss",
      $nome,
      $email,
      $telefone,
      $instituicao,
      $assunto,
      $mensagem
    );

    $stmt->execute();

    $_SESSION['mensagem_enviada'] = true;

    header("Location: index.php#contacto");
    exit;
  }
}
?>



<!DOCTYPE html>
<html lang="pt">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo APP_NAME; ?></title>
  <!-- favicon -->
  <link rel="shortcut icon" href="../assets/images/logo.png" type="image/png">

  <!-- estilos da página -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="stylesheet" href="../assets/css/1230824.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/1230824.js"></script>
  <link
    href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap"
    rel="stylesheet">
</head>

<body>
  <!-- Navegação -->
  <nav class="navbar navbar-expand-lg navbar-dark navbar-medgest fixed-top">
    <div class="container-fluid px-4">

      <!-- LOGO -->
      <a class="navbar-brand-medgest" href="#inicio">
        <div class="logo-icone">
          <img src="../assets/images/logo.png" alt="Logo MedGest" style="height:32px;">
        </div>
        Med<span class="logo-gest">Gest</span>
      </a>

      <!-- BOTÃO MOBILE -->
      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarMedGest" aria-controls="navbarMedGest" aria-expanded="false"
        aria-label="Abrir menu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- LINKS -->
      <div class="collapse navbar-collapse" id="navbarMedGest">

        <ul class="navbar-nav mx-auto gap-1">
          <li class="nav-item">
            <a class="nav-link nav-link-medgest" href="#sobre-nos">Sobre Nós</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-medgest" href="#servicos">Serviços</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-medgest" href="#funcionalidades">Funcionalidades</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-medgest" href="#testemunhos">Clientes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link nav-link-medgest" href="#contacto">Contacto</a>
          </li>
        </ul>

        <!-- LOGIN -->
        <a href="login.php" class="nav-link nav-link-medgest nav-btn-entrar">
          Área Restrita
        </a>

      </div>
    </div>
  </nav>

  <!-- Seção "Conteudo da pagina" -->
  <!-- HERO -->
  <section id="inicio" class="hero">
    <div class="hero-grid-bg"></div>

    <div class="container py-5">
      <div class="row align-items-center justify-content-center text-center g-5">

        <div class="col-lg-8">
          <div class="hero-conteudo">

            <h1 class="hero-titulo">
              Gestão inteligente do<br>
              <span class="destaque">inventário hospitalar</span>
            </h1>

            <p class="hero-descricao">
              A MedGest é uma plataforma web completa que centraliza toda a informação
              sobre equipamentos médicos, fornecedores e documentação técnica num único lugar,
              acessível em qualquer momento e em qualquer dispositivo.
            </p>

            <div class="hero-botoes">
              <a href="#contacto" class="btn-primario">
                <i class="fas fa-calendar-check"></i> Solicitar demonstração
              </a>

              <a href="#funcionalidades" class="btn-secundario">
                <i class="fas fa-play-circle"></i> Ver funcionalidades
              </a>
            </div>

            <div class="hero-estatisticas">
              <div class="hero-stat">
                <div class="hero-stat-numero" data-contar="47" data-sufixo="+">0+</div>
                <div class="hero-stat-label">Hospitais parceiros</div>
              </div>

              <div class="hero-stat">
                <div class="hero-stat-numero" data-contar="12000" data-sufixo="+">0+</div>
                <div class="hero-stat-label">Equipamentos geridos</div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </section>



  <!-- Seção "SOBRE NÓS" -->
  <section id="sobre-nos" class="secao-sobre">
    <div class="container text-center">
      <div class="sobre-grid">

        <div class="sobre-conteudo animar-direita">
          <div class="secao-etiqueta">Sobre a MedGest</div>
          <h2 class="secao-titulo mb-4">

            <?= htmlspecialchars($conteudosPublicos['sobre']['titulo']) ?>
          </h2>
          <p>
            <?= nl2br(htmlspecialchars($conteudosPublicos['sobre']['texto'])) ?>
          </p>

          <div class="sobre-valores">
            <div class="valor-item">
              <div class="valor-icone"><i class="fas fa-shield-halved"></i></div>
              <div>
                <div class="valor-titulo">Segurança</div>
                <div class="valor-desc">Dados protegidos com encriptação de nível hospitalar</div>
              </div>
            </div>
            <div class="valor-item">
              <div class="valor-icone"><i class="fas fa-bolt"></i></div>
              <div>
                <div class="valor-titulo">Performance</div>
                <div class="valor-desc">Resposta rápida mesmo com grandes volumes de dados</div>
              </div>
            </div>
            <div class="valor-item">
              <div class="valor-icone"><i class="fas fa-users"></i></div>
              <div>
                <div class="valor-titulo">Usabilidade</div>
                <div class="valor-desc">Interface intuitiva para toda a equipa técnica</div>
              </div>
            </div>
            <div class="valor-item">
              <div class="valor-icone"><i class="fas fa-headset"></i></div>
              <div>
                <div class="valor-titulo">Suporte</div>
                <div class="valor-desc">Assistência técnica disponível 24 horas por dia</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Seção "Conteudo da pagina - SERVIÇOS" -->
  <section id="servicos" class="secao-servicos">
    <div class="container text-center">
      <div class="secao-etiqueta animar-entrada">O que oferecemos</div>
      <h2 class="secao-titulo animar-entrada" data-delay="100">
        <?= htmlspecialchars($conteudosPublicos['servicos']['titulo']) ?>
      </h2>

      <p class="secao-subtitulo animar-entrada" data-delay="150">
        <?= nl2br(htmlspecialchars($conteudosPublicos['servicos']['texto'])) ?>
      </p>
      <div class="servicos-grid">

        <div class="servico-cartao animar-entrada" data-delay="0">
          <div class="servico-icone-wrapper" style="background: rgba(10,79,110,0.08); color: var(--cor-primaria);">
            <i class="fas fa-boxes-stacked"></i>
          </div>
          <h3 class="servico-titulo">Gestão de Inventário</h3>
          <p class="servico-desc">
            Registo centralizado de todos os equipamentos médicos com informação detalhada
            sobre localização, estado, criticidade e histórico técnico.
          </p>
        </div>

        <div class="servico-cartao animar-entrada" data-delay="100">
          <div class="servico-icone-wrapper" style="background: rgba(45,212,160,0.1); color: var(--cor-acento-escuro);">
            <i class="fas fa-file-medical"></i>
          </div>
          <h3 class="servico-titulo">Gestão Documental</h3>
          <p class="servico-desc">
            Associação de manuais, certificados, contratos e relatórios técnicos diretamente
            à ficha de cada equipamento, acessíveis em qualquer momento.
          </p>
        </div>

        <div class="servico-cartao animar-entrada" data-delay="200">
          <div class="servico-icone-wrapper" style="background: rgba(99,179,237,0.1); color: #3b82f6;">
            <i class="fas fa-handshake"></i>
          </div>
          <h3 class="servico-titulo">Gestão de Fornecedores</h3>
          <p class="servico-desc">
            Base de dados integrada de fabricantes, distribuidores e empresas de assistência
            técnica, com associação direta aos equipamentos sob contrato.
          </p>
        </div>

        <div class="servico-cartao animar-entrada" data-delay="0">
          <div class="servico-icone-wrapper" style="background: rgba(251,191,36,0.1); color: #d97706;">
            <i class="fas fa-chart-bar"></i>
          </div>
          <h3 class="servico-titulo">Dashboards</h3>
          <p class="servico-desc">
            Visão global do estado dos equipamentos médicos, permitindo
            acompanhar manutenção, disponibilidade e informação relevante do inventário hospitalar.
          </p>
        </div>

        <div class="servico-cartao animar-entrada" data-delay="100">
          <div class="servico-icone-wrapper" style="background: rgba(239,68,68,0.08); color: #ef4444;">
            <i class="fas fa-triangle-exclamation"></i>
          </div>
          <h3 class="servico-titulo">Alertas e Notificações</h3>
          <p class="servico-desc">
            Sistema de alertas para garantias a expirar, equipamentos em manutenção e
            documentação em falta.
          </p>
        </div>

        <div class="servico-cartao animar-entrada" data-delay="200">
          <div class="servico-icone-wrapper" style="background: rgba(139,92,246,0.1); color: #7c3aed;">
            <i class="fas fa-magnifying-glass-chart"></i>
          </div>
          <h3 class="servico-titulo">Pesquisa Avançada</h3>
          <p class="servico-desc">
            Localização instantânea de qualquer equipamento por código, marca, modelo,
            serviço ou criticidade, com filtros combinados e vistas personalizadas.
          </p>
        </div>

      </div>
    </div>
  </section>

  <!-- Seção "Conteudo da pagina - FUNCIONALIDADES" -->
  <section id="funcionalidades" class="secao-funcionalidades">
    <div class="container text-center">
      <div class="secao-etiqueta animar-entrada">Todas as funcionalidades</div>
      <h2 class="secao-titulo animar-entrada" data-delay="100">
        <?= htmlspecialchars($conteudosPublicos['funcionalidades']['titulo']) ?>
      </h2>
      <div class="func-lista">
        <div class="func-item animar-entrada" data-delay="0">
          <div class="func-check"><i class="fas fa-check"></i></div>
          <div>
            <div class="func-texto-titulo">Módulo de Equipamentos</div>
            <div class="func-texto-desc">Inserção, listagem, edição e remoção de equipamentos com ficha detalhada</div>
          </div>
        </div>
        <div class="func-item animar-entrada" data-delay="80">
          <div class="func-check"><i class="fas fa-check"></i></div>
          <div>
            <div class="func-texto-titulo">Gestão de Localizações</div>
            <div class="func-texto-desc">Organização dos equipamentos por edifício, piso, serviço ou sala hospitalar</div>
          </div>
        </div>
        <div class="func-item animar-entrada" data-delay="160">
          <div class="func-check"><i class="fas fa-check"></i></div>
          <div>
            <div class="func-texto-titulo">Módulo de Fornecedores</div>
            <div class="func-texto-desc">Consulta e gestão de fabricantes e distribuidores</div>
          </div>
        </div>
        <div class="func-item animar-entrada" data-delay="240">
          <div class="func-check"><i class="fas fa-check"></i></div>
          <div>
            <div class="func-texto-titulo">Gestão Documental</div>
            <div class="func-texto-desc">Acesso rápido a manuais, certificados e documentos ligados a cada equipamento</div>
          </div>
        </div>
        <div class="func-item animar-entrada" data-delay="320">
          <div class="func-check"><i class="fas fa-check"></i></div>
          <div>
            <div class="func-texto-titulo">Garantias e Contratos</div>
            <div class="func-texto-desc">Controlo de prazos, garantias e informação contratual dos equipamentos</div>
          </div>
        </div>
      </div>
    </div>



  </section>


  <!-- Seção "Conteudo da pagina - CLIENTES" -->
  <section id="testemunhos" class="secao-testemunhos">
    <div class="container text-center">
      <div class="secao-etiqueta animar-entrada">O que dizem os nossos clientes</div>
      <h2 class="secao-titulo animar-entrada" data-delay="100">
        <?= htmlspecialchars($conteudosPublicos['testemunhos']['titulo']) ?>
      </h2>

      <p class="secao-subtitulo animar-entrada" data-delay="150">
        <?= nl2br(htmlspecialchars($conteudosPublicos['testemunhos']['texto'])) ?>
      </p>

      <div class="row g-4 mt-2">

        <div class="col-md-4 animar-entrada" data-delay="0">
          <div class="testemunho-cartao">
            <div class="testemunho-aspas">"</div>
            <p class="testemunho-texto">
              A MedGest transformou por completo a forma como gerimos os nossos equipamentos médicos.
              Passámos de folhas Excel dispersas para um sistema centralizado que toda a equipa
              utiliza com facilidade.
            </p>
            <div class="testemunho-autor">
              <div class="testemunho-avatar" style="background: linear-gradient(135deg, var(--cor-primaria), var(--cor-primaria-clara));">JP</div>
              <div>
                <div class="testemunho-nome">João Pinheiro</div>
                <div class="testemunho-cargo">Coordenador Técnico · HSM Lisboa</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4 animar-entrada" data-delay="100">
          <div class="testemunho-cartao">
            <div class="testemunho-aspas">"</div>
            <p class="testemunho-texto">
              O sistema de alertas de garantias foi fundamental para evitar situações de
              equipamentos críticos sem cobertura contratual.
            </p>
            <div class="testemunho-autor">
              <div class="testemunho-avatar" style="background: linear-gradient(135deg, #0d7a5f, var(--cor-acento-escuro));">AC</div>
              <div>
                <div class="testemunho-nome">Ana Carvalho</div>
                <div class="testemunho-cargo">Responsável de Manutenção Hospitalar · HSJ Porto</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-4 animar-entrada" data-delay="200">
          <div class="testemunho-cartao">
            <div class="testemunho-aspas">"</div>
            <p class="testemunho-texto">
              A interface é intuitiva e de fácil utilização. Em menos de
              uma semana toda a equipa estava operacional.
            </p>
            <div class="testemunho-autor">
              <div class="testemunho-avatar" style="background: linear-gradient(135deg, #5c3317, #a0522d);">MF</div>
              <div>
                <div class="testemunho-nome">Miguel Ferreira</div>
                <div class="testemunho-cargo">Gestor Operacional · CHUC Coimbra</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- Seção "Conteudo da pagina - CONTACTO" -->
  <section id="contacto" class="secao-contacto">
    <div class="container">
      <div class="text-center mb-5">
        <div class="secao-etiqueta animar-entrada">Entre em contacto</div>
        <h2 class="secao-titulo animar-entrada" data-delay="100">
          <?= htmlspecialchars($conteudosPublicos['contacto']['titulo']) ?>
        </h2>

        <p class="secao-subtitulo animar-entrada" data-delay="150">
          <?= nl2br(htmlspecialchars($conteudosPublicos['contacto']['texto'])) ?>
        </p>
      </div>

      <div class="contacto-wrapper animar-entrada" data-delay="100">
        <div class="row g-5">

          <!-- Formulário de contacto-->
          <div class="col-lg-7">
            <form id="formContacto" method="POST">
              <input type="hidden" name="enviar_contacto" value="1">

              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label-medgest" for="nome">Nome completo <span style="color:#ef4444;">*</span></label>
                  <input type="text" id="nome" name="nome" class="form-control-medgest" required
                    placeholder="Ex: Maria Santos" aria-describedby="msg-nome" />
                  <div id="msg-nome" class="form-controle-mensagem" aria-live="polite"></div>
                </div>

                <div class="col-md-6">
                  <label class="form-label-medgest" for="email">Email<span style="color:#ef4444;">*</span></label>
                  <input type="email" id="email" name="email" class="form-control-medgest" required
                    placeholder="Ex: maria@hospital.pt" aria-describedby="msg-email" />
                  <div id="msg-email" class="form-controle-mensagem" aria-live="polite"></div>
                </div>

                <div class="col-md-6">
                  <label class="form-label-medgest" for="telefone">Telefone</label>
                  <input type="tel" id="telefone" name="telefone" class="form-control-medgest"
                    placeholder="Ex: 222 000 000" aria-describedby="msg-telefone" />
                  <div id="msg-telefone" class="form-controle-mensagem" aria-live="polite"></div>
                </div>

                <div class="col-md-6">
                  <label class="form-label-medgest" for="instituicao">Instituição <span style="color:#ef4444;">*</span></label>
                  <input type="text" id="instituicao" name="instituicao" class="form-control-medgest" required
                    placeholder="Ex: Centro Hospitalar Lisboa Norte" aria-describedby="msg-instituicao" />
                  <div id="msg-instituicao" class="form-controle-mensagem" aria-live="polite"></div>
                </div>

                <div class="col-12">
                  <label class="form-label-medgest" for="assunto">Assunto <span style="color:#ef4444;">*</span></label>
                  <select id="assunto" name="assunto" class="form-control-medgest" required aria-describedby="msg-assunto">
                    <option value="">Selecione um assunto…</option>
                    <option value="informacoes">Pedido de informações</option>
                    <option value="suporte">Suporte da plataforma</option>
                    <option value="outro">Outro</option>
                  </select>
                  <div id="msg-assunto" class="form-controle-mensagem" aria-live="polite"></div>
                </div>

                <div class="col-12">
                  <label class="form-label-medgest" for="mensagem">Mensagem <span style="color:#ef4444;">*</span></label>
                  <textarea id="mensagem" name="mensagem" class="form-control-medgest" required rows="5"
                    placeholder="Faça a sua questão" aria-describedby="msg-mensagem"></textarea>
                  <div id="msg-mensagem" class="form-controle-mensagem" aria-live="polite"></div>
                </div>
                <div class="col-12">
                  <p style="font-size: 0.8rem; color: var(--cor-texto-suave); margin-bottom: 1rem;">
                    <span style="color:#ef4444;">*</span> Campos obrigatórios
                  </p>
                  <button type="submit" class="btn-primario w-100" id="btnEnviarContacto" style="border-radius: 12px; justify-content: center;">
                    <?php if (isset($_SESSION['mensagem_enviada'])): ?>
                      <i class="fas fa-check me-2"></i>Mensagem enviada com sucesso!
                      <?php unset($_SESSION['mensagem_enviada']); ?>
                    <?php else: ?>
                      <i class="fas fa-paper-plane me-2"></i>Enviar mensagem
                    <?php endif; ?>
                  </button>
                  <div class="text-center mt-2">

                    <button type="button" class="btn-login-demo" onclick="preencherContactoTeste()">
                      Preencher formulário teste
                    </button>

                  </div>
                </div>
              </div>
            </form>
          </div>

          <!-- Informação de contacto -->
          <div class="col-lg-5">
            <div class="contacto-info">
              <h3 style="font-size: 1.3rem; margin-bottom: 1.5rem; color: var(--cor-primaria-escura);">
                Informações de contacto
              </h3>

              <div class="contacto-info-item">
                <div class="contacto-icone"><i class="fas fa-location-dot"></i></div>
                <div>
                  <div class="contacto-detalhe-titulo">Morada</div>
                  <div class="contacto-detalhe-valor">Porto, Portugal</div>
                </div>
              </div>

              <div class="contacto-info-item">
                <div class="contacto-icone"><i class="fas fa-phone"></i></div>
                <div>
                  <div class="contacto-detalhe-titulo">Telefone</div>
                  <div class="contacto-detalhe-valor">+351 222 000 100</div>
                </div>
              </div>

              <div class="contacto-info-item">
                <div class="contacto-icone"><i class="fas fa-envelope"></i></div>
                <div>
                  <div class="contacto-detalhe-titulo">Email</div>
                  <div class="contacto-detalhe-valor">geral@medgest.pt</div>
                </div>
              </div>

              <div class="contacto-info-item">
                <div class="contacto-icone"><i class="fas fa-clock"></i></div>
                <div>
                  <div class="contacto-detalhe-titulo">Horário</div>
                  <div class="contacto-detalhe-valor">2ª a 6ª Feira: 9h — 18h</div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>


  <!-- Rodapé -->
  <footer class="footer">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-2 col-md-6">
          <div class="footer-titulo-coluna">EMPRESA</div>
          <ul class="footer-lista">
            <li><a href="#sobre-nos">Sobre nós</a></li>
            <li><a href="#servicos">Serviços</a></li>
            <li><a href="#funcionalidades">Funcionalidades</a></li>
            <li><a href="#testemunhos">Clientes</a></li>
            <li><a href="#contacto">Contacto</a></li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-6">
          <div class="footer-titulo-coluna">CONTACTOS</div>
          <ul class="footer-lista">
            <li><i class="fas fa-location-dot me-2"></i>Porto, Portugal</li>
            <li><i class="fas fa-phone me-2"></i>+351 222 000
              100</li>
            <li><i class="fas fa-envelope me-2"></i>geral@medgest.pt</li>
            <li><i class="fas fa-clock me-2"></i>Horário: 2ª a 6ª Feira: 9h — 18h
            </li>
          </ul>
        </div>

      </div>


  </footer>

</body>

</html>