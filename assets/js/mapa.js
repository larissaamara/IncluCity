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
const buscaLocal = document.getElementById('buscaLocal');
const resumoLocais = document.getElementById('resumoLocais');
const btnLimparFiltros = document.getElementById('btnLimparFiltros');
const etapasFormulario = [...document.querySelectorAll('.form-etapa')];
const indicadoresFormulario = [...document.querySelectorAll('.progresso-formulario li')];
let etapaFormularioAtual = 0;

function escaparHtml(valor) {
  const elemento = document.createElement('div');
  elemento.textContent = String(valor ?? '');
  return elemento.innerHTML;
}

function destacarNome(nome, termoBusca) {
  if (!termoBusca) return escaparHtml(nome);

  const nomeOriginal = String(nome ?? '');
  const inicio = normalizarTexto(nomeOriginal).indexOf(termoBusca);
  if (inicio === -1) return escaparHtml(nomeOriginal);

  const fim = inicio + termoBusca.length;
  return `${escaparHtml(nomeOriginal.slice(0, inicio))}<mark>${escaparHtml(nomeOriginal.slice(inicio, fim))}</mark>${escaparHtml(nomeOriginal.slice(fim))}`;
}

function urlFoto(caminho) {
  const caminhoSeguro = String(caminho ?? '').replace(/\\/g, '/');
  if (!caminhoSeguro.startsWith('assets/uploads/') || caminhoSeguro.includes('..')) return '';
  return `../${escaparHtml(caminhoSeguro)}`;
}

function conteudoLocal(local) {
  const categorias = (local.categorias || []).map(escaparHtml).join(' • ');
  const recursos = (local.recursos || []).map(escaparHtml).join(', ');
  const foto = urlFoto((local.fotos || [])[0]);
  return `<div class="popup-local">${foto ? `<img class="popup-local-foto" src="${foto}" alt="Foto de ${escaparHtml(local.nome)}">` : ''}<strong>${escaparHtml(local.nome)}</strong><span>${categorias}</span><p>${escaparHtml(local.endereco)}, ${escaparHtml(local.numero)} — ${escaparHtml(local.bairro)}</p><small><b>Recursos:</b> ${recursos}</small></div>`;
}

function renderizar(lista = locais, filtrosAtivos = false, termoBusca = '') {
  const container = document.getElementById('listaLocais');
  container.replaceChildren();
  marcadores.forEach(marcador => map.removeLayer(marcador));
  marcadores = [];
  lista.forEach(local => {
    const marcador = L.marker([local.lat, local.lng]).addTo(map).bindPopup(conteudoLocal(local));
    if (termoBusca) {
      marcador.setZIndexOffset(1000);
      marcador.getElement()?.classList.add('marcador-destaque');
    }
    marcadores.push(marcador);
    const item = document.createElement('button');
    item.type = 'button'; item.className = termoBusca ? 'local local-destaque' : 'local';
    const categorias = local.categorias || [];
    const recursos = local.recursos || [];
    const foto = urlFoto((local.fotos || [])[0]);
    const recursosVisiveis = recursos.slice(0, 3);
    const quantidadeRestante = recursos.length - recursosVisiveis.length;
    item.innerHTML = `
      ${foto ? `<img class="local-foto" src="${foto}" alt="Foto de ${escaparHtml(local.nome)}" loading="lazy" decoding="async">` : ''}
      <span class="local-categoria">${categorias.map(escaparHtml).join(' • ') || 'Local'}</span>
      <strong class="local-nome">${destacarNome(local.nome, termoBusca)}</strong>
      <span class="local-endereco"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>${escaparHtml(local.bairro)} — ${escaparHtml(local.cidade)}</span>
      <span class="local-recursos">${recursosVisiveis.map(recurso => `<span>${escaparHtml(recurso)}</span>`).join('')}${quantidadeRestante > 0 ? `<span>+${quantidadeRestante}</span>` : ''}</span>
      <span class="local-acao">Ver no mapa <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>`;
    item.setAttribute('aria-label', `Ver ${local.nome} no mapa`);
    item.addEventListener('click', () => { map.setView([local.lat, local.lng], 16); marcador.openPopup(); });
    container.appendChild(item);
  });
  if (!lista.length) {
    container.textContent = filtrosAtivos
      ? 'Nenhum local corresponde à sua pesquisa.'
      : 'Nenhum local aprovado encontrado.';
  }

  resumoLocais.textContent = lista.length === 1
    ? '1 local encontrado'
    : `${lista.length} locais encontrados`;
  btnLimparFiltros.hidden = !filtrosAtivos;
}

async function carregarLocais() {
  try {
    const resposta = await fetch('../api/locais.php', { headers: { Accept: 'application/json' } });
    if (!resposta.ok) throw new Error('Não foi possível carregar os locais.');
    locais = (await resposta.json()).locais || [];
    filtrar();
  } catch (erro) {
    document.getElementById('listaLocais').textContent = erro.message;
    resumoLocais.textContent = 'Não foi possível exibir os resultados.';
    btnLimparFiltros.hidden = true;
  }
}

