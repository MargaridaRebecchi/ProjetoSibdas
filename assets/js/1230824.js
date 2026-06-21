//INDEX
// Navbar pública - link ativo
document.addEventListener('DOMContentLoaded', function () {

    const linksPublicos = document.querySelectorAll(
        '.navbar-medgest .nav-link-medgest[href^="#"]'
    );

    linksPublicos.forEach(function (link) {

        link.addEventListener('click', function () {

            linksPublicos.forEach(function (l) {
                l.classList.remove('ativo');
            });

            this.classList.add('ativo');

        });

    });

});
//INDEX - HERO- ESTATS
document.addEventListener("DOMContentLoaded", function () {
    const numeros = document.querySelectorAll(".hero-stat-numero");

    function animarNumero(elemento) {
        const valorFinal = Number(elemento.dataset.contar);
        const sufixo = elemento.dataset.sufixo || "";
        let valorAtual = 0;

        const duracao = 1500;
        const intervalo = 20;
        const incremento = valorFinal / (duracao / intervalo);

        const contador = setInterval(function () {
            valorAtual += incremento;

            if (valorAtual >= valorFinal) {
                elemento.textContent = valorFinal + sufixo;
                clearInterval(contador);
            } else {
                elemento.textContent = Math.floor(valorAtual) + sufixo;
            }
        }, intervalo);
    }

    const observer = new IntersectionObserver(function (entradas) {
        entradas.forEach(function (entrada) {
            if (entrada.isIntersecting) {
                animarNumero(entrada.target);
                observer.unobserve(entrada.target);
            }
        });
    }, {
        threshold: 0.5
    });

    numeros.forEach(function (numero) {
        observer.observe(numero);
    });
});

//INDEX- VALIDAÇOES FORMULARIO CONTACTO
// ── Validação do formulário de contacto ──────────────────────────
document.addEventListener("DOMContentLoaded", function () {

  const formContacto = document.getElementById("formContacto");

  if (!formContacto) return;

  const campos = formContacto.querySelectorAll(".form-control-medgest");

  formContacto.addEventListener("submit", function (e) {
    e.preventDefault();

    let formValido = true;

    campos.forEach(function (campo) {
      if (!validarCampo(campo)) {
        formValido = false;
      }
    });

    if (formValido) {
      mostrarSucesso();
    }
  });

  campos.forEach(function (campo) {
    campo.addEventListener("blur", function () {
      validarCampo(campo);
    });

    campo.addEventListener("input", function () {
      if (campo.classList.contains("erro")) {
        validarCampo(campo);
      }
    });
  });

  function validarCampo(campo) {
    const mensagem = document.getElementById("msg-" + campo.id);

    let valido = true;
    let texto = "";

    if (campo.required && campo.value.trim() === "") {
      valido = false;
      texto = "Este campo é obrigatório.";
    }

    else if (campo.type === "email" && campo.value.trim() !== "") {
      const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (!regexEmail.test(campo.value.trim())) {
        valido = false;
        texto = "Introduza um email válido.";
      }
    }

    else if (campo.id === "telefone" && campo.value.trim() !== "") {
      const regexTelefone = /^[0-9+\s()-]{9,15}$/;

      if (!regexTelefone.test(campo.value.trim())) {
        valido = false;
        texto = "Introduza um número de telefone válido.";
      }
    }

    else if (campo.id === "mensagem" && campo.value.trim().length < 10) {
      valido = false;
      texto = "A mensagem deve ter pelo menos 10 caracteres.";
    }

    if (mensagem) {
      mensagem.textContent = texto;
      mensagem.classList.toggle("erro", !valido);
      mensagem.classList.toggle("sucesso", valido && texto !== "");
    }

    campo.classList.toggle("erro", !valido);

    return valido;
  }

  function mostrarSucesso() {
    const btnSubmit = formContacto.querySelector('button[type="submit"]');
    const textoOriginal = btnSubmit.innerHTML;

    btnSubmit.innerHTML = '<i class="fas fa-check me-2"></i>Mensagem enviada!';
    btnSubmit.disabled = true;
    btnSubmit.classList.add("btn-sucesso");

    formContacto.reset();

    const mensagens = formContacto.querySelectorAll(".form-controle-mensagem");
    mensagens.forEach(function (msg) {
      msg.textContent = "";
      msg.classList.remove("erro", "sucesso");
    });

    campos.forEach(function (campo) {
      campo.classList.remove("erro");
    });

    setTimeout(function () {
      btnSubmit.innerHTML = textoOriginal;
      btnSubmit.disabled = false;
      btnSubmit.classList.remove("btn-sucesso");
    }, 4000);
  }

});

