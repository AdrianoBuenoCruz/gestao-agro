<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= htmlspecialchars(\App\Core\Auth::csrfToken()) ?>">
  <title>Gestor Agro - <?= htmlspecialchars($page_title) ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,100..900;1,9..144,100..900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <link rel="stylesheet" href="modulos.css">
</head>

<body>
  <div class="overlay"></div>

  <div class="app">
    <aside class="menu sidebar">
      <div>
        <div class="logo brand">
          <h2 class="brand-font">Gestor Agro</h2>
          <div class="brand-rule"></div>
          <small>Sistema de Gestão Agrícola</small>
        </div>

        <nav class="nav sidebar-nav">
          <button class="item nav-link active" onclick="abrirModulo('inicio', this)">
            <i class="bi bi-house-door"></i> Início
          </button>
          <?php if (\App\Core\Auth::can('safras.gerenciar')): ?>
          <button class="item nav-link" onclick="abrirModulo('safra', this)">
            <i class="bi bi-calendar4-range"></i> Safra
          </button>
          <?php endif; ?>
          <?php if (\App\Core\Auth::can('producao.gerenciar')): ?>
          <button class="item nav-link" onclick="abrirModulo('producao', this)">
            <i class="bi bi-boxes"></i> Produção
          </button>
          <?php endif; ?>
          <?php if (\App\Core\Auth::can('financeiro.gerenciar')): ?>
          <button class="item nav-link" onclick="abrirModulo('financeiro', this)">
            <i class="bi bi-cash-stack"></i> Financeiro
          </button>
          <?php endif; ?>

          <?php if (\App\Core\Auth::can('colaboradores.ver')): ?>
          <button class="item nav-link" onclick="abrirModulo('colaboradores', this)">
            <i class="bi bi-people"></i> Colaboradores
          </button>
          <?php endif; ?>
          <?php if (\App\Core\Auth::can('operacoes.ver')): ?>
          <button class="item nav-link" onclick="abrirModulo('operacoes', this)">
            <i class="bi bi-clipboard2-data"></i> Operações
          </button>
          <?php endif; ?>
          <?php if (\App\Core\Auth::can('manutencoes.ver')): ?>
          <button class="item nav-link" onclick="abrirModulo('manutencoes', this)">
            <i class="bi bi-wrench-adjustable"></i> Manutenções
          </button>
          <?php endif; ?>

          <?php if (\App\Core\Auth::can('equipamentos.ver')): ?>
          <div class="grupo">
            <button class="item nav-link" id="btnMaquinas" onclick="abrirSubmenu(this)">
              <i class="bi bi-nut"></i> Máquinas
            </button>
            <div class="subnav" id="subnavMaquinas">
              <button class="subitem nav-link" onclick="abrirModulo('colheitadeira', this)">Colheitadeira</button>
              <button class="subitem nav-link" onclick="abrirModulo('trator', this)">Trator</button>
              <button class="subitem nav-link" onclick="abrirModulo('plantadeira', this)">Plantadeira</button>
              <button class="subitem nav-link" onclick="abrirModulo('pulverizador', this)">Pulverizador</button>
            </div>
          </div>
          <?php endif; ?>

          <?php if (\App\Core\Auth::can('usuarios.gerenciar')): ?>
          <button class="item nav-link" onclick="abrirModulo('usuarios', this)">
            <i class="bi bi-person-lock"></i> Usuários
          </button>
          <button class="item nav-link" onclick="abrirModulo('fazenda', this)">
            <i class="bi bi-geo-alt"></i> Dados da Fazenda
          </button>
          <?php endif; ?>
        </nav>
      </div>
      <div class="sidebar-foot">
        <div class="mb-3">
          <strong><?= htmlspecialchars(\App\Core\Auth::user()['nome']) ?></strong>
          <small class="d-block mt-1"><?= htmlspecialchars(\App\Core\Auth::role()) ?></small>
        </div>
        <button id="sair" class="btn text-white w-100 border-0" style="background-color: rgba(255,255,255,0.1)">Sair</button>
      </div>
    </aside>

    <main class="main main-content">
      
      <section class="modulo ativo p-4" id="inicio">
        <div class="topo mb-4">
          <span class="text-eyebrow">Painel de Controle</span>
          <h1 class="mt-1">Sistema Web de Gestão Agrícola - Gestor Agro</h1>
          <p class="text-muted small">Informações atualizadas dinamicamente via banco de dados</p>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger small mb-4"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <?php if (\App\Core\Auth::can('financeiro.gerenciar')): ?>
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card <?= $saldo >= 0 ? 'is-gold' : 'is-terra' ?>" id="cardSaldoFinanceiro">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Saldo financeiro</div>
                            <div class="stat-value mt-1" style="font-size:1.45rem;" id="valorSaldoFinanceiro"><?= moeda($saldo) ?></div>
                        </div>
                        <i class="bi bi-cash-coin stat-icon"></i>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card <?= $equipManutencao > 0 ? 'is-terra' : 'is-gold' ?>" id="cardEquipManutencao">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Equipamentos em manutenção</div>
                            <div class="stat-value mt-1" id="qtdEquipManutencao"><?= $equipManutencao ?></div>
                        </div>
                        <i class="bi bi-tools stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card is-mata">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Quantidade colhida</div>
                            <div class="stat-value mt-1" id="qtdProducaoColhida"><?= number_format((float) $quantidadeColhida, 2, ',', '.') ?> sc</div>
                        </div>
                        <i class="bi bi-box-seam stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card is-gold">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Área já colhida</div>
                            <div class="stat-value mt-1" id="qtdAreaColhida"><?= number_format((float) $areaColhida, 2, ',', '.') ?> ha</div>
                        </div>
                        <i class="bi bi-bounding-box-circles stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card is-mata">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Operações ativas</div>
                            <div class="stat-value mt-1" id="qtdOperacoesAtivas"><?= (int) $operacoesAtivas ?></div>
                        </div>
                        <i class="bi bi-activity stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card is-gold">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Área total da fazenda</div>
                            <div class="stat-value mt-1" id="qtdAreaFazenda"><?= number_format((float) $fazenda['area_total_ha'], 2, ',', '.') ?> ha</div>
                        </div>
                        <i class="bi bi-map stat-icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card stat-card is-mata">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="stat-label">Colaboradores ativos</div>
                            <div class="stat-value mt-1" id="qtdColaboradoresAtivos"><?= (int) $colaboradoresAtivos ?></div>
                        </div>
                        <i class="bi bi-people stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <?php if (\App\Core\Auth::can('financeiro.gerenciar')): ?>
            <div class="col-xl-7">
                <div class="card p-3">
                    <h2 class="h6 mb-3 font-weight-bold">Receitas x Despesas (últimos 6 meses)</h2>
                    <div style="position: relative; height:240px;">
                        <canvas id="graficoFinanceiro"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-xl-5">
                <div class="card p-3 h-100">
                    <h2 class="h6 mb-3 font-weight-bold">Produção colhida por cultura</h2>
                    <div style="position: relative; height:240px;">
                        <canvas id="graficoProducao"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-3 mb-4 farm-panel">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
              <span class="text-eyebrow">Visão da propriedade</span>
              <h2 class="h4 mb-1" id="nomeFazendaPainel"><?= htmlspecialchars($fazenda['nome']) ?></h2>
              <p class="text-muted mb-0"><i class="bi bi-geo-alt"></i>
                <span id="localFazendaPainel"><?= htmlspecialchars(trim(($fazenda['municipio'] ?? '') . ' - ' . ($fazenda['estado'] ?? ''), ' -')) ?></span>
              </p>
            </div>
            <div class="farm-metrics">
              <div><strong id="areaProdutivaPainel"><?= number_format((float) $fazenda['area_produtiva_ha'], 2, ',', '.') ?> ha</strong><span>Área produtiva</span></div>
              <div><strong id="culturaPainel"><?= htmlspecialchars($fazenda['cultura_principal'] ?: 'Não informada') ?></strong><span>Cultura principal</span></div>
              <div><strong id="produtividadePainel"><?= number_format((float) $produtividadeMedia, 2, ',', '.') ?> sc/ha</strong><span>Produtividade média</span></div>
            </div>
          </div>
        </div>

        <?php if (\App\Core\Auth::can('financeiro.gerenciar')): ?>
        <div class="row g-3">
            <div class="col-12">
                <div class="card p-3">
                    <h2 class="h6 mb-3">Lançamentos Financeiros</h2>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo</th>
                                    <th>Categoria</th>
                                    <th>Descrição</th>
                                    <th>Valor</th>
                                    <th>Data</th>
                                    <th>Talhão</th>
                                    <th>Forma Pagamento</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyFinanceiro">
                                <!-- preenchido via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
      </section>

      <?php if (\App\Core\Auth::can('safras.gerenciar')): ?>
      <section class="modulo box p-4" id="safra" style="display:none;">
        <h2>Safra</h2>
        <form class="form" id="formSafra" onsubmit="salvarSafra(event)">
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="nomeSafra">Nome da safra</label>
              <input type="text" class="form-control" id="nomeSafra" placeholder="Ex: Safra 2026">
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="cultura">Cultura</label>
              <input type="text" class="form-control" id="cultura" placeholder="Ex: Milho">
            </div>
          </div>
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="area">Área plantada</label>
              <input type="number" class="form-control" id="area" placeholder="Em hectares">
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="talhao">Talhão</label>
              <input type="text" class="form-control" id="talhao" placeholder="Ex: Talhão A">
            </div>
          </div>
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="plantio">Data de plantio</label>
              <input type="date" class="form-control" id="plantio">
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="colheitaPrev">Previsão de colheita</label>
              <input type="date" class="form-control" id="colheitaPrev">
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </form>

        <div class="lista-safras mt-4">
          <h3 class="h6">Safras cadastradas</h3>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Safra</th>
                  <th>Cultura</th>
                  <th>Área</th>
                  <th>Talhão</th>
                  <th>Plantio</th>
                  <th>Previsão de colheita</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody id="tbodySafra">
                <!-- preenchido via JS -->
              </tbody>
            </table>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <?php if (\App\Core\Auth::can('producao.gerenciar')): ?>
      <section class="modulo box p-4" id="producao" style="display:none;">
        <h2>Produção</h2>
        <form class="form" id="formProducao" onsubmit="salvarProducao(event)">
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="prodCultura">Cultura</label>
              <input type="text" class="form-control" id="prodCultura" placeholder="Ex: Soja">
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="areaColhida">Área colhida</label>
              <input type="number" class="form-control" id="areaColhida" placeholder="Em hectares">
            </div>
          </div>
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="qtdColhida">Quantidade colhida</label>
              <input type="number" class="form-control" id="qtdColhida" placeholder="Em sacas">
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="produtividade">Produtividade</label>
              <input type="number" class="form-control" id="produtividade" placeholder="Sacas por hectare">
            </div>
          </div>
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="dataColheita">Data da colheita</label>
              <input type="date" class="form-control" id="dataColheita">
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="destino">Destino</label>
              <input type="text" class="form-control" id="destino" placeholder="Ex: Venda">
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </form>

        <div class="lista-producao mt-4">
          <h3 class="h6">Produção registrada</h3>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Cultura</th>
                  <th>Área colhida</th>
                  <th>Quantidade</th>
                  <th>Produtividade</th>
                  <th>Data da colheita</th>
                  <th>Destino</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody id="tbodyProducao">
                <!-- preenchido via JS -->
              </tbody>
            </table>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <?php if (\App\Core\Auth::can('financeiro.gerenciar')): ?>
      <section class="modulo box p-4" id="financeiro" style="display:none;">
        <h2>Financeiro</h2>
        <form class="form" id="formFinanceiro" onsubmit="salvarFinanceiro(event)">
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="tipo">Tipo</label>
              <select class="form-select" id="tipo" name="tipo" required>
                <option value="Receita">Receita</option>
                <option value="Despesa">Despesa</option>
              </select>
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="descricao">Descrição</label>
              <input type="text" class="form-control" id="descricao" name="descricao" placeholder="Ex: Venda de milho" required>
            </div>
          </div>
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="quantidadeFin">Quantidade</label>
              <input type="number" class="form-control" id="quantidadeFin" name="quantidadeFin" placeholder="Em sacas">
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="preco">Preço por saca</label>
              <input type="number" class="form-control" id="preco" name="preco" step="0.01" placeholder="R$">
            </div>
          </div>
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="valorTotal">Valor total</label>
              <input type="number" class="form-control" id="valorTotal" name="valorTotal" step="0.01" placeholder="R$" required>
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="dataFin">Data</label>
              <input type="date" class="form-control" id="dataFin" name="dataFin" required>
            </div>
          </div>
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="categoria">Categoria</label>
              <input type="text" class="form-control" id="categoria" name="categoria" maxlength="100" placeholder="Ex: Venda de produção, combustível ou manutenção">
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="talhaoFin">Talhão</label>
              <input type="number" class="form-control" id="talhaoFin" name="talhao_id" min="1" placeholder="Ex: 1">
            </div>
          </div>
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="forma">Forma de pagamento</label>
              <select class="form-select" id="forma" name="forma_pagamento">
                <option value="">Selecione</option>
                <option value="PIX">PIX</option>
                <option value="Dinheiro">Dinheiro</option>
                <option value="Cartão de débito">Cartão de débito</option>
                <option value="Cartão de crédito">Cartão de crédito</option>
                <option value="Boleto">Boleto</option>
                <option value="Transferência bancária">Transferência bancária</option>
                <option value="Outro">Outro</option>
              </select>
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="obs">Observações</label>
              <textarea class="form-control" id="obs" name="observacoes" rows="2" maxlength="255" placeholder="Informações adicionais do lançamento"></textarea>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Salvar Lançamento</button>
        </form>
      </section>
      <?php endif; ?>

      <?php if (\App\Core\Auth::can('equipamentos.ver')): ?>
      <section class="modulo box p-4" id="colheitadeira" style="display:none;">
        <h2>Colheitadeira</h2>
        <form class="form" id="formColheitadeira" onsubmit="salvarEquipamento(event, 'Colheitadeira')">
          <div class="linha row g-3 mb-3"><div class="campo col-md-6"><label class="form-label" for="colhModelo">Modelo</label><input type="text" class="form-control" id="colhModelo" placeholder="Ex: John Deere S440"></div><div class="campo col-md-6"><label class="form-label" for="colhMarca">Marca</label><input type="text" class="form-control" id="colhMarca" placeholder="Ex: John Deere"></div></div>
          <div class="linha row g-3 mb-3"><div class="campo col-md-6"><label class="form-label" for="colhAno">Ano</label><input type="number" class="form-control" id="colhAno" placeholder="Ex: 2022"></div><div class="campo col-md-6"><label class="form-label" for="colhId">Identificação</label><input type="text" class="form-control" id="colhId" placeholder="Ex: COLH-01"></div></div>
          <div class="linha row g-3 mb-3"><div class="campo col-md-6"><label class="form-label" for="colhHorimetro">Horímetro</label><input type="number" class="form-control" id="colhHorimetro" placeholder="Ex: 3250"></div><div class="campo col-md-6"><label class="form-label" for="colhEspec">Combustível</label><input type="text" class="form-control" id="colhEspec" placeholder="Ex: Diesel"></div></div>
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="colhStatus">Status</label>
              <select class="form-select" id="colhStatus">
                <option value="Ativo">Ativo</option>
                <option value="Operando">Operando</option>
                <option value="Em manutenção">Em manutenção</option>
                <option value="Inativo">Inativo</option>
              </select>
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="colhManutencao">Próxima manutenção preventiva</label>
              <input type="date" class="form-control" id="colhManutencao">
            </div>
          </div>
          <div class="campo mb-3"><label class="form-label" for="colhObs">Observação</label><input type="text" class="form-control" id="colhObs" placeholder="Ex: Revisão da plataforma de corte agendada"></div>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </form>

        <div class="lista-equipamentos mt-4">
          <h3 class="h6">Colheitadeiras cadastradas</h3>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Modelo / Marca</th>
                  <th>Ano</th>
                  <th>Identificação</th>
                  <th>Horímetro</th>
                  <th>Combustível</th>
                  <th>Status</th>
                  <th>Manutenção preventiva</th>
                  <th>Observação</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody id="tbodyColheitadeira">
                <!-- preenchido via JS -->
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section class="modulo box p-4" id="trator" style="display:none;">
        <h2>Trator</h2>
        <form class="form" id="formTrator" onsubmit="salvarEquipamento(event, 'Trator')">
          <div class="linha row g-3 mb-3"><div class="campo col-md-6"><label class="form-label" for="tratModelo">Modelo</label><input type="text" class="form-control" id="tratModelo" placeholder="Ex: 4292"></div><div class="campo col-md-6"><label class="form-label" for="tratMarca">Marca</label><input type="text" class="form-control" id="tratMarca" placeholder="Ex: Massey Ferguson"></div></div>
          <div class="linha row g-3 mb-3"><div class="campo col-md-6"><label class="form-label" for="tratAno">Ano</label><input type="number" class="form-control" id="tratAno" placeholder="Ex: 2021"></div><div class="campo col-md-6"><label class="form-label" for="tratId">Matricula da Maquina</label><input type="text" class="form-control" id="tratId" placeholder="Ex: FN-01"></div></div>
          <div class="linha row g-3 mb-3"><div class="campo col-md-6"><label class="form-label" for="tratHorimetro">Horímetro</label><input type="number" class="form-control" id="tratHorimetro" placeholder="Ex: 4100"></div><div class="campo col-md-6"><label class="form-label" for="tratEspec">Potência</label><input type="text" class="form-control" id="tratEspec" placeholder="Ex: 105 cv"></div></div>
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="tratStatus">Status</label>
              <select class="form-select" id="tratStatus">
                <option value="Ativo">Ativo</option>
                <option value="Operando">Operando</option>
                <option value="Em manutenção">Em manutenção</option>
                <option value="Inativo">Inativo</option>
              </select>
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="tratManutencao">Próxima manutenção preventiva</label>
              <input type="date" class="form-control" id="tratManutencao">
            </div>
          </div>
          <div class="campo mb-3"><label class="form-label" for="tratObs">Observação</label><input type="text" class="form-control" id="tratObs" placeholder="Ex: Troca de óleo e filtros agendada"></div>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </form>

        <div class="lista-equipamentos mt-4">
          <h3 class="h6">Tratores cadastrados</h3>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Modelo / Marca</th>
                  <th>Ano</th>
                  <th>Identificação</th>
                  <th>Horímetro</th>
                  <th>Potência</th>
                  <th>Status</th>
                  <th>Manutenção preventiva</th>
                  <th>Observação</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody id="tbodyTrator">
                <!-- preenchido via JS -->
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section class="modulo box p-4" id="plantadeira" style="display:none;">
        <h2>Plantadeira</h2>
        <form class="form" id="formPlantadeira" onsubmit="salvarEquipamento(event, 'Plantadeira')">
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6"><label class="form-label" for="planModelo">Modelo</label><input type="text" class="form-control" id="planModelo" placeholder="Ex: Plantadeira 11 linhas"></div>
            <div class="campo col-md-6">
              <label class="form-label" for="planMarca">Marca</label>
              <select class="form-select" id="planMarca">
                <option>Jumil</option><option>John Deere</option><option>Case IH</option><option>Stara</option><option>GTS</option><option>New Holland</option><option>Massey Ferguson</option>
              </select>
            </div>
          </div>
          <div class="linha row g-3 mb-3"><div class="campo col-md-6"><label class="form-label" for="planAno">Ano</label><input type="number" class="form-control" id="planAno" placeholder="Ex: 2020"></div><div class="campo col-md-6"><label class="form-label" for="planId">Identificação</label><input type="text" class="form-control" id="planId" placeholder="Ex: PLANT-01"></div></div>
          <div class="linha row g-3 mb-3"><div class="campo col-md-6"><label class="form-label" for="planHorimetro">Horímetro</label><input type="number" class="form-control" id="planHorimetro" placeholder="Se aplicável"></div><div class="campo col-md-6"><label class="form-label" for="planEspec">Quantidade de linhas</label><input type="number" class="form-control" id="planEspec" placeholder="Ex: 11"></div></div>
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="planStatus">Status</label>
              <select class="form-select" id="planStatus">
                <option value="Ativo">Ativo</option>
                <option value="Operando">Operando</option>
                <option value="Em manutenção">Em manutenção</option>
                <option value="Inativo">Inativo</option>
              </select>
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="planManutencao">Próxima manutenção preventiva</label>
              <input type="date" class="form-control" id="planManutencao">
            </div>
          </div>
          <div class="campo mb-3"><label class="form-label" for="planObs">Observação</label><input type="text" class="form-control" id="planObs" placeholder="Ex: Regulagem dos discos agendada"></div>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </form>

        <div class="lista-equipamentos mt-4">
          <h3 class="h6">Plantadeiras cadastradas</h3>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Modelo / Marca</th>
                  <th>Ano</th>
                  <th>Identificação</th>
                  <th>Horímetro</th>
                  <th>Linhas</th>
                  <th>Status</th>
                  <th>Manutenção preventiva</th>
                  <th>Observação</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody id="tbodyPlantadeira">
                <!-- preenchido via JS -->
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section class="modulo box p-4" id="pulverizador" style="display:none;">
        <h2>Pulverizador</h2>
        <form class="form" id="formPulverizador" onsubmit="salvarEquipamento(event, 'Pulverizador')">
          <div class="linha row g-3 mb-3"><div class="campo col-md-6"><label class="form-label" for="pulvModelo">Modelo</label><input type="text" class="form-control" id="pulvModelo" placeholder="Ex: Uniport 2500"></div><div class="campo col-md-6"><label class="form-label" for="pulvMarca">Marca</label><input type="text" class="form-control" id="pulvMarca" placeholder="Ex: Jacto"></div></div>
          <div class="linha row g-3 mb-3"><div class="campo col-md-6"><label class="form-label" for="pulvAno">Ano</label><input type="number" class="form-control" id="pulvAno" placeholder="Ex: 2023"></div><div class="campo col-md-6"><label class="form-label" for="pulvId">Identificação</label><input type="text" class="form-control" id="pulvId" placeholder="Ex: PULV-01"></div></div>
          <div class="linha row g-3 mb-3"><div class="campo col-md-6"><label class="form-label" for="pulvHorimetro">Horímetro</label><input type="number" class="form-control" id="pulvHorimetro" placeholder="Se aplicável"></div><div class="campo col-md-6"><label class="form-label" for="pulvEspec">Capacidade do tanque</label><input type="text" class="form-control" id="pulvEspec" placeholder="Ex: 2500 litros"></div></div>
          <div class="linha row g-3 mb-3">
            <div class="campo col-md-6">
              <label class="form-label" for="pulvStatus">Status</label>
              <select class="form-select" id="pulvStatus">
                <option value="Ativo">Ativo</option>
                <option value="Operando">Operando</option>
                <option value="Em manutenção">Em manutenção</option>
                <option value="Inativo">Inativo</option>
              </select>
            </div>
            <div class="campo col-md-6">
              <label class="form-label" for="pulvManutencao">Próxima manutenção preventiva</label>
              <input type="date" class="form-control" id="pulvManutencao">
            </div>
          </div>
          <div class="campo mb-3"><label class="form-label" for="pulvObs">Observação</label><input type="text" class="form-control" id="pulvObs" placeholder="Ex: Limpeza dos bicos agendada"></div>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </form>

        <div class="lista-equipamentos mt-4">
          <h3 class="h6">Pulverizadores cadastrados</h3>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Modelo / Marca</th>
                  <th>Ano</th>
                  <th>Identificação</th>
                  <th>Horímetro</th>
                  <th>Tanque</th>
                  <th>Status</th>
                  <th>Manutenção preventiva</th>
                  <th>Observação</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody id="tbodyPulverizador">
                <!-- preenchido via JS -->
              </tbody>
            </table>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <?php if (\App\Core\Auth::can('colaboradores.ver')): ?>
      <section class="modulo box p-4" id="colaboradores" style="display:none;">
        <div class="section-heading">
          <div><span class="text-eyebrow">Equipe</span><h2>Colaboradores</h2></div>
          <p class="text-muted">Cadastro, vínculo com máquina e forma de pagamento.</p>
        </div>
        <?php if (\App\Core\Auth::can('colaboradores.gerenciar')): ?>
        <form class="form card p-3 mb-4" id="formColaborador" onsubmit="salvarColaborador(event)">
          <input type="hidden" name="id" id="colaboradorId">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nome completo</label><input class="form-control" name="nome" required maxlength="120"></div>
            <div class="col-md-3"><label class="form-label">CPF</label><input class="form-control" name="cpf" maxlength="14" placeholder="000.000.000-00"></div>
            <div class="col-md-3"><label class="form-label">Telefone</label><input class="form-control" name="telefone" maxlength="20"></div>
            <div class="col-md-4"><label class="form-label">Cargo</label><input class="form-control" name="cargo" placeholder="Ex: Operador de máquinas"></div>
            <div class="col-md-4"><label class="form-label">Forma de pagamento</label><select class="form-select" name="forma_pagamento" required><option value="">Selecione</option><option>Diária</option><option>CLT</option><option>Quinzenal</option><option>Hora</option></select></div>
            <div class="col-md-4"><label class="form-label">Valor de referência</label><input type="number" step="0.01" min="0" class="form-control" name="valor_pagamento" placeholder="R$"></div>
            <div class="col-md-5"><label class="form-label">Máquina principal</label><select class="form-select select-equipamento" name="equipamento_id"><option value="">Sem vínculo fixo</option></select></div>
            <div class="col-md-3"><label class="form-label">Data de admissão</label><input type="date" class="form-control" name="data_admissao"></div>
            <div class="col-md-4"><label class="form-label">Situação</label><select class="form-select" name="ativo"><option value="1">Ativo</option><option value="0">Inativo</option></select></div>
            <div class="col-12"><label class="form-label">Observações</label><textarea class="form-control" name="observacoes" rows="2" maxlength="500"></textarea></div>
          </div>
          <div class="mt-3"><button class="btn btn-primary" type="submit">Salvar colaborador</button><button class="btn btn-light ms-2" type="button" onclick="limparFormulario('formColaborador')">Limpar</button></div>
        </form>
        <?php endif; ?>
        <div class="card p-3"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Nome</th><th>Cargo</th><th>Pagamento</th><th>Máquina</th><th>Admissão</th><th>Status</th><th>Ações</th></tr></thead><tbody id="tbodyColaboradores"></tbody></table></div></div>
      </section>
      <?php endif; ?>

      <?php if (\App\Core\Auth::can('operacoes.ver')): ?>
      <section class="modulo box p-4" id="operacoes" style="display:none;">
        <div class="section-heading"><div><span class="text-eyebrow">Campo</span><h2>Operações agrícolas</h2></div><p class="text-muted">Controle da máquina, operador, duração e consumo.</p></div>
        <?php if (\App\Core\Auth::can('operacoes.registrar')): ?>
        <form class="form card p-3 mb-4" id="formOperacao" onsubmit="salvarOperacao(event)">
          <input type="hidden" name="id">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Máquina</label><select class="form-select select-equipamento" name="equipamento_id" required><option value="">Selecione</option></select></div>
            <div class="col-md-4"><label class="form-label">Operador</label><select class="form-select select-operador" name="operador_id" required><option value="">Selecione</option></select></div>
            <div class="col-md-4"><label class="form-label">Data</label><input type="date" class="form-control" name="data_operacao" required></div>
            <div class="col-md-3"><label class="form-label">Consumo médio (L/h)</label><input type="number" step="0.01" min="0" class="form-control" name="consumo_medio"></div>
            <div class="col-md-3"><label class="form-label">Hora de início</label><input type="time" class="form-control" name="hora_inicio" required></div>
            <div class="col-md-3"><label class="form-label">Hora de término</label><input type="time" class="form-control" name="hora_termino"></div>
            <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status" required><option>Planejada</option><option>Em andamento</option><option>Concluída</option><option>Cancelada</option></select></div>
            <div class="col-12"><label class="form-label">Descrição da atividade</label><textarea class="form-control" name="descricao" rows="2" maxlength="500"></textarea></div>
          </div>
          <div class="mt-3"><button class="btn btn-primary" type="submit">Salvar operação</button><button class="btn btn-light ms-2" type="button" onclick="limparFormulario('formOperacao')">Limpar</button></div>
        </form>
        <?php endif; ?>
        <div class="card p-3"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Data</th><th>Máquina</th><th>Operador</th><th>Horário</th><th>Consumo</th><th>Status</th><th>Ações</th></tr></thead><tbody id="tbodyOperacoes"></tbody></table></div></div>
      </section>
      <?php endif; ?>

      <?php if (\App\Core\Auth::can('manutencoes.ver')): ?>
      <section class="modulo box p-4" id="manutencoes" style="display:none;">
        <div class="section-heading"><div><span class="text-eyebrow">Maquinário</span><h2>Manutenções</h2></div><p class="text-muted">Histórico preventivo, corretivo e preditivo.</p></div>
        <?php if (\App\Core\Auth::can('manutencoes.registrar')): ?>
        <form class="form card p-3 mb-4" id="formManutencao" onsubmit="salvarManutencao(event)">
          <input type="hidden" name="id">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Máquina</label><select class="form-select select-equipamento" name="equipamento_id" required><option value="">Selecione</option></select></div>
            <div class="col-md-2"><label class="form-label">Tipo</label><select class="form-select" name="tipo" required><option>Preventiva</option><option>Corretiva</option><option>Preditiva</option></select></div>
            <div class="col-md-3"><label class="form-label">Abertura</label><input type="date" class="form-control" name="data_abertura" required></div>
            <div class="col-md-3"><label class="form-label">Conclusão</label><input type="date" class="form-control" name="data_conclusao"></div>
            <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status" required><option>Aberta</option><option>Em andamento</option><option>Concluída</option><option>Cancelada</option></select></div>
            <div class="col-md-3"><label class="form-label">Custo</label><input type="number" step="0.01" min="0" class="form-control" name="custo" placeholder="R$"></div>
            <div class="col-md-6"><label class="form-label">Responsável</label><input class="form-control" name="responsavel" maxlength="120"></div>
            <div class="col-12"><label class="form-label">Descrição</label><textarea class="form-control" name="descricao" rows="2" maxlength="500" required></textarea></div>
          </div>
          <div class="mt-3"><button class="btn btn-primary" type="submit">Salvar manutenção</button><button class="btn btn-light ms-2" type="button" onclick="limparFormulario('formManutencao')">Limpar</button></div>
        </form>
        <?php endif; ?>
        <div class="card p-3"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Máquina</th><th>Tipo</th><th>Descrição</th><th>Período</th><th>Custo</th><th>Status</th><th>Ações</th></tr></thead><tbody id="tbodyManutencoes"></tbody></table></div></div>
      </section>
      <?php endif; ?>

      <?php if (\App\Core\Auth::can('usuarios.gerenciar')): ?>
      <section class="modulo box p-4" id="usuarios" style="display:none;">
        <div class="section-heading"><div><span class="text-eyebrow">Segurança</span><h2>Usuários e permissões</h2></div><p class="text-muted">Administrador, Auxiliar Administrativo e Operador.</p></div>
        <div class="permission-summary row g-3 mb-4">
          <div class="col-lg-4"><div class="card p-3 h-100"><strong>Administrador</strong><small>Acesso total, usuários e dados da fazenda.</small></div></div>
          <div class="col-lg-4"><div class="card p-3 h-100"><strong>Auxiliar Administrativo</strong><small>Gestão administrativa, financeira, equipe e operações.</small></div></div>
          <div class="col-lg-4"><div class="card p-3 h-100"><strong>Operador</strong><small>Painel, máquinas, operações e manutenções.</small></div></div>
        </div>
        <form class="form card p-3 mb-4" id="formUsuario" onsubmit="salvarUsuario(event)">
          <input type="hidden" name="id">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Nome</label><input class="form-control" name="nome" required maxlength="120"></div>
            <div class="col-md-4"><label class="form-label">Usuário</label><input class="form-control" name="usuario" required maxlength="80"></div>
            <div class="col-md-4"><label class="form-label">E-mail</label><input type="email" class="form-control" name="email" maxlength="150"></div>
            <div class="col-md-4"><label class="form-label">Perfil</label><select class="form-select" name="nivel" required><option>Administrador</option><option>Auxiliar Administrativo</option><option>Operador</option></select></div>
            <div class="col-md-4"><label class="form-label">Senha <small class="text-muted">(vazia mantém a atual)</small></label><input type="password" class="form-control" name="senha" minlength="8"></div>
            <div class="col-md-4"><label class="form-label">Situação</label><select class="form-select" name="ativo"><option value="1">Ativo</option><option value="0">Inativo</option></select></div>
          </div>
          <div class="mt-3"><button class="btn btn-primary" type="submit">Salvar usuário</button><button class="btn btn-light ms-2" type="button" onclick="limparFormulario('formUsuario')">Limpar</button></div>
        </form>
        <div class="card p-3"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Nome</th><th>Usuário</th><th>E-mail</th><th>Perfil</th><th>Status</th><th>Ações</th></tr></thead><tbody id="tbodyUsuarios"></tbody></table></div></div>
      </section>

      <section class="modulo box p-4" id="fazenda" style="display:none;">
        <div class="section-heading"><div><span class="text-eyebrow">Propriedade</span><h2>Dados da Fazenda</h2></div><p class="text-muted">Informações exibidas no painel principal.</p></div>
        <form class="form card p-3" id="formFazenda" onsubmit="salvarFazenda(event)">
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nome da propriedade</label><input class="form-control" name="nome" value="<?= htmlspecialchars($fazenda['nome']) ?>" required></div>
            <div class="col-md-3"><label class="form-label">Área total (ha)</label><input type="number" step="0.01" min="0" class="form-control" name="area_total_ha" value="<?= htmlspecialchars((string) $fazenda['area_total_ha']) ?>"></div>
            <div class="col-md-3"><label class="form-label">Área produtiva (ha)</label><input type="number" step="0.01" min="0" class="form-control" name="area_produtiva_ha" value="<?= htmlspecialchars((string) $fazenda['area_produtiva_ha']) ?>"></div>
            <div class="col-md-5"><label class="form-label">Município</label><input class="form-control" name="municipio" value="<?= htmlspecialchars((string) $fazenda['municipio']) ?>"></div>
            <div class="col-md-2"><label class="form-label">UF</label><input class="form-control" name="estado" maxlength="2" value="<?= htmlspecialchars((string) $fazenda['estado']) ?>"></div>
            <div class="col-md-5"><label class="form-label">Cultura principal</label><input class="form-control" name="cultura_principal" value="<?= htmlspecialchars((string) $fazenda['cultura_principal']) ?>"></div>
          </div>
          <div class="mt-3"><button class="btn btn-primary" type="submit">Atualizar painel</button></div>
        </form>
      </section>
      <?php endif; ?>
    </main>
  </div>

  <footer class="app-footer text-muted border-top py-3" style="background-color: var(--creme-card)">
    <p class="mb-0">© <?= date('Y') ?> Gestor Agro. Todos os direitos reservados.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
  <script>
    window.permissoes = <?= json_encode([
      'usuariosGerenciar' => \App\Core\Auth::can('usuarios.gerenciar'),
      'colaboradoresGerenciar' => \App\Core\Auth::can('colaboradores.gerenciar'),
      'operacoesGerenciar' => \App\Core\Auth::can('operacoes.gerenciar'),
      'manutencoesGerenciar' => \App\Core\Auth::can('manutencoes.gerenciar'),
      'equipamentosGerenciar' => \App\Core\Auth::can('equipamentos.gerenciar'),
      'financeiroGerenciar' => \App\Core\Auth::can('financeiro.gerenciar'),
    ], JSON_UNESCAPED_UNICODE) ?>;
  </script>
  <script src="modulos.js"></script>
  
  <script>
    // gráfico de receitas x despesas
    const ctx = document.getElementById('graficoFinanceiro');
    if(ctx) {
        window.graficoFinanceiroChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labelsMes) ?>,
                datasets: [
                    {
                        label: 'Receitas',
                        data: <?= json_encode($dadosReceitas) ?>,
                        backgroundColor: '#3F6B47',
                        borderRadius: 4
                    },
                    {
                        label: 'Despesas',
                        data: <?= json_encode($dadosDespesas) ?>,
                        backgroundColor: '#A8552F',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { 
                    y: { beginAtZero: true, grid: { color: '#E5DFD0' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const ctxProducao = document.getElementById('graficoProducao');
    if (ctxProducao) {
      window.graficoProducaoChart = new Chart(ctxProducao, {
        type: 'doughnut',
        data: {
          labels: <?= json_encode($culturas, JSON_UNESCAPED_UNICODE) ?>,
          datasets: [{
            data: <?= json_encode($producaoPorCultura) ?>,
            backgroundColor: ['#1F3D2B','#3F6B47','#C99A3B','#A8552F','#6E9B70','#8B6F47','#436A7A','#B7A46A'],
            borderWidth: 2,
            borderColor: '#FFFEFB'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } }
        }
      });
    }
  </script>
</body>
</html>
