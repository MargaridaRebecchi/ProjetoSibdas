
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

// Modal editar fornecedor

const modalEditarFornecedor =
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

    modalApagarFornecedor.addEventListener('show.bs.modal', function(event) {

        const botao = event.relatedTarget;

        const id = botao.getAttribute('data-id');
        const nome = botao.getAttribute('data-nome');

        document.getElementById('nomeFornecedorApagar')
            .textContent = nome;

        document.getElementById('confirmarApagarFornecedor')
            .href = 'fornecedores.php?apagar=' + id;

    });

});

