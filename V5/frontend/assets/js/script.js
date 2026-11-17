const API = "http://localhost:8000";
const QUANTIDADE_POR_PAGINA = 12;

const canais = [
    { nome: "Unoesc", handle: "@unoescoficial" },
    { nome: "Google for Developers", handle: "@GoogleDevelopers" },
    { nome: "NASA", handle: "@NASA" }
];

const selectCanal = document.querySelector("#canal");
const campoPesquisa = document.querySelector("#pesquisa");
const areaVideos = document.querySelector("#videos");
const infoCanal = document.querySelector("#infoCanal");
const loading = document.querySelector("#loading");
const mensagem = document.querySelector("#mensagem");
const botaoCarregarMais = document.querySelector("#carregarMais");
const modalVideo = document.querySelector("#modalVideo");
const playerVideo = document.querySelector("#playerVideo");
const fecharModal = document.querySelector("#fecharModal");

let playlistUploads = null;
let proximaPagina = null;
let videosCarregados = [];

document.addEventListener("DOMContentLoaded", iniciar);

async function iniciar() {
    carregarListaCanais();

    selectCanal.addEventListener("change", carregarCanalSelecionado);
    campoPesquisa.addEventListener("input", filtrarVideos);
    botaoCarregarMais.addEventListener("click", carregarMaisVideos);
    fecharModal.addEventListener("click", fecharVideo);

    modalVideo.addEventListener("click", evento => {
        if (evento.target === modalVideo) fecharVideo();
    });

    modalVideo.addEventListener("close", () => {
        playerVideo.src = "";
    });

    await carregarCanalSelecionado();
}

function carregarListaCanais() {
    canais.forEach((canal, indice) => {
        const option = document.createElement("option");
        option.value = indice;
        option.textContent = canal.nome;
        selectCanal.appendChild(option);
    });
}

async function carregarCanalSelecionado() {
    limparEstado();
    mostrarLoading(true);

    try {
        const canal = canais[selectCanal.value];
        const dadosCanal = await buscarCanal(canal.handle);

        infoCanal.innerHTML = `
            <h2>${escaparHTML(dadosCanal.titulo)}</h2>
            <p>Últimos vídeos publicados</p>
        `;

        playlistUploads = dadosCanal.playlist;
        await carregarVideos();

    } catch (erro) {
        console.error(erro);
        mostrarMensagem(erro.message, "erro");

    } finally {
        mostrarLoading(false);
    }
}

async function buscarCanal(handle) {
    try {
        const parametros = new URLSearchParams({ handle });
        const resposta = await fetch(`${API}/api/canal?${parametros}`);

        if (!resposta.ok) {
            const erro = await resposta.json();
            console.error("Erro retornado pelo FastAPI (Canal):", erro);
            throw new Error(erro.detail ?? "Erro ao carregar o canal.");
        }

        return await resposta.json();
    } catch (apiErro) {
        console.error("Falha na requisição HTTP para o backend:", apiErro);
        throw apiErro;
    }
}

async function carregarVideos() {
    esconderMensagem();

    const parametros = new URLSearchParams({
        playlist: playlistUploads,
        max_results: QUANTIDADE_POR_PAGINA
    });

    if (proximaPagina)
        parametros.set("page_token", proximaPagina);

    try {
        const resposta = await fetch(`${API}/api/videos?${parametros}`);

        if (!resposta.ok) {
            const erro = await resposta.json();
            console.error("Erro retornado pelo FastAPI (Vídeos):", erro);
            throw new Error(erro.detail ?? "Erro ao carregar os vídeos.");
        }

        const dados = await resposta.json();

        proximaPagina = dados.nextPageToken ?? null;
        videosCarregados.push(...dados.videos);

        atualizarBotaoCarregarMais();
        filtrarVideos();
    } catch (apiErro) {
        console.error("Falha na requisição HTTP para o backend:", apiErro);
        mostrarMensagem("Não foi possível conectar ao servidor local.", "erro");
    }
}

async function carregarMaisVideos() {
    if (!proximaPagina) return;

    botaoCarregarMais.disabled = true;
    botaoCarregarMais.textContent = "Carregando...";

    try {
        await carregarVideos();

    } catch (erro) {
        console.error(erro);
        mostrarMensagem(erro.message, "erro");

    } finally {
        botaoCarregarMais.disabled = false;
        botaoCarregarMais.textContent = "Carregar mais";
    }
}

function filtrarVideos() {
    const termo = campoPesquisa.value.trim().toLowerCase();

    const encontrados = videosCarregados.filter(video =>
        video.titulo.toLowerCase().includes(termo) ||
        video.descricao.toLowerCase().includes(termo)
    );

    mostrarVideos(encontrados);
}

function mostrarVideos(videos) {
    areaVideos.innerHTML = "";

    if (!videos.length) {
        mostrarMensagem("Nenhum vídeo encontrado.", "aviso");
        return;
    }

    esconderMensagem();

    videos.forEach(video => {
        const artigo = document.createElement("article");
        artigo.className = "video-card";

        artigo.innerHTML = `
            <div class="video-thumb">
                <img src="${video.thumbnail}"
                     alt="Miniatura do vídeo ${escaparHTML(video.titulo)}"
                     loading="lazy">

                <div class="video-overlay">
                    <p>${escaparHTML(limitarTexto(video.descricao, 180))}</p>
                </div>
            </div>

            <div class="video-conteudo">
                <h3>${escaparHTML(video.titulo)}</h3>

                <span class="video-data">
                    Publicado em ${formatarData(video.publicadoEm)}
                </span>

                <div class="video-acoes">
                    <button class="assistir">Assistir</button>

                    <a class="abrir-youtube"
                       href="https://www.youtube.com/watch?v=${video.videoId}"
                       target="_blank"
                       rel="noopener noreferrer">
                        Abrir no YouTube
                    </a>
                </div>
            </div>
        `;

        artigo.querySelector(".video-thumb").addEventListener(
            "click",
            () => abrirVideo(video.videoId)
        );

        artigo.querySelector(".assistir").addEventListener(
            "click",
            () => abrirVideo(video.videoId)
        );

        areaVideos.appendChild(artigo);
    });
}

function formatarData(data) {
    return new Intl.DateTimeFormat("pt-BR", {
        day: "2-digit",
        month: "long",
        year: "numeric"
    }).format(new Date(data));
}

function limitarTexto(texto, limite) {
    return texto.length <= limite ?
        texto :
        texto.substring(0, limite) + "...";
}

function escaparHTML(texto) {
    return String(texto)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function abrirVideo(videoId) {
    playerVideo.src = `https://www.youtube.com/embed/${videoId}?autoplay=1`;
    modalVideo.showModal();
}

function fecharVideo() {
    modalVideo.close();
}

function mostrarLoading(exibir) {
    loading.classList.toggle("oculto", !exibir);
}

function mostrarMensagem(texto, tipo = "aviso") {
    mensagem.textContent = texto;
    mensagem.className = `mensagem ${tipo}`;
}

function esconderMensagem() {
    mensagem.className = "mensagem oculto";
}

function atualizarBotaoCarregarMais() {
    botaoCarregarMais.classList.toggle("oculto", !proximaPagina);
}

function limparEstado() {
    playlistUploads = null;
    proximaPagina = null;
    videosCarregados = [];
    areaVideos.innerHTML = "";
    infoCanal.innerHTML = "";
    campoPesquisa.value = "";

    esconderMensagem();
    atualizarBotaoCarregarMais();
}