//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
// MODULO LOCALIZAÇÕES
//Fechar os retangulos quando mudo de aba
document.querySelectorAll('.localizacoes-tabs .nav-link')
    .forEach(tab => {

        tab.addEventListener('shown.bs.tab', function () {

            document
                .querySelectorAll('.accordion-collapse.show')
                .forEach(item => {

                    bootstrap.Collapse.getOrCreateInstance(item)
                        .hide();

                });

        });

    });
//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
//MÓDULO FORNECEDORES
//Validaçoes registo de fornecedores
document.addEventListener('DOMContentLoaded', function () {
  

    const formFornecedor = document.getElementById('formFornecedor');

    if (!formFornecedor) return;

    function validarFornecedorCampo(campo) {

        campo.classList.remove('is-valid', 'is-invalid');

        let valido = true;

        if (campo.hasAttribute('required') && campo.value.trim() === '') {
            valido = false;
        }

        if (campo.name === 'nif') {
            valido = /^[0-9]{9}$/.test(campo.value.trim());
        }

        if (campo.name === 'telefone') {
            valido = /^[0-9]{9}$/.test(campo.value.trim());
        }

        if (campo.name === 'telefone_contacto') {
            valido = /^[0-9]{9}$/.test(campo.value.trim());
        }

        if (campo.name === 'pessoa_contacto') {
            valido = /^[A-Za-zÀ-ÿ\s]+$/.test(campo.value.trim());
        }

        if (campo.name === 'email') {
            valido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(campo.value.trim());
        }

        if (campo.name === 'website') {
            valido = /^(https?:\/\/)?(www\.)?[A-Za-z0-9.-]+\.[A-Za-z]{2,}(\/.*)?$/.test(campo.value.trim());
        }

        if (valido) {
            campo.classList.add('is-valid');
            campo.setCustomValidity('');
        } else {
            campo.classList.add('is-invalid');
            campo.setCustomValidity('erro');
        }

        return valido;
    }

    formFornecedor.querySelectorAll('input, select, textarea').forEach(campo => {

        campo.addEventListener('blur', function () {
            validarFornecedorCampo(this);
        });

        campo.addEventListener('input', function () {
            if (this.classList.contains('is-invalid')) {
                validarFornecedorCampo(this);
            }
        });

    });

    formFornecedor.addEventListener('submit', function (e) {

        let formularioValido = true;

        formFornecedor.querySelectorAll('input, select, textarea').forEach(campo => {
            if (!validarFornecedorCampo(campo)) {
                formularioValido = false;
            }
        });

        if (!formularioValido) {
            e.preventDefault();
        }

    });

});
/*Preencher teste*/
function preencherTesteFornecedor() {

    document.querySelector('[name="nome_empresa"]').value = 'Teste';

    document.querySelector('[name="nif"]').value = '123456789';

    document.querySelector('[name="telefone"]').value = '912345678';

    document.querySelector('[name="email"]').value = 'geral@teste.pt';

    document.querySelector('[name="morada"]').value =
        'Rua da Saúde nº 100, Lisboa';

    document.querySelector('[name="website"]').value =
        'https://www.teste.pt';

    document.querySelector('[name="pessoa_contacto"]').value =
        'Joao Silva';

    document.querySelector('[name="telefone_contacto"]').value =
        '913456789';

    document.querySelector('[name="tipo_fornecedor"]').value =
        'fabricante';

    document.querySelector('[name="observacoes"]').value =
        'Fornecedor de teste para validação do sistema.';
}
//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX//
//MÓDULO FORNECEDORES
// Modal editar fornecedor

