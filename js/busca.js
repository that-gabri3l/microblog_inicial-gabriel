'use strict';

const formularioBusca = document.getElementById('form-busca');
const campoBusca = document.getElementById('campo-busca');
const painelResultados = document.getElementById('resultados');

if (formularioBusca && campoBusca && painelResultados) {
    const tamanhoMinimoBusca = 2;
    let temporizadorBusca;
    let requisicaoAtual;

    function mostrarMensagem(mensagem, classe = 'text-muted') {
        const paragrafo = document.createElement('p');
        paragrafo.className = `${classe} text-center mb-0`;
        paragrafo.textContent = mensagem;
        painelResultados.replaceChildren(paragrafo);
        painelResultados.hidden = false;
        painelResultados.setAttribute('aria-busy', 'false');
    }

    function ocultarResultados() {
        painelResultados.replaceChildren();
        painelResultados.hidden = true;
        painelResultados.setAttribute('aria-busy', 'false');
    }

    function renderizarResultados(resultados) {
        const titulo = document.createElement('h2');
        titulo.className = 'fs-5';
        titulo.append('Resultados: ');

        const contador = document.createElement('span');
        contador.className = 'badge rounded-pill text-bg-success';
        contador.textContent = String(resultados.length);
        titulo.append(contador);

        const lista = document.createElement('div');
        lista.className = 'list-group';

        resultados.forEach((noticia) => {
            const id = Number(noticia.id);
            if (!Number.isSafeInteger(id) || id < 1) {
                return;
            }

            const link = document.createElement('a');
            link.className = 'list-group-item list-group-item-action';
            link.href = `noticia.php?id=${encodeURIComponent(String(id))}`;

            const tituloNoticia = document.createElement('h3');
            tituloNoticia.className = 'fs-6';
            tituloNoticia.textContent = String(noticia.titulo ?? '');

            const resumo = document.createElement('p');
            resumo.className = 'mb-0';
            resumo.textContent = String(noticia.resumo ?? '');

            link.append(tituloNoticia, resumo);
            lista.append(link);
        });

        painelResultados.replaceChildren(titulo, lista);
        painelResultados.hidden = false;
        painelResultados.setAttribute('aria-busy', 'false');

        if (lista.childElementCount === 0) {
            mostrarMensagem('Nenhuma notícia encontrada.');
        }
    }

    async function buscarNoticias() {
        const termo = campoBusca.value.trim();

        if (requisicaoAtual) {
            requisicaoAtual.abort();
            requisicaoAtual = null;
        }

        if (termo.length === 0) {
            ocultarResultados();
            return;
        }

        if (termo.length < tamanhoMinimoBusca) {
            mostrarMensagem(`Digite pelo menos ${tamanhoMinimoBusca} caracteres para buscar.`);
            return;
        }

        const requisicao = new AbortController();
        requisicaoAtual = requisicao;
        mostrarMensagem('Buscando notícias...');
        painelResultados.setAttribute('aria-busy', 'true');

        try {
            const resposta = await fetch('resultados.php', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: new URLSearchParams({ busca: termo }),
                signal: requisicao.signal
            });

            if (!resposta.ok) {
                throw new Error('A busca retornou um erro.');
            }

            const dados = await resposta.json();
            if (!Array.isArray(dados.resultados)) {
                throw new Error('A resposta da busca é inválida.');
            }

            if (requisicaoAtual === requisicao) {
                renderizarResultados(dados.resultados);
            }
        } catch (erro) {
            if (erro.name !== 'AbortError' && requisicaoAtual === requisicao) {
                mostrarMensagem('Não foi possível realizar a busca. Tente novamente.', 'text-danger');
            }
        } finally {
            if (requisicaoAtual === requisicao) {
                requisicaoAtual = null;
                painelResultados.setAttribute('aria-busy', 'false');
            }
        }
    }

    campoBusca.addEventListener('input', () => {
        window.clearTimeout(temporizadorBusca);
        if (requisicaoAtual) {
            requisicaoAtual.abort();
            requisicaoAtual = null;
        }

        temporizadorBusca = window.setTimeout(buscarNoticias, 300);
    });

    formularioBusca.addEventListener('submit', (evento) => {
        evento.preventDefault();
        window.clearTimeout(temporizadorBusca);
        buscarNoticias();
    });

    campoBusca.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape') {
            campoBusca.value = '';
            if (requisicaoAtual) {
                requisicaoAtual.abort();
                requisicaoAtual = null;
            }
            ocultarResultados();
        }
    });

    document.addEventListener('click', (evento) => {
        if (!formularioBusca.contains(evento.target) && !painelResultados.contains(evento.target)) {
            ocultarResultados();
        }
    });
}

