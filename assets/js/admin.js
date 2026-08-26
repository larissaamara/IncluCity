const busca = document.getElementById('buscaAdmin');
const filtroStatus = document.getElementById('statusAdmin');
const mensagem = document.getElementById('mensagemAdmin');
const semResultados = document.getElementById('semResultados');
const token = document.querySelector('meta[name="csrf-token"]').content;
const btnConfiguracoes = document.getElementById('btnConfiguracoes');
const btnFecharConfiguracoes = document.getElementById('btnFecharConfiguracoes');
const painelConfiguracoes = document.getElementById('painelConfiguracoes');
const fundoConfiguracoes = document.getElementById('fundoConfiguracoes');

function alternarConfiguracoes(aberto) {
  painelConfiguracoes.classList.toggle('aberto', aberto);
  painelConfiguracoes.setAttribute('aria-hidden', String(!aberto));
  btnConfiguracoes.setAttribute('aria-expanded', String(aberto));
  fundoConfiguracoes.hidden = !aberto;
  document.body.classList.toggle('configuracoes-abertas', aberto);
  if (aberto) btnFecharConfiguracoes.focus();
}

btnConfiguracoes.addEventListener('click', () => alternarConfiguracoes(true));
btnFecharConfiguracoes.addEventListener('click', () => alternarConfiguracoes(false));
fundoConfiguracoes.addEventListener('click', () => alternarConfiguracoes(false));
document.addEventListener('keydown', evento => {
  if (evento.key === 'Escape' && painelConfiguracoes.classList.contains('aberto')) alternarConfiguracoes(false);
});

function normalizar(texto) {
  return String(texto).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
}

function filtrarSolicitacoes() {
  const termo = normalizar(busca.value.trim());
  const status = filtroStatus.value;
  let visiveis = 0;

  document.querySelectorAll('.solicitacao').forEach(card => {
    const correspondeBusca = normalizar(card.dataset.busca).includes(termo);
    const correspondeStatus = status === 'todos' || card.dataset.status === status
      || (status === 'outros' && !['pendente', 'aprovado', 'reprovado'].includes(card.dataset.status));
    card.hidden = !(correspondeBusca && correspondeStatus);
    if (!card.hidden) visiveis++;
  });
  semResultados.hidden = visiveis !== 0;
}

function atualizarResumo() {
  const cards = [...document.querySelectorAll('.solicitacao')];
  const totais = {
    todos: cards.length,
    pendente: cards.filter(card => card.dataset.status === 'pendente').length,
    aprovado: cards.filter(card => card.dataset.status === 'aprovado').length,
    reprovado: cards.filter(card => card.dataset.status === 'reprovado').length
  };
  Object.entries(totais).forEach(([tipo, total]) => {
    const valor = document.querySelector(`[data-resumo="${tipo}"] strong`);
    if (valor) valor.textContent = String(total);
  });
}

busca.addEventListener('input', filtrarSolicitacoes);
filtroStatus.addEventListener('change', filtrarSolicitacoes);
filtroStatus.addEventListener('change', () => {
  document.querySelectorAll('[data-filtrar-status]').forEach(card => {
    const ativo = card.dataset.filtrarStatus === filtroStatus.value;
    card.classList.toggle('ativo', ativo);
    card.setAttribute('aria-pressed', String(ativo));
  });
});

document.querySelectorAll('[data-filtrar-status]').forEach(card => {
  card.addEventListener('click', () => {
    filtroStatus.value = card.dataset.filtrarStatus;
    filtroStatus.dispatchEvent(new Event('change'));
    document.getElementById('listaAdmin').scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
});

function exibirMensagem(texto, erro = false) {
  mensagem.textContent = texto;
  mensagem.className = erro ? 'mensagem erro' : 'mensagem ativa';
}

document.getElementById('listaAdmin').addEventListener('click', async evento => {
  const botao = evento.target.closest('[data-acao]');
  if (!botao) return;
  const card = botao.closest('.solicitacao');
  const acao = botao.dataset.acao;
  const nome = card.querySelector('h2').textContent;
  const perguntas = {
    excluir: `Excluir permanentemente a solicitação “${nome}”?`,
    recusar: `Recusar “${nome}” e impedir sua publicação no mapa?`,
    aprovar: `Aprovar “${nome}” e publicar no mapa?`
  };
  const pergunta = perguntas[acao];
  if (!window.confirm(pergunta)) return;

  card.querySelectorAll('button').forEach(item => item.disabled = true);
  try {
    const resposta = await fetch('../api/admin_locais.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token },
      body: JSON.stringify({ id: Number(card.dataset.id), acao })
    });
    const resultado = await resposta.json();
    if (!resposta.ok) throw new Error(resultado.erro || 'Não foi possível concluir a ação.');
    exibirMensagem(resultado.mensagem);
    if (acao === 'excluir') card.remove();
    else {
      const novoStatus = acao === 'aprovar' ? 'aprovado' : 'reprovado';
      card.dataset.status = novoStatus;
      const status = card.querySelector('.status');
      status.className = `status status-${novoStatus}`;
      status.textContent = novoStatus;
      botao.remove();
      card.querySelectorAll('button').forEach(item => item.disabled = false);
    }
    atualizarResumo();
    filtrarSolicitacoes();
  } catch (erro) {
    exibirMensagem(erro.message, true);
    card.querySelectorAll('button').forEach(item => item.disabled = false);
  }
});

filtrarSolicitacoes();

document.querySelectorAll('.tipo-usuario').forEach(select => {
  select.dataset.valorAnterior = select.value;
  select.addEventListener('change', async () => {
    const linha = select.closest('.usuario-linha');
    const tipo = select.value;
    const descricao = tipo === 'admin' ? 'conceder acesso administrativo' : 'remover o acesso administrativo';
    if (!window.confirm(`Deseja ${descricao} desta conta?`)) {
      select.value = select.dataset.valorAnterior;
      return;
    }
    select.disabled = true;
    try {
      const resposta = await fetch('../api/admin_usuarios.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token },
        body: JSON.stringify({ id: Number(linha.dataset.usuarioId), tipo_usuario: tipo })
      });
      const resultado = await resposta.json();
      if (!resposta.ok) throw new Error(resultado.erro || 'Não foi possível alterar a permissão.');
      select.dataset.valorAnterior = tipo;
      exibirMensagem(resultado.mensagem);
      window.setTimeout(() => window.location.reload(), 500);
    } catch (erro) {
      select.value = select.dataset.valorAnterior;
      exibirMensagem(erro.message, true);
    } finally {
      select.disabled = false;
    }
  });
});