var modalEditarFornecedor =
    document.getElementById('modalEditarFornecedor');

if (modalEditarFornecedor) {

    modalEditarFornecedor.addEventListener('show.bs.modal', function(event) {

        const botao = event.relatedTarget;

        document.getElementById('edit_id_fornecedor').value = botao.getAttribute('data-id');
        document.getElementById('edit_nome_empresa').value = botao.getAttribute('data-nome');
        document.getElementById('edit_nif').value = botao.getAttribute('data-nif');
        document.getElementById('edit_telefone').value = botao.getAttribute('data-telefone');
        document.getElementById('edit_email').value = botao.getAttribute('data-email');
        document.getElementById('edit_morada').value = botao.getAttribute('data-morada');
        document.getElementById('edit_website').value = botao.getAttribute('data-website');
        document.getElementById('edit_pessoa_contacto').value = botao.getAttribute('data-pessoa');
        document.getElementById('edit_telefone_contacto').value = botao.getAttribute('data-telefone-contacto');
        document.getElementById('edit_tipo_fornecedor').value = botao.getAttribute('data-tipo');
        document.getElementById('edit_observacoes').value = botao.getAttribute('data-observacoes');
    });

}
// Validações no modal editar fornecedor
const formEditarFornecedor = document.getElementById('formEditarFornecedor');

if (formEditarFornecedor) {

    function validarCampoEditarFornecedor(campo) {
        campo.classList.remove('is-valid', 'is-invalid');

        let valido = true;

        if (campo.hasAttribute('required') && campo.value.trim() === '') {
            valido = false;
        }

        if (campo.name === 'nif') {
            valido = /^[0-9]{9}$/.test(campo.value.trim());
        }

        if (campo.name === 'telefone') {
            valido = /^[0-9]{9}$/.test(campo.value.trim());
        }

        if (campo.name === 'telefone_contacto') {
            valido = /^[0-9]{9}$/.test(campo.value.trim());
        }

        if (campo.name === 'pessoa_contacto') {
            valido = /^[A-Za-zÀ-ÿ\s]+$/.test(campo.value.trim());
        }

        if (campo.name === 'email') {
            valido = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(campo.value.trim());
        }

        if (campo.name === 'website') {
            valido = /^(https?:\/\/)?(www\.)?[A-Za-z0-9.-]+\.[A-Za-z]{2,}(\/.*)?$/.test(campo.value.trim());
        }

        if (valido) {
            campo.classList.add('is-valid');
            campo.setCustomValidity('');
        } else {
            campo.classList.add('is-invalid');
            campo.setCustomValidity('erro');
        }

        return valido;
    }

    formEditarFornecedor
        .querySelectorAll('input, select, textarea')
        .forEach(campo => {

            campo.addEventListener('blur', function () {
                validarCampoEditarFornecedor(this);
            });

            campo.addEventListener('input', function () {
                validarCampoEditarFornecedor(this);
            });

        });

    formEditarFornecedor.addEventListener('submit', function (e) {

        let formularioValido = true;

        formEditarFornecedor
            .querySelectorAll('input, select, textarea')
            .forEach(campo => {
                if (!validarCampoEditarFornecedor(campo)) {
                    formularioValido = false;
                }
            });

        if (!formularioValido) {
            e.preventDefault();
        }

    });
}


