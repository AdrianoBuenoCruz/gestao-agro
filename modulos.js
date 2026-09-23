// navegação do menu lateral

const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || "";

function escaparHtml(valor) {
  return String(valor ?? "").replace(/[&<>'"]/g, caractere => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#039;", '"': "&quot;"
  })[caractere]);
}

function dataBr(valor) {
  return valor ? String(valor).substring(0, 10).split("-").reverse().join("/") : "-";
}

function horaBr(valor) {
  return valor ? String(valor).substring(0, 5) : "-";
}

function moedaBr(valor) {
  return Number(valor || 0).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
}

function badgeStatus(status) {
  const texto = escaparHtml(status || "-");
  const classe = ["Ativo", "Concluída", "Concluído"].includes(status)
    ? "is-active"
    : ["Planejada", "Aberta", "Em andamento", "Em manutenção"].includes(status)
      ? "is-warning"
      : ["Cancelada", "Inativo"].includes(status) ? "is-danger" : "is-muted";
  return `<span class="badge-status ${classe}">${texto}</span>`;
}

function notificar(titulo, texto = "", icone = "success") {
  if (window.Swal) {
    return Swal.fire({ title: titulo, text: texto, icon: icone, confirmButtonColor: "#3F6B47" });
  }
  window.alert(texto ? `${titulo}\n${texto}` : titulo);
  return Promise.resolve();
}

async function confirmarAcao(texto) {
  if (!window.Swal) return window.confirm(texto);
  const resposta = await Swal.fire({
    title: "Confirmação", text: texto, icon: "warning", showCancelButton: true,
    confirmButtonText: "Sim, continuar", cancelButtonText: "Cancelar",
    confirmButtonColor: "#A8552F", cancelButtonColor: "#6B6A5C"
  });
  return resposta.isConfirmed;
}

async function apiJson(url, opcoes = {}) {
  const headers = new Headers(opcoes.headers || {});
  headers.set("X-CSRF-Token", CSRF_TOKEN);
  const resposta = await fetch(url, { ...opcoes, headers });
  const dados = await resposta.json().catch(() => ({ status: false, mensagem: "Resposta inválida do servidor." }));
  if (!resposta.ok || dados.status === false) {
    throw new Error(dados.mensagem || "Não foi possível concluir a operação.");
  }
  return dados;
}

function limparFormulario(id) {
  const form = document.getElementById(id);
  if (!form) return;
  form.reset();
  const campoId = form.querySelector('[name="id"]');
  if (campoId) campoId.value = "";
}

function abrirModulo(id, botao) {
  let modulos = document.querySelectorAll(".modulo");
  let itens = document.querySelectorAll(".item");
  let subitens = document.querySelectorAll(".subitem");

  // tira o ativo de todo mundo antes de marcar o novo
  modulos.forEach(modulo => modulo.classList.remove("ativo"));
  itens.forEach(item => item.classList.remove("ativo"));
  subitens.forEach(subitem => subitem.classList.remove("ativo"));

  // e marca só o que foi clicado
  let moduloAlvo = document.getElementById(id);
  if (moduloAlvo) moduloAlvo.classList.add("ativo");
  if (botao) botao.classList.add("ativo");
}

function abrirSubmenu(botao) {
  let subnav = document.getElementById("subnavMaquinas");
  if (botao) botao.classList.toggle("ativo");
  if (subnav) subnav.classList.toggle("ativo");
}


// safra

