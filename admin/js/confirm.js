'use strict';

const botoesExcluir = document.querySelectorAll('.excluir');
const modalExclusao = document.querySelector("#modal-exclusao");
const botaoSim = modalExclusao.querySelector("#sim");
const botaoNao = modalExclusao.querySelector("#nao");

let formularioSelecionado = null;

for (const botao of botoesExcluir) {
    botao.addEventListener("click", function() {
        formularioSelecionado = botao.closest("form");
        modalExclusao.showModal();
    });
}

botaoSim.addEventListener("click", function() {
    if (formularioSelecionado !== null) {
        const formulario = formularioSelecionado;
        formularioSelecionado = null;
        formulario.submit();
    }
});

botaoNao.addEventListener("click", function() {
    modalExclusao.close();
});

// O evento close também ocorre quando o diálogo é fechado com Escape.
modalExclusao.addEventListener("close", function() {
    formularioSelecionado = null;
});