// Modal apagar fornecedor
document.addEventListener('DOMContentLoaded', function () {

    const modalApagarFornecedor =
        document.getElementById('modalApagarFornecedor');

    if (!modalApagarFornecedor) return;

    modalApagarFornecedor.addEventListener('show.bs.modal', function(event) {

        const botao = event.relatedTarget;

        const id = botao.getAttribute('data-id');
        const nome = botao.getAttribute('data-nome');

        document.getElementById('nomeFornecedorApagar').textContent = nome;

        document.getElementById('confirmarApagarFornecedor').href =
            'fornecedores.php?apagar=' + id;

    });

});
// Modal sucesso associação
document.addEventListener('DOMContentLoaded', function() {

    if (typeof mostrarModalAssociacao === 'undefined' || !mostrarModalAssociacao) {
        return;
    }

    const modalEl = document.getElementById('modalAssociacaoSucesso');

    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);

    modal.show();

    window.history.replaceState(
        {},
        document.title,
        'fornecedores.php'
    );

});

// Modal associação duplicada
document.addEventListener('DOMContentLoaded', function() {

    if (typeof mostrarModalDuplicado === 'undefined' || !mostrarModalDuplicado) {
        return;
    }

    const modalEl = document.getElementById('modalAssociacaoDuplicada');

    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);

    modal.show();

    window.history.replaceState(
        {},
        document.title,
        'fornecedores.php'
    );

});
// Associar fornecedor/equipamento - sincronizar papel e fornecedor
function atualizarPapelFornecedor() {

    const fornecedor = document.getElementById('selectFornecedorAssociar');
    const papel = document.getElementById('selectPapelAssociar');
    const inputPapel = document.getElementById('inputPapelAssociar');

    if (!fornecedor || !papel || !inputPapel) return;

    const opcao = fornecedor.options[fornecedor.selectedIndex];

    if (!opcao || opcao.value === '') {
        papel.value = '';
        inputPapel.value = '';
        return;
    }

    const tipo = opcao.dataset.tipo;

    papel.value = tipo;
    inputPapel.value = tipo;
}
window.atualizarPapelFornecedor = function () {

    const fornecedor = document.getElementById('selectFornecedorAssociar');
    const papel = document.getElementById('selectPapelAssociar');
    const inputPapel = document.getElementById('inputPapelAssociar');

    if (!fornecedor || !papel || !inputPapel) return;

    const opcao = fornecedor.options[fornecedor.selectedIndex];

    if (!opcao || opcao.value === '') {
        papel.value = '';
        inputPapel.value = '';
        return;
    }

    const tipo = opcao.getAttribute('data-tipo');

    papel.value = tipo;
    inputPapel.value = tipo;
};
let fornecedoresAssociarOriginais = [];

document.addEventListener('DOMContentLoaded', function () {

    const fornecedor = document.getElementById('selectFornecedorAssociar');

    if (!fornecedor) return;

    fornecedoresAssociarOriginais = Array.from(fornecedor.options).map(function(opcao) {
        return {
            value: opcao.value,
            text: opcao.textContent,
            tipo: opcao.getAttribute('data-tipo')
        };
    });

});

window.filtrarFornecedoresPorPapel = function () {

    const fornecedor = document.getElementById('selectFornecedorAssociar');
    const papel = document.getElementById('selectPapelAssociar');
    const inputPapel = document.getElementById('inputPapelAssociar');

    const tipoEscolhido = papel.value;

    inputPapel.value = tipoEscolhido;

    fornecedor.innerHTML = '';

    fornecedoresAssociarOriginais.forEach(function(opcao) {

        if (
            opcao.value === '' ||
            tipoEscolhido === '' ||
            opcao.tipo === tipoEscolhido
        ) {
            const novaOpcao = document.createElement('option');

            novaOpcao.value = opcao.value;
            novaOpcao.textContent = opcao.text;

            if (opcao.tipo) {
                novaOpcao.setAttribute('data-tipo', opcao.tipo);
            }

            fornecedor.appendChild(novaOpcao);
        }

    });

    fornecedor.value = '';
};
// Botao informativo papel
document.addEventListener('DOMContentLoaded', function () {

    const tooltipTriggerList =
        [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));

    tooltipTriggerList.forEach(function (tooltipTriggerEl) {

        new bootstrap.Tooltip(tooltipTriggerEl);

    });

});

