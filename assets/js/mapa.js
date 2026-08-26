const map = L.map('map').setView([-23.2237, -45.9009], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
setTimeout(() => map.invalidateSize(), 300);
window.addEventListener('resize', () => map.invalidateSize());

let locais = [];
let marcadores = [];
let marcadorSelecao = null;
let selecionandoNoMapa = false;

const modal = document.getElementById('formAdicionarLocal');
const formulario = document.getElementById('formLocal');
const menu = document.getElementById('menuMapa');
const erroFormulario = document.getElementById('erroFormulario');

function escaparHtml(valor) {
  const elemento = document.createElement('div');
  elemento.textContent = String(valor ?? '');
  return elemento.innerHTML;
}

function conteudoLocal(local) {
  const categorias = (local.categorias || []).map(escaparHtml).join(' • ');
  const recursos = (local.recursos || []).map(escaparHtml).join(', ');
  return `<div class="popup-local"><strong>${escaparHtml(local.nome)}</strong><span>${categorias}</span><p>${escaparHtml(local.endereco)}, ${escaparHtml(local.numero)} — ${escaparHtml(local.bairro)}</p><small><b>Recursos:</b> ${recursos}</small></div>`;
}

function renderizar(lista = locais) {
  const container = document.getElementById('listaLocais');
  container.replaceChildren();
  marcadores.forEach(marcador => map.removeLayer(marcador));
  marcadores = [];
  lista.forEach(local => {
    const marcador = L.marker([local.lat, local.lng]).addTo(map).bindPopup(conteudoLocal(local));
    marcadores.push(marcador);
    const item = document.createElement('button');
    item.type = 'button'; item.className = 'local';
    const categorias = local.categorias || [];
    const recursos = local.recursos || [];
    const recursosVisiveis = recursos.slice(0, 3);
    const quantidadeRestante = recursos.length - recursosVisiveis.length;
    item.innerHTML = `
      <span class="local-categoria">${categorias.map(escaparHtml).join(' • ') || 'Local'}</span>
      <strong class="local-nome">${escaparHtml(local.nome)}</strong>
      <span class="local-endereco"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>${escaparHtml(local.bairro)} — ${escaparHtml(local.cidade)}</span>
      <span class="local-recursos">${recursosVisiveis.map(recurso => `<span>${escaparHtml(recurso)}</span>`).join('')}${quantidadeRestante > 0 ? `<span>+${quantidadeRestante}</span>` : ''}</span>
      <span class="local-acao">Ver no mapa <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>`;
    item.setAttribute('aria-label', `Ver ${local.nome} no mapa`);
    item.addEventListener('click', () => { map.setView([local.lat, local.lng], 16); marcador.openPopup(); });
    container.appendChild(item);
  });
  if (!lista.length) container.textContent = 'Nenhum local aprovado encontrado.';
}

async function carregarLocais() {
  try {
    const resposta = await fetch('../api/locais.php', { headers: { Accept: 'application/json' } });
    if (!resposta.ok) throw new Error('Não foi possível carregar os locais.');
    locais = (await resposta.json()).locais || [];
    renderizar();
  } catch (erro) {
    document.getElementById('listaLocais').textContent = erro.message;
  }
}

function filtrar() {
  const categoria = document.getElementById('filtroCategoria').value;
  const deficiencia = document.getElementById('filtroDeficiencia').value;
  const recurso = document.getElementById('filtroRecurso').value;

  const recursosPorDeficiencia = {
    fisica: ['Banheiro acessível', 'Rampa de acesso', 'Elevador acessível', 'Entrada acessível', 'Vaga acessível', 'Espaço para cadeira de rodas', 'Atendimento prioritário', 'Balcão acessível', 'Corrimão'],
    visual: ['Piso tátil', 'Sinalização acessível', 'Braile', 'Audiodescrição', 'Comunicação acessível', 'Cão-guia permitido'],
    auditiva: ['Libras', 'Sinalização acessível', 'Comunicação acessível'],
    cognitiva: ['Sala de conforto', 'Atendimento prioritário', 'Sinalização acessível', 'Comunicação acessível']
  };

  renderizar(locais.filter(local => {
    const deficiencias = local.deficiencias || [];
    const correspondeCategoria = categoria === 'todos' || local.categorias.includes(categoria);
    const correspondeRecurso = recurso === 'todos' || local.recursos.includes(recurso);
    const recursosRelacionados = recursosPorDeficiencia[deficiencia] || [];
    const correspondeDeficiencia = deficiencia === 'todos'
      || deficiencias.includes(deficiencia)
      || (!deficiencias.length && local.recursos.some(item => recursosRelacionados.includes(item)));

    return correspondeCategoria && correspondeRecurso && correspondeDeficiencia;
  }));
}
window.filtrar = filtrar;

function alternarMenu(aberto) {
  menu.classList.toggle('aberto', aberto); menu.setAttribute('aria-hidden', String(!aberto));
  document.getElementById('btnMenuMapa').setAttribute('aria-expanded', String(aberto));
}
document.getElementById('btnMenuMapa').addEventListener('click', () => alternarMenu(true));
document.getElementById('btnFecharMenu').addEventListener('click', () => alternarMenu(false));
document.getElementById('btnAdicionarLocal').addEventListener('click', () => { alternarMenu(false); modal.style.display = 'flex'; document.getElementById('nome').focus(); });

function fecharFormulario() { modal.style.display = 'none'; selecionandoNoMapa = false; document.body.classList.remove('selecao-mapa'); }
document.getElementById('btnFechar').addEventListener('click', fecharFormulario);
document.getElementById('btnCancelar').addEventListener('click', fecharFormulario);
modal.addEventListener('click', evento => { if (evento.target === modal) fecharFormulario(); });

function controlarOutro(nome, campoId, inputId) {
  document.querySelectorAll(`input[name="${nome}[]"]`).forEach(input => input.addEventListener('change', () => {
    const ativo = [...document.querySelectorAll(`input[name="${nome}[]"]:checked`)].some(item => item.value === 'Outro');
    document.getElementById(campoId).classList.toggle('visivel', ativo);
    document.getElementById(inputId).required = ativo;
  }));
}
controlarOutro('categorias', 'campoOutraCategoria', 'outraCategoria');
controlarOutro('recursos', 'campoOutroRecurso', 'outroRecurso');

document.getElementById('btnSelecionarMapa').addEventListener('click', () => {
  selecionandoNoMapa = true; modal.style.display = 'none'; document.body.classList.add('selecao-mapa');
  document.getElementById('localizacaoStatus').textContent = 'Clique no ponto exato do mapa.';
});
map.on('click', evento => {
  if (!selecionandoNoMapa) return;
  selecionandoNoMapa = false; document.body.classList.remove('selecao-mapa');
  if (marcadorSelecao) map.removeLayer(marcadorSelecao);
  marcadorSelecao = L.marker(evento.latlng).addTo(map).bindPopup('Local selecionado').openPopup();
  document.getElementById('latitude').value = evento.latlng.lat.toFixed(7);
  document.getElementById('longitude').value = evento.latlng.lng.toFixed(7);
  const status = document.getElementById('localizacaoStatus'); status.textContent = 'Localização selecionada no mapa.'; status.classList.add('ok');
  modal.style.display = 'flex';
});

document.getElementById('fotos').addEventListener('change', evento => {
  const preview = document.getElementById('previewFotos'); preview.replaceChildren();
  const arquivos = [...evento.target.files];
  if (arquivos.length > 8) { erroFormulario.textContent = 'Selecione no máximo 8 fotos.'; evento.target.value = ''; return; }
  erroFormulario.textContent = '';
  arquivos.forEach((arquivo, indice) => {
    if (arquivo.size > 5 * 1024 * 1024) { erroFormulario.textContent = 'Cada foto deve ter no máximo 5 MB.'; return; }
    const figura = document.createElement('figure'); const imagem = document.createElement('img');
    imagem.src = URL.createObjectURL(arquivo); imagem.alt = `Prévia da foto ${indice + 1}`;
    imagem.addEventListener('load', () => URL.revokeObjectURL(imagem.src), { once: true }); figura.appendChild(imagem); preview.appendChild(figura);
  });
});

async function geocodificarEndereco() {
  const partes = ['endereco', 'numero', 'bairro', 'cidade', 'estado', 'cep'].map(id => document.getElementById(id).value.trim()).filter(Boolean);
  const resposta = await fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=br&q=${encodeURIComponent(partes.join(', '))}`);
  if (!resposta.ok) throw new Error('Não foi possível consultar o endereço.');
  const dados = await resposta.json();
  if (!dados.length) throw new Error('Endereço não encontrado. Selecione o ponto diretamente no mapa.');
  return { latitude: dados[0].lat, longitude: dados[0].lon };
}

formulario.addEventListener('submit', async evento => {
  evento.preventDefault(); erroFormulario.textContent = '';
  const categorias = formulario.querySelectorAll('input[name="categorias[]"]:checked');
  const deficiencias = formulario.querySelectorAll('input[name="deficiencias[]"]:checked');
  const recursos = formulario.querySelectorAll('input[name="recursos[]"]:checked');
  if (!categorias.length || !deficiencias.length || !recursos.length) { erroFormulario.textContent = 'Selecione ao menos uma categoria, uma deficiência atendida e um recurso de acessibilidade.'; return; }
  const botao = formulario.querySelector('.btn-enviar'); botao.disabled = true; botao.textContent = 'Enviando…';
  try {
    if (!document.getElementById('latitude').value) {
      const coordenadas = await geocodificarEndereco(); document.getElementById('latitude').value = coordenadas.latitude; document.getElementById('longitude').value = coordenadas.longitude;
    }
    const dados = new FormData(formulario);
    const resposta = await fetch('../api/locais.php', { method: 'POST', headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }, body: dados });
    const resultado = await resposta.json(); if (!resposta.ok) throw new Error(resultado.erro || 'Falha ao enviar solicitação.');
    fecharFormulario(); formulario.reset(); document.getElementById('previewFotos').replaceChildren(); document.getElementById('confirmacaoSolicitacao').classList.add('ativa');
    document.getElementById('localizacaoStatus').textContent = 'Localização ainda não selecionada.';
    if (marcadorSelecao) { map.removeLayer(marcadorSelecao); marcadorSelecao = null; }
  } catch (erro) { erroFormulario.textContent = erro.message; }
  finally { botao.disabled = false; botao.textContent = 'Enviar solicitação'; }
});

document.getElementById('btnFecharConfirmacao').addEventListener('click', () => document.getElementById('confirmacaoSolicitacao').classList.remove('ativa'));
carregarLocais();