function salvarSafra(event) {
  event.preventDefault();

  const formData = new FormData();
  formData.append("nome", document.getElementById("nomeSafra").value);
  formData.append("cultura", document.getElementById("cultura").value);
  formData.append("area", document.getElementById("area").value);
  formData.append("talhao", document.getElementById("talhao").value);
  formData.append("data_plantio", document.getElementById("plantio").value);
  formData.append("previsao_colheita", document.getElementById("colheitaPrev").value);

  fetch("safra_salvar.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
    .then(resposta => {
      if (resposta.status) {
        notificar("Safra salva", resposta.mensagem);
        document.getElementById("formSafra").reset();
        listarSafras();
      } else {
        notificar("Erro ao salvar", resposta.mensagem || "Erro desconhecido", "error");
        console.error(resposta);
      }
    })
    .catch(err => {
      notificar("Falha de comunicação", "Não foi possível acessar o servidor.", "error");
      console.error(err);
    });
}

function listarSafras() {
  fetch("safra_listar.php")
    .then(res => res.json())
    .then(data => {
      let tbody = document.getElementById("tbodySafra");
      if (!tbody) return;

      if (!Array.isArray(data) || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-muted small">Nenhuma safra cadastrada ainda.</td></tr>`;
        return;
      }

      tbody.innerHTML = "";
      data.forEach(item => {
        const plantioTexto = item.data_plantio ? item.data_plantio.split("-").reverse().join("/") : "-";
        const colheitaTexto = item.previsao_colheita ? item.previsao_colheita.split("-").reverse().join("/") : "-";

        tbody.innerHTML += `
          <tr>
            <td><strong>${item.nome || "-"}</strong></td>
            <td>${item.cultura || "-"}</td>
            <td>${item.area ?? "-"}</td>
            <td>${item.talhao || "-"}</td>
            <td>${plantioTexto}</td>
            <td>${colheitaTexto}</td>
            <td>
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="excluirSafra(${item.id})">Excluir</button>
            </td>
          </tr>
        `;
      });
    })
    .catch(err => console.log("Erro ao listar safras:", err));
}

async function excluirSafra(id) {
  if (!(await confirmarAcao("Tem certeza que deseja excluir esta safra?"))) return;

  fetch("safra_excluir.php?id=" + id)
    .then(res => res.json())
    .then(() => { listarSafras(); notificar("Safra excluída"); })
    .catch(err => console.log("Erro ao excluir safra:", err));
}

// produção

function salvarProducao(event) {
  event.preventDefault();

  const formData = new FormData();
  formData.append("cultura", document.getElementById("prodCultura").value);
  formData.append("area_colhida", document.getElementById("areaColhida").value);
  formData.append("quantidade_colhida", document.getElementById("qtdColhida").value);
  formData.append("produtividade", document.getElementById("produtividade").value);
  formData.append("data_colheita", document.getElementById("dataColheita").value);
  formData.append("destino", document.getElementById("destino").value);

  fetch("producao_salvar.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
    .then(resposta => {
      if (resposta.status) {
        notificar("Produção salva", resposta.mensagem);
        document.getElementById("formProducao").reset();
        listarProducoes();
        atualizarDashboardGeral();
      } else {
        notificar("Erro ao salvar", resposta.mensagem || "Erro desconhecido", "error");
        console.error(resposta);
      }
    })
    .catch(err => {
      notificar("Falha de comunicação", "Não foi possível acessar o servidor.", "error");
      console.error(err);
    });
}

function listarProducoes() {
  fetch("producao_listar.php")
    .then(res => res.json())
    .then(data => {
      let tbody = document.getElementById("tbodyProducao");
      if (!tbody) return;

      if (!Array.isArray(data) || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-muted small">Nenhum registro de produção ainda.</td></tr>`;
        return;
      }

      tbody.innerHTML = "";
      data.forEach(item => {
        const dataTexto = item.data_colheita ? item.data_colheita.split("-").reverse().join("/") : "-";

        tbody.innerHTML += `
          <tr>
            <td><strong>${item.cultura || "-"}</strong></td>
            <td>${item.area_colhida ?? "-"}</td>
            <td>${item.quantidade_colhida ?? "-"}</td>
            <td>${item.produtividade ?? "-"}</td>
            <td>${dataTexto}</td>
            <td>${item.destino || "-"}</td>
            <td>
              <button type="button" class="btn btn-sm btn-outline-danger" onclick="excluirProducao(${item.id})">Excluir</button>
            </td>
          </tr>
        `;
      });
    })
    .catch(err => console.log("Erro ao listar produção:", err));
}

async function excluirProducao(id) {
  if (!(await confirmarAcao("Tem certeza que deseja excluir este registro de produção?"))) return;

  fetch("producao_excluir.php?id=" + id)
    .then(res => res.json())
    .then(() => { listarProducoes(); atualizarDashboardGeral(); notificar("Produção excluída"); })
    .catch(err => console.log("Erro ao excluir produção:", err));
}

function salvarFinanceiro(event) {
  event.preventDefault();

  const form = document.getElementById("formFinanceiro");
  const formData = new FormData(form);

  fetch("financeiro_salvar.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
    .then(resposta => {
      if (resposta.status) {
        notificar("Lançamento salvo", resposta.mensagem);
        form.reset();
        listarFinanceiro(); // atualiza a tabela na hora, sem reload
        atualizarDashboardFinanceiro(); // atualiza saldo e gráfico do dashboard
      } else {
        notificar("Erro ao salvar", resposta.mensagem || "Erro desconhecido", "error");
        console.error(resposta);
      }
    })
    .catch(err => {
      notificar("Falha de comunicação", "Não foi possível acessar o servidor.", "error");
      console.error(err);
    });
}

// máquinas (trator, colheitadeira, plantadeira, pulverizador - tudo na mesma tabela)

const TIPOS_EQUIPAMENTO = {
  Trator:        { prefixo: "trat" },
  Colheitadeira: { prefixo: "colh" },
  Plantadeira:   { prefixo: "plan" },
  Pulverizador:  { prefixo: "pulv" }
};

function salvarEquipamento(event, tipo) {
  event.preventDefault();

  const cfg = TIPOS_EQUIPAMENTO[tipo];
  if (!cfg) return;
  const p = cfg.prefixo;

  const formData = new FormData();
  formData.append("tipo", tipo);
  formData.append("modelo", document.getElementById(p + "Modelo").value);
  formData.append("marca", document.getElementById(p + "Marca").value);
  formData.append("ano", document.getElementById(p + "Ano").value);
  formData.append("identificacao", document.getElementById(p + "Id").value);
  formData.append("horimetro", document.getElementById(p + "Horimetro").value);
  formData.append("especificacao", document.getElementById(p + "Espec").value);
  formData.append("status", document.getElementById(p + "Status").value);
  formData.append("manutencao_preventiva_data", document.getElementById(p + "Manutencao").value);
  formData.append("observacoes", document.getElementById(p + "Obs").value);

  fetch("equipamentos_salvar.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
    .then(resposta => {
      if (resposta.status) {
        notificar("Máquina salva", resposta.mensagem);
        document.getElementById("form" + tipo).reset();
        listarEquipamentos(tipo); // atualiza a tabela na hora
        atualizarDashboardEquipamentos(); // atualiza o alerta de manutenção no dashboard
      } else {
        notificar("Erro ao salvar", resposta.mensagem || "Erro desconhecido", "error");
        console.error(resposta);
      }
    })
    .catch(err => {
      notificar("Falha de comunicação", "Não foi possível acessar o servidor.", "error");
      console.error(err);
    });
}

function listarEquipamentos(tipo) {
  fetch("equipamentos_listar.php?tipo=" + encodeURIComponent(tipo))
    .then(res => res.json())
    .then(data => {
      let tbody = document.getElementById("tbody" + tipo);
      if (!tbody) return;

      if (!Array.isArray(data) || data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-muted small">Nenhum(a) ${tipo.toLowerCase()} cadastrado(a) ainda.</td></tr>`;
        return;
      }

      const hoje = new Date().toISOString().split("T")[0];

      tbody.innerHTML = "";
      data.forEach(item => {
        const manutencaoVencida = item.manutencao_preventiva_data && item.manutencao_preventiva_data <= hoje;
        const manutencaoTexto = item.manutencao_preventiva_data
          ? item.manutencao_preventiva_data.split("-").reverse().join("/")
          : "Não definida";

        tbody.innerHTML += `
          <tr>
            <td><strong>${item.modelo || "-"}</strong><br><span class="text-muted small">${item.marca || ""}</span></td>
            <td>${item.ano || "-"}</td>
            <td>${item.identificacao || "-"}</td>
            <td>${item.horimetro ?? "-"}</td>
            <td>${item.potencia || "-"}</td>
            <td>
              <select class="form-select form-select-sm" ${window.permissoes?.equipamentosGerenciar ? `onchange="atualizarStatusEquipamento(${item.id}, this.value, '${tipo}')"` : "disabled"}>
                <option value="Ativo" ${item.status === "Ativo" ? "selected" : ""}>Ativo</option>
                <option value="Operando" ${item.status === "Operando" ? "selected" : ""}>Operando</option>
                <option value="Em manutenção" ${item.status === "Em manutenção" ? "selected" : ""}>Em manutenção</option>
                <option value="Inativo" ${item.status === "Inativo" ? "selected" : ""}>Inativo</option>
              </select>
            </td>
            <td>
              <span class="badge ${manutencaoVencida ? "bg-danger" : "bg-light text-dark border"}">${manutencaoTexto}</span>
            </td>
            <td class="small">${item.observacoes || "-"}</td>
            <td>
              ${window.permissoes?.equipamentosGerenciar ? `<button type="button" class="btn btn-sm btn-outline-danger" onclick="excluirEquipamento(${item.id}, '${tipo}')">Excluir</button>` : "-"}
            </td>
          </tr>
        `;
      });
    })
    .catch(err => console.log("Erro ao listar " + tipo + ":", err));
}

function atualizarStatusEquipamento(id, status, tipo) {
  const formData = new FormData();
  formData.append("id", id);
  formData.append("status", status);

  fetch("equipamentos_atualizar_status.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
    .then(resposta => {
      if (!resposta.status) {
        notificar("Erro ao atualizar", resposta.mensagem || "Erro desconhecido", "error");
      }
      listarEquipamentos(tipo);
      atualizarDashboardEquipamentos(); // reflete a mudança no alerta do dashboard na hora
      if (resposta.status) notificar("Status atualizado", resposta.mensagem);
    })
    .catch(err => console.log("Erro ao atualizar status do equipamento:", err));
}

async function excluirEquipamento(id, tipo) {
  if (!(await confirmarAcao("Tem certeza que deseja excluir este equipamento?"))) return;

  fetch("equipamentos_excluir.php?id=" + id)
    .then(res => res.json())
    .then(() => {
      listarEquipamentos(tipo);
      atualizarDashboardEquipamentos();
      notificar("Equipamento excluído");
    })
    .catch(err => console.log("Erro ao excluir equipamento:", err));
}

// contador de manutenção no dashboard

function atualizarDashboardEquipamentos() {
  fetch("dashboard_equipamentos.php")
    .then(res => res.json())
    .then(dados => {
      if (!dados.status) {
        console.log("Erro ao atualizar equipamentos do dashboard:", dados.mensagem);
        return;
      }

      let card = document.getElementById("cardEquipManutencao");
      let qtd = document.getElementById("qtdEquipManutencao");
      if (qtd) qtd.textContent = dados.total;
      if (card) {
        card.classList.toggle("is-terra", dados.total > 0);
        card.classList.toggle("is-gold", dados.total <= 0);
      }
    })
    .catch(err => console.log("Erro ao buscar equipamentos do dashboard:", err));
}

window.onload = function () {
  let botaoSair = document.getElementById("sair");
  if (botaoSair) {
    botaoSair.onclick = async function (e) {
      e.preventDefault();
      if (await confirmarAcao("Deseja encerrar a sessão?")) window.location.href = "logout.php";
    };
  }

  // carrega financeiro
  if (document.getElementById("tbodyFinanceiro")) listarFinanceiro();
  if (document.getElementById("valorSaldoFinanceiro")) atualizarDashboardFinanceiro();

  // carrega as máquinas e o contador de manutenção
  listarEquipamentos("Trator");
  listarEquipamentos("Colheitadeira");
  listarEquipamentos("Plantadeira");
  listarEquipamentos("Pulverizador");
  atualizarDashboardEquipamentos();

  // carrega safra e produção
  if (document.getElementById("tbodySafra")) listarSafras();
  if (document.getElementById("tbodyProducao")) listarProducoes();

  // carrega os novos módulos MVC
  carregarEquipamentosReferencia();
  if (document.getElementById("tbodyColaboradores")) listarColaboradores();
  if (document.getElementById("tbodyOperacoes")) listarOperacoes();
  if (document.getElementById("tbodyManutencoes")) listarManutencoes();
  if (document.getElementById("tbodyUsuarios")) listarUsuarios();
  carregarOperadores();
  atualizarDashboardGeral();

  if (!window.permissoes?.equipamentosGerenciar) {
    ["formTrator", "formColheitadeira", "formPlantadeira", "formPulverizador"].forEach(id => {
      const form = document.getElementById(id);
      if (form) form.style.display = "none";
    });
  }
};


// financeiro

function listarFinanceiro() {
  fetch("financeiro_listar.php")
    .then(res => res.json())
    .then(data => {
      let tbody = document.getElementById("tbodyFinanceiro");
      if (!tbody) return;

      tbody.innerHTML = "";

      data.forEach(item => {
        tbody.innerHTML += `
          <tr>
            <td>${Number(item.id)}</td>
            <td>${escaparHtml(item.tipo)}</td>
            <td>${escaparHtml(item.categoria || "-")}</td>
            <td>${escaparHtml(item.descricao)}</td>
            <td>${moedaBr(item.valor)}</td>
            <td>${dataBr(item.data_lancamento)}</td>
            <td>${escaparHtml(item.talhao_id ?? "-")}</td>
            <td>${escaparHtml(item.forma_pagamento || "-")}</td>
            <td class="text-nowrap">
              <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" onclick="excluirFinanceiro(${Number(item.id)})" title="Excluir lançamento">
                <i class="bi bi-trash3"></i><span>Excluir</span>
              </button>
            </td>
          </tr>
        `;
      });
    })
    .catch(err => console.log(err));
}

async function excluirFinanceiro(id) {
  if (!(await confirmarAcao("Tem certeza que deseja excluir este lançamento?"))) return;

  fetch("financeiro_excluir.php?id=" + id)
    .then(response => response.text())
    .then(() => {
      listarFinanceiro(); // atualiza a tabela
      atualizarDashboardFinanceiro(); // atualiza saldo e gráfico do dashboard
      notificar("Lançamento excluído");
    })
    .catch(error => {
      console.log("Erro ao excluir:", error);
    });
}

function atualizarFinanceiro(id) {
  const formData = new FormData();
  formData.append("id", id);
  formData.append("tipo", document.getElementById("tipo").value);
  formData.append("categoria", document.getElementById("categoria").value);
  formData.append("descricao", document.getElementById("descricao").value);
  formData.append("valor", document.getElementById("valorTotal").value);
  formData.append("data_lancamento", document.getElementById("dataFin").value);
  formData.append("talhao_id", document.getElementById("talhaoFin").value);
  formData.append("forma_pagamento", document.getElementById("forma").value);
  formData.append("observacoes", document.getElementById("obs").value);
  fetch("financeiro_update.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.json())
    .then(() => {
      listarFinanceiro(); // atualiza tabela
      atualizarDashboardFinanceiro(); // atualiza saldo e gráfico do dashboard
      notificar("Atualizado", "Lançamento financeiro atualizado com sucesso.");
    })
    .catch(err => console.log(err));
}

// atualiza saldo e gráfico do dashboard

function atualizarDashboardFinanceiro() {
  fetch("financeiro_dashboard.php")
    .then(res => res.json())
    .then(dados => {
      if (!dados.status) {
        console.log("Erro ao atualizar dashboard:", dados.mensagem);
        return;
      }

      // atualiza o saldo
      let valorSaldo = document.getElementById("valorSaldoFinanceiro");
      if (valorSaldo) valorSaldo.textContent = dados.saldoFormatado;

      // muda a cor do card conforme o saldo
      let cardSaldo = document.getElementById("cardSaldoFinanceiro");
      if (cardSaldo) {
        cardSaldo.classList.remove("is-gold", "is-terra");
        cardSaldo.classList.add(dados.saldo >= 0 ? "is-gold" : "is-terra");
      }

      // e o gráfico, se já tiver sido criado
      if (window.graficoFinanceiroChart) {
        window.graficoFinanceiroChart.data.labels = dados.labels;
        window.graficoFinanceiroChart.data.datasets[0].data = dados.receitas;
        window.graficoFinanceiroChart.data.datasets[1].data = dados.despesas;
        window.graficoFinanceiroChart.update();
      }
    })
    .catch(err => console.log("Erro ao buscar dashboard:", err));
}

// ============================================================
// Módulos MVC: colaboradores, usuários, operações e manutenção
// ============================================================

let cacheColaboradores = [];
let cacheUsuarios = [];
let cacheOperacoes = [];
let cacheManutencoes = [];
let cacheEquipamentos = [];

function preencherFormulario(formId, dados) {
  const form = document.getElementById(formId);
  if (!form) return;
  Object.entries(dados).forEach(([chave, valor]) => {
    const campo = form.elements.namedItem(chave);
    if (campo) campo.value = valor ?? "";
  });
  form.scrollIntoView({ behavior: "smooth", block: "start" });
}

async function carregarEquipamentosReferencia() {
  const selects = document.querySelectorAll(".select-equipamento");
  if (!selects.length) return;
  try {
    const tipos = ["Trator", "Colheitadeira", "Plantadeira", "Pulverizador"];
    const listas = await Promise.all(tipos.map(tipo =>
      fetch("equipamentos_listar.php?tipo=" + encodeURIComponent(tipo)).then(r => r.json())
    ));
    cacheEquipamentos = listas.flatMap(lista => Array.isArray(lista) ? lista : []);
    selects.forEach(select => {
      const atual = select.value;
      const primeira = select.options[0]?.outerHTML || '<option value="">Selecione</option>';
      select.innerHTML = primeira + cacheEquipamentos.map(item =>
        `<option value="${Number(item.id)}">${escaparHtml(item.identificacao || item.tipo)} - ${escaparHtml(item.modelo || item.marca || "Sem modelo")}</option>`
      ).join("");
      select.value = atual;
    });
  } catch (erro) {
    console.error("Erro ao carregar máquinas de referência:", erro);
  }
}

async function carregarOperadores() {
  const selects = document.querySelectorAll(".select-operador");
  if (!selects.length) return;
  try {
    const resposta = await apiJson("colaboradores_api.php?action=opcoes&ativos=1");
    selects.forEach(select => {
      const atual = select.value;
      select.innerHTML = '<option value="">Selecione</option>' + resposta.dados.map(item =>
        `<option value="${Number(item.id)}">${escaparHtml(item.nome)}${item.cargo ? " - " + escaparHtml(item.cargo) : ""}</option>`
      ).join("");
      select.value = atual;
    });
  } catch (erro) {
    console.error(erro);
  }
}

async function listarColaboradores() {
  const tbody = document.getElementById("tbodyColaboradores");
  if (!tbody) return;
  try {
    const resposta = await apiJson("colaboradores_api.php?action=listar");
    cacheColaboradores = resposta.dados;
    tbody.innerHTML = cacheColaboradores.length ? cacheColaboradores.map(item => `
      <tr>
        <td><strong>${escaparHtml(item.nome)}</strong><br><small class="text-muted">${escaparHtml(item.telefone || item.cpf || "")}</small></td>
        <td>${escaparHtml(item.cargo || "-")}</td>
        <td>${escaparHtml(item.forma_pagamento)}${item.valor_pagamento ? `<br><small>${moedaBr(item.valor_pagamento)}</small>` : ""}</td>
        <td>${escaparHtml(item.maquina || "Sem vínculo")}</td>
        <td>${dataBr(item.data_admissao)}</td>
        <td>${badgeStatus(Number(item.ativo) === 1 ? "Ativo" : "Inativo")}</td>
        <td class="text-nowrap">${window.permissoes?.colaboradoresGerenciar ? `
          <button class="btn btn-sm btn-outline-primary" onclick="editarColaborador(${Number(item.id)})">Editar</button>
          <button class="btn btn-sm btn-outline-danger" onclick="excluirColaborador(${Number(item.id)})">Excluir</button>` : "-"}
        </td>
      </tr>`).join("") : '<tr><td colspan="7" class="text-muted">Nenhum colaborador cadastrado.</td></tr>';
  } catch (erro) {
    tbody.innerHTML = `<tr><td colspan="7" class="text-danger">${escaparHtml(erro.message)}</td></tr>`;
  }
}

async function salvarColaborador(event) {
  event.preventDefault();
  const form = event.currentTarget;
  try {
    const resposta = await apiJson("colaboradores_api.php?action=salvar", { method: "POST", body: new FormData(form) });
    await notificar("Colaborador salvo", resposta.mensagem);
    limparFormulario(form.id);
    await listarColaboradores();
    await carregarOperadores();
    atualizarDashboardGeral();
  } catch (erro) { notificar("Não foi possível salvar", erro.message, "error"); }
}

function editarColaborador(id) {
  const item = cacheColaboradores.find(registro => Number(registro.id) === Number(id));
  if (item) preencherFormulario("formColaborador", item);
}

async function excluirColaborador(id) {
  if (!(await confirmarAcao("Excluir este colaborador? Operações vinculadas podem impedir a exclusão."))) return;
  const form = new FormData(); form.append("id", id);
  try {
    const resposta = await apiJson("colaboradores_api.php?action=excluir", { method: "POST", body: form });
    notificar("Colaborador excluído", resposta.mensagem);
    listarColaboradores(); carregarOperadores(); atualizarDashboardGeral();
  } catch (erro) { notificar("Não foi possível excluir", erro.message, "error"); }
}

async function listarUsuarios() {
  const tbody = document.getElementById("tbodyUsuarios");
  if (!tbody) return;
  try {
    const resposta = await apiJson("usuarios_api.php?action=listar");
    cacheUsuarios = resposta.dados;
    tbody.innerHTML = cacheUsuarios.map(item => `
      <tr><td><strong>${escaparHtml(item.nome || "-")}</strong></td><td>${escaparHtml(item.usuario)}</td>
      <td>${escaparHtml(item.email || "-")}</td><td>${escaparHtml(item.nivel)}</td>
      <td>${badgeStatus(Number(item.ativo) === 1 ? "Ativo" : "Inativo")}</td>
      <td class="text-nowrap"><button class="btn btn-sm btn-outline-primary" onclick="editarUsuario(${Number(item.id)})">Editar</button>
      <button class="btn btn-sm btn-outline-danger" onclick="excluirUsuario(${Number(item.id)})">Excluir</button></td></tr>`).join("");
  } catch (erro) { tbody.innerHTML = `<tr><td colspan="6" class="text-danger">${escaparHtml(erro.message)}</td></tr>`; }
}

async function salvarUsuario(event) {
  event.preventDefault();
  const form = event.currentTarget;
  try {
    const resposta = await apiJson("usuarios_api.php?action=salvar", { method: "POST", body: new FormData(form) });
    await notificar("Usuário salvo", resposta.mensagem);
    limparFormulario(form.id); listarUsuarios();
  } catch (erro) { notificar("Não foi possível salvar", erro.message, "error"); }
}

function editarUsuario(id) {
  const item = cacheUsuarios.find(registro => Number(registro.id) === Number(id));
  if (item) preencherFormulario("formUsuario", { ...item, senha: "" });
}

async function excluirUsuario(id) {
  if (!(await confirmarAcao("Excluir este usuário de acesso?"))) return;
  const form = new FormData(); form.append("id", id);
  try {
    const resposta = await apiJson("usuarios_api.php?action=excluir", { method: "POST", body: form });
    notificar("Usuário excluído", resposta.mensagem); listarUsuarios();
  } catch (erro) { notificar("Não foi possível excluir", erro.message, "error"); }
}

async function listarOperacoes() {
  const tbody = document.getElementById("tbodyOperacoes");
  if (!tbody) return;
  try {
    const resposta = await apiJson("operacoes_api.php?action=listar");
    cacheOperacoes = resposta.dados;
    tbody.innerHTML = cacheOperacoes.length ? cacheOperacoes.map(item => `
      <tr><td>${dataBr(item.data_operacao)}</td><td>${escaparHtml(item.maquina)}</td><td>${escaparHtml(item.operador)}</td>
      <td>${horaBr(item.hora_inicio)} - ${horaBr(item.hora_termino)}</td><td>${item.consumo_medio ? escaparHtml(item.consumo_medio) + " L/h" : "-"}</td>
      <td>${badgeStatus(item.status)}</td><td class="text-nowrap">
      <button class="btn btn-sm btn-outline-primary" onclick="editarOperacao(${Number(item.id)})">Editar</button>
      ${window.permissoes?.operacoesGerenciar ? `<button class="btn btn-sm btn-outline-danger" onclick="excluirOperacao(${Number(item.id)})">Excluir</button>` : ""}
      </td></tr>`).join("") : '<tr><td colspan="7" class="text-muted">Nenhuma operação registrada.</td></tr>';
  } catch (erro) { tbody.innerHTML = `<tr><td colspan="7" class="text-danger">${escaparHtml(erro.message)}</td></tr>`; }
}

async function salvarOperacao(event) {
  event.preventDefault(); const form = event.currentTarget;
  try {
    const resposta = await apiJson("operacoes_api.php?action=salvar", { method: "POST", body: new FormData(form) });
    await notificar("Operação salva", resposta.mensagem); limparFormulario(form.id); listarOperacoes(); atualizarDashboardGeral();
  } catch (erro) { notificar("Não foi possível salvar", erro.message, "error"); }
}

function editarOperacao(id) {
  const item = cacheOperacoes.find(registro => Number(registro.id) === Number(id));
  if (item) preencherFormulario("formOperacao", { ...item, hora_inicio: horaBr(item.hora_inicio), hora_termino: item.hora_termino ? horaBr(item.hora_termino) : "" });
}

async function excluirOperacao(id) {
  if (!(await confirmarAcao("Excluir esta operação?"))) return;
  const form = new FormData(); form.append("id", id);
  try { const r = await apiJson("operacoes_api.php?action=excluir", { method: "POST", body: form }); notificar("Operação excluída", r.mensagem); listarOperacoes(); atualizarDashboardGeral(); }
  catch (erro) { notificar("Não foi possível excluir", erro.message, "error"); }
}

async function listarManutencoes() {
  const tbody = document.getElementById("tbodyManutencoes");
  if (!tbody) return;
  try {
    const resposta = await apiJson("manutencoes_api.php?action=listar");
    cacheManutencoes = resposta.dados;
    tbody.innerHTML = cacheManutencoes.length ? cacheManutencoes.map(item => `
      <tr><td>${escaparHtml(item.maquina)}</td><td>${escaparHtml(item.tipo)}</td><td>${escaparHtml(item.descricao)}</td>
      <td>${dataBr(item.data_abertura)} - ${dataBr(item.data_conclusao)}</td><td>${item.custo ? moedaBr(item.custo) : "-"}</td>
      <td>${badgeStatus(item.status)}</td><td class="text-nowrap"><button class="btn btn-sm btn-outline-primary" onclick="editarManutencao(${Number(item.id)})">Editar</button>
      ${window.permissoes?.manutencoesGerenciar ? `<button class="btn btn-sm btn-outline-danger" onclick="excluirManutencao(${Number(item.id)})">Excluir</button>` : ""}</td></tr>`).join("") : '<tr><td colspan="7" class="text-muted">Nenhuma manutenção registrada.</td></tr>';
  } catch (erro) { tbody.innerHTML = `<tr><td colspan="7" class="text-danger">${escaparHtml(erro.message)}</td></tr>`; }
}

async function salvarManutencao(event) {
  event.preventDefault(); const form = event.currentTarget;
  try {
    const resposta = await apiJson("manutencoes_api.php?action=salvar", { method: "POST", body: new FormData(form) });
    await notificar("Manutenção salva", resposta.mensagem); limparFormulario(form.id); listarManutencoes();
    atualizarDashboardGeral(); atualizarDashboardEquipamentos(); carregarEquipamentosReferencia();
  } catch (erro) { notificar("Não foi possível salvar", erro.message, "error"); }
}

function editarManutencao(id) {
  const item = cacheManutencoes.find(registro => Number(registro.id) === Number(id));
  if (item) preencherFormulario("formManutencao", item);
}

async function excluirManutencao(id) {
  if (!(await confirmarAcao("Excluir este histórico de manutenção?"))) return;
  const form = new FormData(); form.append("id", id);
  try { const r = await apiJson("manutencoes_api.php?action=excluir", { method: "POST", body: form }); notificar("Manutenção excluída", r.mensagem); listarManutencoes(); atualizarDashboardGeral(); }
  catch (erro) { notificar("Não foi possível excluir", erro.message, "error"); }
}

async function salvarFazenda(event) {
  event.preventDefault();
  try {
    const resposta = await apiJson("fazenda_api.php?action=salvar", { method: "POST", body: new FormData(event.currentTarget) });
    await notificar("Painel atualizado", resposta.mensagem); atualizarDashboardGeral();
  } catch (erro) { notificar("Não foi possível atualizar", erro.message, "error"); }
}

async function atualizarDashboardGeral() {
  try {
    const resposta = await apiJson("dashboard_geral.php");
    const d = resposta.dados;
    const atualizar = (id, valor) => { const el = document.getElementById(id); if (el) el.textContent = valor; };
    atualizar("qtdProducaoColhida", `${Number(d.quantidadeColhida).toLocaleString("pt-BR", { maximumFractionDigits: 2 })} sc`);
    atualizar("qtdAreaColhida", `${Number(d.areaColhida).toLocaleString("pt-BR", { maximumFractionDigits: 2 })} ha`);
    atualizar("qtdOperacoesAtivas", d.operacoesAtivas);
    atualizar("qtdColaboradoresAtivos", d.colaboradoresAtivos);
    atualizar("qtdAreaFazenda", `${Number(d.fazenda.area_total_ha).toLocaleString("pt-BR", { maximumFractionDigits: 2 })} ha`);
    atualizar("nomeFazendaPainel", d.fazenda.nome);
    atualizar("localFazendaPainel", [d.fazenda.municipio, d.fazenda.estado].filter(Boolean).join(" - "));
    atualizar("areaProdutivaPainel", `${Number(d.fazenda.area_produtiva_ha).toLocaleString("pt-BR", { maximumFractionDigits: 2 })} ha`);
    atualizar("culturaPainel", d.fazenda.cultura_principal || "Não informada");
    atualizar("produtividadePainel", `${Number(d.produtividadeMedia).toLocaleString("pt-BR", { maximumFractionDigits: 2 })} sc/ha`);
    if (window.graficoProducaoChart) {
      window.graficoProducaoChart.data.labels = d.culturas;
      window.graficoProducaoChart.data.datasets[0].data = d.producaoPorCultura;
      window.graficoProducaoChart.update();
    }
  } catch (erro) { console.error("Erro ao atualizar painel geral:", erro); }
}