function filtrar() {
  const categoria = document.getElementById('filtroCategoria').value;
  const deficiencia = document.getElementById('filtroDeficiencia').value;
  const recurso = document.getElementById('filtroRecurso').value;
  const termoBusca = normalizarTexto(buscaLocal.value.trim());
  const filtrosAtivos = termoBusca !== '' || categoria !== 'todos' || deficiencia !== 'todos' || recurso !== 'todos';

  const recursosPorDeficiencia = {
    fisica: ['Banheiro acessível', 'Rampa de acesso', 'Elevador acessível', 'Entrada acessível', 'Vaga acessível', 'Espaço para cadeira de rodas', 'Atendimento prioritário', 'Balcão acessível', 'Corrimão'],
    visual: ['Piso tátil', 'Sinalização acessível', 'Braile', 'Audiodescrição', 'Comunicação acessível', 'Cão-guia permitido'],
    auditiva: ['Libras', 'Sinalização acessível', 'Comunicação acessível'],
    cognitiva: ['Sala de conforto', 'Atendimento prioritário', 'Sinalização acessível', 'Comunicação acessível']
  };

  renderizar(locais.filter(local => {
    const categorias = local.categorias || [];
    const deficiencias = local.deficiencias || [];
    const recursos = local.recursos || [];
    const correspondeNome = termoBusca === '' || normalizarTexto(local.nome).includes(termoBusca);
    const correspondeCategoria = categoria === 'todos' || categorias.includes(categoria);
    const correspondeRecurso = recurso === 'todos' || recursos.includes(recurso);
    const recursosRelacionados = recursosPorDeficiencia[deficiencia] || [];
    const correspondeDeficiencia = deficiencia === 'todos'
      || deficiencias.includes(deficiencia)
      || (!deficiencias.length && recursos.some(item => recursosRelacionados.includes(item)));

    return correspondeNome && correspondeCategoria && correspondeRecurso && correspondeDeficiencia;
  }), filtrosAtivos, termoBusca);
}
window.filtrar = filtrar;