//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
//MÓDULO DOCUMENTAÇÃO
//Modal apagar documento

document.addEventListener('DOMContentLoaded', function () {

    const modalApagar = document.getElementById('modalApagarDocumento');
    const nomeApagar = document.getElementById('nomeDocumentoApagar');
    const confirmarApagar = document.getElementById('confirmarApagarDocumento');
    const modalSucesso = document.getElementById('modalSucessoDocumento');
    const modalEditadoDocumento = document.getElementById('modalEditadoDocumento');
    const modalEditarDocumento = document.getElementById('modalEditarDocumento');
    const modalDocumentoDuplicado = document.getElementById('modalDocumentoDuplicado');
    const modalPdfNaoEncontrado = document.getElementById('modalPdfNaoEncontrado');

    if (modalApagar && nomeApagar && confirmarApagar) {
        document.querySelectorAll('.btn-apagar-documento').forEach(botao => {
            botao.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const nome = this.getAttribute('data-nome');

                nomeApagar.textContent = nome;
                confirmarApagar.href = 'documentacao.php?apagar=' + id;

                new bootstrap.Modal(modalApagar).show();
            });
        });
    }
//Modal editar documento
    
    if (modalEditarDocumento) {
        modalEditarDocumento.addEventListener('show.bs.modal', function (event) {
            const botao = event.relatedTarget;

            document.getElementById('edit_id_documento').value = botao.getAttribute('data-id');
            document.getElementById('edit_id_contrato').value = botao.getAttribute('data-contrato') || '';
            document.getElementById('edit_id_equipamento').value = botao.getAttribute('data-equipamento');
            document.getElementById('edit_tipo_documento').value = botao.getAttribute('data-tipo');
            document.getElementById('edit_nome_documento').value = botao.getAttribute('data-nome');
            document.getElementById('edit_data_documento').value = botao.getAttribute('data-data');
            document.getElementById('edit_data_validade').value = botao.getAttribute('data-validade') || '';

            const tipoEdit = document.getElementById('edit_tipo_documento');
            const dataEdit = document.getElementById('edit_data_documento');
            const validadeEdit = document.getElementById('edit_data_validade');
            const campoEntidadeEdit = document.getElementById('edit_campo_entidade_responsavel');
            const entidadeEdit = document.getElementById('edit_entidade_responsavel');

            function validarDatasEditDocumento() {
                

                if (
                    validadeEdit.value !== '' &&
                    dataEdit.value > validadeEdit.value
                ) {
                    validadeEdit.setCustomValidity(
                        'A data de validade deve ser posterior à data do documento.'
                    );
                } else {
                    validadeEdit.setCustomValidity('');
                }
            }

            function atualizarCamposEditDocumento() {
                if (
                    tipoEdit.value === 'garantia' ||
                    tipoEdit.value === 'contrato'
                ) {
                    validadeEdit.required = true;
                    campoEntidadeEdit.style.display = 'block';
                    entidadeEdit.required = true;
                } else {
                    validadeEdit.required = false;
                    entidadeEdit.required = false;
                    entidadeEdit.value = '';
                    campoEntidadeEdit.style.display = 'none';
                }

                validarDatasEditDocumento();
            }

            entidadeEdit.value = botao.getAttribute('data-entidade') || '';

            tipoEdit.onchange = atualizarCamposEditDocumento;
            dataEdit.onchange = validarDatasEditDocumento;
            validadeEdit.onchange = validarDatasEditDocumento;

            atualizarCamposEditDocumento();
        });
    }

    const params = new URLSearchParams(window.location.search);

    if (params.get('apagado') === '1' && modalSucesso) {
        new bootstrap.Modal(modalSucesso).show();

        window.history.replaceState({}, document.title, window.location.pathname);
    }

    if (params.get('editado') === '1' && modalEditadoDocumento) {
        new bootstrap.Modal(modalEditadoDocumento).show();

        window.history.replaceState({}, document.title, window.location.pathname);
    }
    if (params.get('duplicado') === '1' && modalDocumentoDuplicado) {
        new bootstrap.Modal(modalDocumentoDuplicado).show();

        window.history.replaceState({}, document.title, window.location.pathname);
    }

    if (params.get('pdf_nao_encontrado') === '1' && modalPdfNaoEncontrado) {
        new bootstrap.Modal(modalPdfNaoEncontrado).show();

        window.history.replaceState({}, document.title, window.location.pathname);
    }
    const modalValidadeObrigatoria = document.getElementById('modalValidadeObrigatoria');

    if (params.get('validade_obrigatoria') === '1' && modalValidadeObrigatoria) {
        new bootstrap.Modal(modalValidadeObrigatoria).show();

        window.history.replaceState({}, document.title, window.location.pathname);
    }

});

