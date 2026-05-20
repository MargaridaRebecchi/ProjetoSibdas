
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