function normalizarTexto(valor) {
  return String(valor ?? '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLocaleLowerCase('pt-BR');
}

buscaLocal.addEventListener('input', filtrar);
buscaLocal.addEventListener('keydown', evento => {
  if (evento.key === 'Escape' && buscaLocal.value) {
    buscaLocal.value = '';
    filtrar();
  }
});

btnLimparFiltros.addEventListener('click', () => {
  buscaLocal.value = '';
  document.getElementById('filtroCategoria').value = 'todos';
  document.getElementById('filtroDeficiencia').value = 'todos';
  document.getElementById('filtroRecurso').value = 'todos';
  filtrar();
  buscaLocal.focus();
});

function alternarMenu(aberto) {
  menu.classList.toggle('aberto', aberto); menu.setAttribute('aria-hidden', String(!aberto));
  document.getElementById('btnMenuMapa').setAttribute('aria-expanded', String(aberto));
}
document.getElementById('btnMenuMapa').addEventListener('click', () => alternarMenu(true));
document.getElementById('btnFecharMenu').addEventListener('click', () => alternarMenu(false));
document.getElementById('btnAdicionarLocal').addEventListener('click', () => {
  alternarMenu(false);
  exibirEtapaFormulario(0, false);
  modal.style.display = 'flex';
  document.getElementById('nome').focus();
});

function fecharFormulario(devolverFoco = true) {
  modal.style.display = 'none';
  selecionandoNoMapa = false;
  document.body.classList.remove('selecao-mapa');
  if (devolverFoco) document.getElementById('btnAdicionarLocal').focus();
}
document.getElementById('btnFechar').addEventListener('click', fecharFormulario);
document.getElementById('btnCancelar').addEventListener('click', fecharFormulario);
modal.addEventListener('click', evento => { if (evento.target === modal) fecharFormulario(); });

function exibirEtapaFormulario(indice, moverFoco = true) {
  etapaFormularioAtual = Math.max(0, Math.min(indice, etapasFormulario.length - 1));
  etapasFormulario.forEach((etapa, posicao) => { etapa.hidden = posicao !== etapaFormularioAtual; });
  indicadoresFormulario.forEach((indicador, posicao) => {
    indicador.classList.toggle('ativo', posicao <= etapaFormularioAtual);
    if (posicao === etapaFormularioAtual) indicador.setAttribute('aria-current', 'step');
    else indicador.removeAttribute('aria-current');
  });
  erroFormulario.textContent = '';
  formulario.scrollTo({ top: 0, behavior: 'smooth' });

  if (moverFoco) {
    const titulo = etapasFormulario[etapaFormularioAtual].querySelector('legend');
    titulo?.setAttribute('tabindex', '-1');
    titulo?.focus({ preventScroll: true });
  }
}

function erroNaEtapa(mensagem, seletor) {
  erroFormulario.textContent = mensagem;
  erroFormulario.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  document.querySelector(seletor)?.focus({ preventScroll: true });
  return false;
}

function validarEtapaFormulario(indice) {
  const etapa = etapasFormulario[indice];
  const campoInvalido = [...etapa.querySelectorAll('input, select, textarea')]
    .find(campo => !campo.disabled && !campo.checkValidity());
  if (campoInvalido) {
    campoInvalido.reportValidity();
    campoInvalido.focus();
    return false;
  }
  if (indice === 0 && !formulario.querySelector('input[name="categorias[]"]:checked')) {
    return erroNaEtapa('Escolha pelo menos um tipo de local para continuar.', 'input[name="categorias[]"]');
  }
  if (indice === 1 && !formulario.querySelector('input[name="recursos[]"]:checked')) {
    return erroNaEtapa('Escolha pelo menos um recurso de acessibilidade que você observou.', 'input[name="recursos[]"]');
  }
  return true;
}

document.querySelectorAll('[data-proxima]').forEach(botao => botao.addEventListener('click', () => {
  if (validarEtapaFormulario(etapaFormularioAtual)) exibirEtapaFormulario(etapaFormularioAtual + 1);
}));
document.querySelectorAll('[data-anterior]').forEach(botao => botao.addEventListener('click', () => {
  exibirEtapaFormulario(etapaFormularioAtual - 1);
}));

modal.addEventListener('keydown', evento => {
  if (evento.key === 'Escape') {
    fecharFormulario();
    return;
  }
  if (evento.key !== 'Tab') return;
  const focaveis = [...modal.querySelectorAll('button, input, select, textarea, [tabindex]:not([tabindex="-1"])')]
    .filter(elemento => !elemento.disabled && elemento.offsetParent !== null);
  if (!focaveis.length) return;
  const primeiro = focaveis[0];
  const ultimo = focaveis[focaveis.length - 1];
  if (evento.shiftKey && document.activeElement === primeiro) {
    evento.preventDefault();
    ultimo.focus();
  } else if (!evento.shiftKey && document.activeElement === ultimo) {
    evento.preventDefault();
    primeiro.focus();
  }
});

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
  if (etapaFormularioAtual < etapasFormulario.length - 1) {
    if (validarEtapaFormulario(etapaFormularioAtual)) exibirEtapaFormulario(etapaFormularioAtual + 1);
    return;
  }
  if (!validarEtapaFormulario(etapaFormularioAtual)) return;
  const categorias = formulario.querySelectorAll('input[name="categorias[]"]:checked');
  const recursos = formulario.querySelectorAll('input[name="recursos[]"]:checked');
  if (!categorias.length || !recursos.length) {
    const etapaComErro = !categorias.length ? 0 : 1;
    exibirEtapaFormulario(etapaComErro);
    validarEtapaFormulario(etapaComErro);
    return;
  }
  const botao = formulario.querySelector('.btn-enviar'); botao.disabled = true; botao.textContent = 'Enviando…';
  try {
    if (!document.getElementById('latitude').value) {
      const coordenadas = await geocodificarEndereco(); document.getElementById('latitude').value = coordenadas.latitude; document.getElementById('longitude').value = coordenadas.longitude;
    }
    const dados = new FormData(formulario);
    const resposta = await fetch('../api/locais.php', { method: 'POST', headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content }, body: dados });
    const resultado = await resposta.json(); if (!resposta.ok) throw new Error(resultado.erro || 'Falha ao enviar solicitação.');
    fecharFormulario(false); formulario.reset(); exibirEtapaFormulario(0, false); document.getElementById('previewFotos').replaceChildren(); document.getElementById('confirmacaoSolicitacao').classList.add('ativa');
    document.getElementById('localizacaoStatus').textContent = 'Localização ainda não selecionada.';
    document.getElementById('localizacaoStatus').classList.remove('ok');
    document.querySelectorAll('.condicional').forEach(campo => campo.classList.remove('visivel'));
    document.getElementById('outraCategoria').required = false;
    document.getElementById('outroRecurso').required = false;
    if (marcadorSelecao) { map.removeLayer(marcadorSelecao); marcadorSelecao = null; }
    document.getElementById('tituloConfirmacao').setAttribute('tabindex', '-1');
    document.getElementById('tituloConfirmacao').focus();
  } catch (erro) {
    if (normalizarTexto(erro.message).includes('endereco')) exibirEtapaFormulario(0);
    erroFormulario.textContent = erro.message;
    erroFormulario.focus();
  }
  finally { botao.disabled = false; botao.textContent = 'Enviar local para análise'; }
});

document.getElementById('btnFecharConfirmacao').addEventListener('click', () => {
  document.getElementById('confirmacaoSolicitacao').classList.remove('ativa');
  document.getElementById('btnAdicionarLocal').focus();
});
carregarLocais();