// Tornar validade e entidade obrigatórias para contrato/garantia
document.addEventListener('DOMContentLoaded', function () {

    const tipoDocumento = document.querySelector('select[name="tipo_documento"]');
    const dataDocumento = document.querySelector('input[name="data_documento"]');
    const dataValidade = document.querySelector('input[name="data_validade"]');
    const campoEntidade = document.getElementById('campo_entidade_responsavel');
    const entidadeResponsavel = document.querySelector('select[name="entidade_responsavel"]');

    if (!tipoDocumento || !dataDocumento || !dataValidade) return;

    function atualizarCamposDocumento() {

        const tipo = tipoDocumento.value;

        if (tipo === 'garantia' || tipo === 'contrato') {

            dataValidade.required = true;

            if (campoEntidade) {
                campoEntidade.style.display = 'block';
            }

            if (entidadeResponsavel) {
                entidadeResponsavel.required = true;
            }

        } else {

            dataValidade.required = false;

            if (campoEntidade) {
                campoEntidade.style.display = 'none';
            }

            if (entidadeResponsavel) {
                entidadeResponsavel.required = false;
                entidadeResponsavel.value = '';
            }
        }

        validarDatasAdicionarDocumento();
    }

    function validarDatasAdicionarDocumento() {

        

        if (
            dataValidade.value !== '' &&
            dataDocumento.value > dataValidade.value
        ) {
            dataValidade.setCustomValidity(
                'A data de validade deve ser posterior à data do documento.'
            );
        } else {
            dataValidade.setCustomValidity('');
        }
    }

    tipoDocumento.addEventListener('change', atualizarCamposDocumento);
    dataDocumento.addEventListener('change', validarDatasAdicionarDocumento);
    dataValidade.addEventListener('change', validarDatasAdicionarDocumento);

    atualizarCamposDocumento();

});
//XXXXXXXXXXXXXXXXXXX
//MÓDULO CONTRATOS E GARANTIAS

// Filtros contratos/garantias
document.addEventListener('DOMContentLoaded', function () {

    const botoesFiltro = document.querySelectorAll('.filtro-contrato');
    const grupos = document.querySelectorAll('.contrato-equipamento-grupo');

    if (!botoesFiltro.length || !grupos.length) return;

    botoesFiltro.forEach(function (botao) {

        botao.addEventListener('click', function () {

            const filtro = this.getAttribute('data-filtro');

            botoesFiltro.forEach(b => b.classList.remove('ativo'));
            this.classList.add('ativo');

            grupos.forEach(function (grupo) {

                const cards = grupo.querySelectorAll('.contrato-card');
                let temVisivel = false;

                cards.forEach(function (card) {

                    const estado = card.getAttribute('data-estado');

                    if (
                        filtro === 'todos' ||
                        estado === 'contrato-' + filtro
                    ) {
                        card.style.display = '';
                        temVisivel = true;
                    } else {
                        card.style.display = 'none';
                    }

                });

                grupo.style.display = temVisivel ? '' : 'none';

            });

        });

    });

});