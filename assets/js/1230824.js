
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
//Fechar as os retangulos quando mudo de aba
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
