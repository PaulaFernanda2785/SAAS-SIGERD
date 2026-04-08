<?php

declare(strict_types=1);

$accounts = $accounts ?? [];
$orgaos = $orgaos ?? [];
$unidades = $unidades ?? [];
$usuarios = $usuarios ?? [];
$perfis = $perfis ?? [];
$profileGuide = $profileGuide ?? [];
$vinculos = $vinculos ?? [];
$options = $options ?? [];
$tabFilters = is_array($tabFilters ?? null) ? $tabFilters : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];
$currentUfFilter = $currentUfFilter ?? null;
$canSelectAllUf = $canSelectAllUf ?? false;
$activeTab = $activeTab ?? 'contas';

$tabs = [
    'contas' => 'Contas',
    'orgaos' => 'Orgaos',
    'unidades' => 'Unidades',
    'usuarios' => 'Usuarios',
    'perfis' => 'Perfis',
    'vinculos' => 'Vinculos',
];

$metricAccounts = (int) ($pagination['contas']['total'] ?? count($accounts));
$metricUsuarios = (int) ($pagination['usuarios']['total'] ?? count($usuarios));
$metricVinculos = (int) ($pagination['vinculos']['total'] ?? count($vinculos));

$contasFilter = is_array($tabFilters['contas'] ?? null) ? $tabFilters['contas'] : [];
$orgaosFilter = is_array($tabFilters['orgaos'] ?? null) ? $tabFilters['orgaos'] : [];
$unidadesFilter = is_array($tabFilters['unidades'] ?? null) ? $tabFilters['unidades'] : [];
$usuariosFilter = is_array($tabFilters['usuarios'] ?? null) ? $tabFilters['usuarios'] : [];
$perfisFilter = is_array($tabFilters['perfis'] ?? null) ? $tabFilters['perfis'] : [];
$vinculosFilter = is_array($tabFilters['vinculos'] ?? null) ? $tabFilters['vinculos'] : [];

$contasPage = is_array($pagination['contas'] ?? null) ? $pagination['contas'] : [];
$orgaosPage = is_array($pagination['orgaos'] ?? null) ? $pagination['orgaos'] : [];
$unidadesPage = is_array($pagination['unidades'] ?? null) ? $pagination['unidades'] : [];
$usuariosPage = is_array($pagination['usuarios'] ?? null) ? $pagination['usuarios'] : [];
$perfisPage = is_array($pagination['perfis'] ?? null) ? $pagination['perfis'] : [];
$vinculosPage = is_array($pagination['vinculos'] ?? null) ? $pagination['vinculos'] : [];

$buildTabQuery = static function (string $tab, array $filters, array $extra = []) use ($currentUfFilter): string {
    $params = ['aba' => $tab];
    if ($currentUfFilter !== null) {
        $params['uf'] = $currentUfFilter;
    }

    foreach ($filters as $key => $value) {
        if (!is_string($key) || !is_scalar($value)) {
            continue;
        }
        $text = trim((string) $value);
        if ($text === '') {
            continue;
        }
        $params[$key] = $text;
    }

    foreach ($extra as $key => $value) {
        if (!is_string($key) || !is_scalar($value)) {
            continue;
        }
        $params[$key] = $value;
    }

    return http_build_query($params);
};
?>
<section class="landing-hero">
    <div class="landing-hero-inner reveal-on-scroll institution-hero">
        <span class="landing-badge">Gestao Institucional SaaS</span>
        <h1>Contas, orgaos, unidades e acessos em um fluxo unico</h1>
        <p>
            Modulos em abas sequenciais para cadastro, manutencao e governanca da estrutura institucional,
            com exclusao logica auditavel e historico de operacoes.
        </p>
        <div class="landing-metrics">
            <article>
                <strong><?= e((string) $metricAccounts) ?> contas</strong>
                <span>Contratantes ativas no ambiente</span>
            </article>
            <article>
                <strong><?= e((string) $metricUsuarios) ?> usuarios</strong>
                <span>Acessos institucionais em operacao</span>
            </article>
            <article>
                <strong><?= e((string) $metricVinculos) ?> vinculos</strong>
                <span>Relacoes usuario-perfil ativas</span>
            </article>
        </div>
    </div>
</section>

<section class="landing-section">
    <header class="landing-section-header">
        <span>Filtro</span>
        <h2>Contexto territorial da gestao institucional</h2>
    </header>
    <article class="landing-card institution-filter-card">
        <form method="get" action="<?= e(url('/admin/institucional')) ?>" class="institution-filter-form">
            <input type="hidden" name="aba" value="<?= e((string) $activeTab) ?>">
            <div class="field">
                <label for="filtro_uf">UF</label>
                <select
                    id="filtro_uf"
                    name="uf"
                    <?= $canSelectAllUf ? 'data-uf-dynamic="true" data-uf-include-empty="true" data-uf-empty-label="Todos" data-uf-selected="' . e((string) $currentUfFilter) . '"' : 'disabled' ?>
                >
                    <option value="">Todos</option>
                    <?php foreach (($options['ufs'] ?? []) as $uf): ?>
                        <?php $sigla = (string) $uf['sigla']; ?>
                        <option value="<?= e($sigla) ?>" <?= $sigla === (string) $currentUfFilter ? 'selected' : '' ?>>
                            <?= e($sigla) ?> - <?= e((string) $uf['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!$canSelectAllUf && $currentUfFilter !== null): ?>
                    <input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>">
                <?php endif; ?>
            </div>
            <button type="submit" class="button">Aplicar filtro</button>
        </form>
    </article>
</section>

<section class="landing-section institution-tabs-shell" data-institution-tabs data-active-tab="<?= e((string) $activeTab) ?>">
    <header class="landing-section-header">
        <span>Modulos</span>
        <h2>Fluxo sequencial de gestao institucional</h2>
    </header>

    <div class="institution-tabs" role="tablist" aria-label="Abas de gestao institucional">
        <?php $step = 1; ?>
        <?php foreach ($tabs as $tabKey => $tabLabel): ?>
            <button
                type="button"
                class="institution-tab"
                data-tab-trigger="<?= e($tabKey) ?>"
                role="tab"
                aria-selected="false"
                aria-controls="tab-panel-<?= e($tabKey) ?>"
            >
                <span class="institution-tab-step"><?= e((string) $step) ?></span>
                <span><?= e($tabLabel) ?></span>
            </button>
            <?php $step++; ?>
        <?php endforeach; ?>
    </div>

    <div class="institution-tab-panels">
        <section id="tab-panel-contas" class="institution-panel" data-tab-panel="contas" role="tabpanel">
            <div class="institution-panel-grid">
                <article class="landing-card institution-form-card">
                    <h3>Nova conta contratante</h3>
                    <form method="post" action="<?= e(url('/admin/institucional/contas')) ?>" data-guard-submit="true">
                        <?= App\Support\Csrf::field('admin_conta_create') ?>
                        <?php if ($currentUfFilter !== null): ?>
                            <input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>">
                        <?php endif; ?>
                        <div class="field">
                            <label for="conta_nome_fantasia">Nome fantasia</label>
                            <input id="conta_nome_fantasia" name="nome_fantasia" type="text" required>
                        </div>
                        <div class="field">
                            <label for="conta_razao_social">Razao social</label>
                            <input id="conta_razao_social" name="razao_social" type="text">
                        </div>
                        <div class="field">
                            <label for="conta_cpf_cnpj">CPF/CNPJ</label>
                            <input id="conta_cpf_cnpj" name="cpf_cnpj" type="text">
                        </div>
                        <div class="field">
                            <label for="conta_uf_sigla">UF de origem</label>
                            <?php if ($canSelectAllUf): ?>
                                <select id="conta_uf_sigla" name="uf_sigla" required data-uf-dynamic="true" data-uf-include-empty="true" data-uf-empty-label="Selecione" data-uf-selected="<?= e((string) $currentUfFilter) ?>">
                                    <option value="">Selecione</option>
                                    <?php foreach (($options['ufs'] ?? []) as $uf): ?>
                                        <?php $sigla = (string) $uf['sigla']; ?>
                                        <option value="<?= e($sigla) ?>" <?= $sigla === (string) $currentUfFilter ? 'selected' : '' ?>><?= e($sigla) ?> - <?= e((string) $uf['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php else: ?>
                                <input type="text" value="<?= e((string) ($currentUfFilter ?? '')) ?>" readonly>
                                <input type="hidden" name="uf_sigla" value="<?= e((string) ($currentUfFilter ?? '')) ?>">
                            <?php endif; ?>
                        </div>
                        <div class="field">
                            <label for="conta_email_principal">Email principal</label>
                            <input id="conta_email_principal" name="email_principal" type="email">
                        </div>
                        <div class="field">
                            <label for="conta_status_cadastral">Status</label>
                            <select id="conta_status_cadastral" name="status_cadastral">
                                <option value="ATIVA">ATIVA</option>
                                <option value="INATIVA">INATIVA</option>
                                <option value="BLOQUEADA">BLOQUEADA</option>
                            </select>
                        </div>
                        <button type="submit">Salvar conta</button>
                    </form>
                </article>

                <article class="landing-card institution-table-card">
                    <h3>Contas cadastradas</h3>
                    <form method="get" action="<?= e(url('/admin/institucional')) ?>" class="institution-list-filter-form">
                        <input type="hidden" name="aba" value="contas">
                        <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                        <div class="field">
                            <label for="f_conta_nome">Nome da conta</label>
                            <input id="f_conta_nome" name="f_conta_nome" type="text" value="<?= e((string) ($contasFilter['nome'] ?? '')) ?>" placeholder="Buscar por nome fantasia">
                        </div>
                        <div class="field">
                            <label for="f_conta_email">Email principal</label>
                            <input id="f_conta_email" name="f_conta_email" type="text" value="<?= e((string) ($contasFilter['email'] ?? '')) ?>" placeholder="Buscar por email">
                        </div>
                        <div class="field">
                            <label for="f_conta_status">Status</label>
                            <select id="f_conta_status" name="f_conta_status">
                                <option value="">Todos</option>
                                <option value="ATIVA" <?= ($contasFilter['status'] ?? '') === 'ATIVA' ? 'selected' : '' ?>>ATIVA</option>
                                <option value="INATIVA" <?= ($contasFilter['status'] ?? '') === 'INATIVA' ? 'selected' : '' ?>>INATIVA</option>
                                <option value="BLOQUEADA" <?= ($contasFilter['status'] ?? '') === 'BLOQUEADA' ? 'selected' : '' ?>>BLOQUEADA</option>
                            </select>
                        </div>
                        <div class="institution-list-filter-actions">
                            <button type="submit" class="button button-secondary">Filtrar</button>
                            <a class="button" href="<?= e(url('/admin/institucional?' . $buildTabQuery('contas', []))) ?>">Limpar</a>
                        </div>
                    </form>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>UF</th>
                                <th>Status</th>
                                <th>Email</th>
                                <th>Acoes</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($accounts === []): ?>
                                <tr>
                                    <td colspan="6" class="center muted">Nenhuma conta encontrada para o filtro aplicado.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($accounts as $row): ?>
                                <?php
                                    $statusAtual = (string) $row['status_cadastral'];
                                    $novoStatus = $statusAtual === 'ATIVA' ? 'INATIVA' : 'ATIVA';
                                    $labelStatus = $statusAtual === 'ATIVA' ? 'Inativar' : 'Ativar';
                                ?>
                                <tr>
                                    <td><?= e((string) $row['id']) ?></td>
                                    <td><?= e((string) $row['nome_fantasia']) ?></td>
                                    <td><?= e((string) ($row['uf_sigla'] ?? '')) ?></td>
                                    <td><?= e($statusAtual) ?></td>
                                    <td><?= e((string) ($row['email_principal'] ?? '')) ?></td>
                                    <td>
                                        <div class="institution-actions">
                                            <details>
                                                <summary>Ver detalhe</summary>
                                                <div class="institution-detail-box">
                                                    <p><strong>Razao social:</strong> <?= e((string) ($row['razao_social'] ?? 'N/A')) ?></p>
                                                    <p><strong>CPF/CNPJ:</strong> <?= e((string) ($row['cpf_cnpj'] ?? 'N/A')) ?></p>
                                                    <p><strong>Criado em:</strong> <?= e((string) ($row['created_at'] ?? 'N/A')) ?></p>
                                                </div>
                                            </details>
                                            <details>
                                                <summary>Editar</summary>
                                                <form method="post" action="<?= e(url('/admin/institucional/acoes')) ?>" data-guard-submit="true" class="institution-inline-form">
                                                    <?= App\Support\Csrf::field('admin_institucional_action') ?>
                                                    <input type="hidden" name="entity" value="conta">
                                                    <input type="hidden" name="action" value="editar">
                                                    <input type="hidden" name="entity_id" value="<?= e((string) $row['id']) ?>">
                                                    <input type="hidden" name="aba" value="contas">
                                                    <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                                                    <div class="field"><label>Nome</label><input type="text" name="nome_fantasia" value="<?= e((string) $row['nome_fantasia']) ?>" required></div>
                                                    <div class="field"><label>Razao social</label><input type="text" name="razao_social" value="<?= e((string) ($row['razao_social'] ?? '')) ?>"></div>
                                                    <div class="field"><label>CPF/CNPJ</label><input type="text" name="cpf_cnpj" value="<?= e((string) ($row['cpf_cnpj'] ?? '')) ?>"></div>
                                                    <div class="field"><label>Email principal</label><input type="email" name="email_principal" value="<?= e((string) ($row['email_principal'] ?? '')) ?>"></div>
                                                    <div class="field">
                                                        <label>Status</label>
                                                        <select name="status_cadastral">
                                                            <option value="ATIVA" <?= $statusAtual === 'ATIVA' ? 'selected' : '' ?>>ATIVA</option>
                                                            <option value="INATIVA" <?= $statusAtual === 'INATIVA' ? 'selected' : '' ?>>INATIVA</option>
                                                            <option value="BLOQUEADA" <?= $statusAtual === 'BLOQUEADA' ? 'selected' : '' ?>>BLOQUEADA</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit">Salvar edicao</button>
                                                </form>
                                            </details>
                                            <form method="post" action="<?= e(url('/admin/institucional/acoes')) ?>" data-guard-submit="true">
                                                <?= App\Support\Csrf::field('admin_institucional_action') ?>
                                                <input type="hidden" name="entity" value="conta">
                                                <input type="hidden" name="action" value="status">
                                                <input type="hidden" name="entity_id" value="<?= e((string) $row['id']) ?>">
                                                <input type="hidden" name="novo_status" value="<?= e($novoStatus) ?>">
                                                <input type="hidden" name="aba" value="contas">
                                                <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                                                <button type="submit" class="button button-secondary"><?= e($labelStatus) ?></button>
                                            </form>
                                            <button
                                                type="button"
                                                class="button institution-delete-btn"
                                                data-delete-open
                                                data-entity="conta"
                                                data-entity-id="<?= e((string) $row['id']) ?>"
                                                data-entity-label="Conta <?= e((string) $row['nome_fantasia']) ?>"
                                                data-tab="contas"
                                            >Excluir</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ((int) ($contasPage['pages'] ?? 1) > 1): ?>
                        <nav class="institution-pagination" aria-label="Paginacao de contas">
                            <?php if ((bool) ($contasPage['has_prev'] ?? false)): ?>
                                <a class="institution-pagination-link" href="<?= e(url('/admin/institucional?' . $buildTabQuery('contas', $contasFilter, ['page_contas' => (int) ($contasPage['prev_page'] ?? 1)]))) ?>">Anterior</a>
                            <?php else: ?>
                                <span class="institution-pagination-link is-disabled">Anterior</span>
                            <?php endif; ?>
                            <span class="institution-pagination-status">
                                Pagina <?= e((string) ($contasPage['page'] ?? 1)) ?> de <?= e((string) ($contasPage['pages'] ?? 1)) ?>
                                - Exibindo <?= e((string) ($contasPage['from'] ?? 0)) ?>-<?= e((string) ($contasPage['to'] ?? 0)) ?> de <?= e((string) ($contasPage['total'] ?? 0)) ?>
                            </span>
                            <?php if ((bool) ($contasPage['has_next'] ?? false)): ?>
                                <a class="institution-pagination-link" href="<?= e(url('/admin/institucional?' . $buildTabQuery('contas', $contasFilter, ['page_contas' => (int) ($contasPage['next_page'] ?? 1)]))) ?>">Proxima</a>
                            <?php else: ?>
                                <span class="institution-pagination-link is-disabled">Proxima</span>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </article>
            </div>
        </section>
        <section id="tab-panel-orgaos" class="institution-panel" data-tab-panel="orgaos" role="tabpanel">
            <div class="institution-panel-grid">
                <article class="landing-card institution-form-card">
                    <h3>Novo orgao</h3>
                    <form method="post" action="<?= e(url('/admin/institucional/orgaos')) ?>" data-guard-submit="true">
                        <?= App\Support\Csrf::field('admin_orgao_create') ?>
                        <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                        <div class="field">
                            <label for="orgao_conta_id">Conta</label>
                            <select id="orgao_conta_id" name="conta_id" required>
                                <option value="">Selecione</option>
                                <?php foreach (($options['contas'] ?? []) as $conta): ?>
                                    <option value="<?= e((string) $conta['id']) ?>"><?= e((string) $conta['nome_fantasia']) ?> - <?= e((string) ($conta['uf_sigla'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field"><label for="orgao_nome_oficial">Nome oficial</label><input id="orgao_nome_oficial" name="nome_oficial" type="text" required></div>
                        <div class="field"><label for="orgao_sigla">Sigla</label><input id="orgao_sigla" name="sigla" type="text"></div>
                        <div class="field"><label for="orgao_cnpj">CNPJ</label><input id="orgao_cnpj" name="cnpj" type="text"></div>
                        <div class="field">
                            <label for="orgao_status_orgao">Status</label>
                            <select id="orgao_status_orgao" name="status_orgao">
                                <option value="ATIVO">ATIVO</option>
                                <option value="INATIVO">INATIVO</option>
                                <option value="BLOQUEADO">BLOQUEADO</option>
                            </select>
                        </div>
                        <button type="submit">Salvar orgao</button>
                    </form>
                </article>

                <article class="landing-card institution-table-card">
                    <h3>Orgaos cadastrados</h3>
                    <form method="get" action="<?= e(url('/admin/institucional')) ?>" class="institution-list-filter-form">
                        <input type="hidden" name="aba" value="orgaos">
                        <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                        <div class="field">
                            <label for="f_orgao_nome">Nome oficial</label>
                            <input id="f_orgao_nome" name="f_orgao_nome" type="text" value="<?= e((string) ($orgaosFilter['nome'] ?? '')) ?>" placeholder="Buscar por nome oficial">
                        </div>
                        <div class="field">
                            <label for="f_orgao_sigla">Sigla</label>
                            <input id="f_orgao_sigla" name="f_orgao_sigla" type="text" value="<?= e((string) ($orgaosFilter['sigla'] ?? '')) ?>" placeholder="Buscar por sigla">
                        </div>
                        <div class="field">
                            <label for="f_orgao_status">Status</label>
                            <select id="f_orgao_status" name="f_orgao_status">
                                <option value="">Todos</option>
                                <option value="ATIVO" <?= ($orgaosFilter['status'] ?? '') === 'ATIVO' ? 'selected' : '' ?>>ATIVO</option>
                                <option value="INATIVO" <?= ($orgaosFilter['status'] ?? '') === 'INATIVO' ? 'selected' : '' ?>>INATIVO</option>
                                <option value="BLOQUEADO" <?= ($orgaosFilter['status'] ?? '') === 'BLOQUEADO' ? 'selected' : '' ?>>BLOQUEADO</option>
                            </select>
                        </div>
                        <div class="institution-list-filter-actions">
                            <button type="submit" class="button button-secondary">Filtrar</button>
                            <a class="button" href="<?= e(url('/admin/institucional?' . $buildTabQuery('orgaos', []))) ?>">Limpar</a>
                        </div>
                    </form>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Conta</th>
                                <th>Nome</th>
                                <th>UF</th>
                                <th>Status</th>
                                <th>Acoes</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($orgaos === []): ?>
                                <tr>
                                    <td colspan="6" class="center muted">Nenhum orgao encontrado para o filtro aplicado.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($orgaos as $row): ?>
                                <?php
                                    $statusAtual = (string) $row['status_orgao'];
                                    $novoStatus = $statusAtual === 'ATIVO' ? 'INATIVO' : 'ATIVO';
                                    $labelStatus = $statusAtual === 'ATIVO' ? 'Inativar' : 'Ativar';
                                ?>
                                <tr>
                                    <td><?= e((string) $row['id']) ?></td>
                                    <td><?= e((string) $row['conta_nome']) ?></td>
                                    <td><?= e((string) $row['nome_oficial']) ?></td>
                                    <td><?= e((string) ($row['uf_sigla'] ?? '')) ?></td>
                                    <td><?= e($statusAtual) ?></td>
                                    <td>
                                        <div class="institution-actions">
                                            <details>
                                                <summary>Ver detalhe</summary>
                                                <div class="institution-detail-box">
                                                    <p><strong>Sigla:</strong> <?= e((string) ($row['sigla'] ?? 'N/A')) ?></p>
                                                    <p><strong>CNPJ:</strong> <?= e((string) ($row['cnpj'] ?? 'N/A')) ?></p>
                                                    <p><strong>Criado em:</strong> <?= e((string) ($row['created_at'] ?? 'N/A')) ?></p>
                                                </div>
                                            </details>
                                            <details>
                                                <summary>Editar</summary>
                                                <form method="post" action="<?= e(url('/admin/institucional/acoes')) ?>" data-guard-submit="true" class="institution-inline-form">
                                                    <?= App\Support\Csrf::field('admin_institucional_action') ?>
                                                    <input type="hidden" name="entity" value="orgao">
                                                    <input type="hidden" name="action" value="editar">
                                                    <input type="hidden" name="entity_id" value="<?= e((string) $row['id']) ?>">
                                                    <input type="hidden" name="aba" value="orgaos">
                                                    <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                                                    <div class="field"><label>Nome oficial</label><input type="text" name="nome_oficial" value="<?= e((string) $row['nome_oficial']) ?>" required></div>
                                                    <div class="field"><label>Sigla</label><input type="text" name="sigla" value="<?= e((string) ($row['sigla'] ?? '')) ?>"></div>
                                                    <div class="field"><label>CNPJ</label><input type="text" name="cnpj" value="<?= e((string) ($row['cnpj'] ?? '')) ?>"></div>
                                                    <div class="field">
                                                        <label>Status</label>
                                                        <select name="status_orgao">
                                                            <option value="ATIVO" <?= $statusAtual === 'ATIVO' ? 'selected' : '' ?>>ATIVO</option>
                                                            <option value="INATIVO" <?= $statusAtual === 'INATIVO' ? 'selected' : '' ?>>INATIVO</option>
                                                            <option value="BLOQUEADO" <?= $statusAtual === 'BLOQUEADO' ? 'selected' : '' ?>>BLOQUEADO</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit">Salvar edicao</button>
                                                </form>
                                            </details>
                                            <form method="post" action="<?= e(url('/admin/institucional/acoes')) ?>" data-guard-submit="true">
                                                <?= App\Support\Csrf::field('admin_institucional_action') ?>
                                                <input type="hidden" name="entity" value="orgao">
                                                <input type="hidden" name="action" value="status">
                                                <input type="hidden" name="entity_id" value="<?= e((string) $row['id']) ?>">
                                                <input type="hidden" name="novo_status" value="<?= e($novoStatus) ?>">
                                                <input type="hidden" name="aba" value="orgaos">
                                                <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                                                <button type="submit" class="button button-secondary"><?= e($labelStatus) ?></button>
                                            </form>
                                            <button type="button" class="button institution-delete-btn" data-delete-open data-entity="orgao" data-entity-id="<?= e((string) $row['id']) ?>" data-entity-label="Orgao <?= e((string) $row['nome_oficial']) ?>" data-tab="orgaos">Excluir</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ((int) ($orgaosPage['pages'] ?? 1) > 1): ?>
                        <nav class="institution-pagination" aria-label="Paginacao de orgaos">
                            <?php if ((bool) ($orgaosPage['has_prev'] ?? false)): ?>
                                <a class="institution-pagination-link" href="<?= e(url('/admin/institucional?' . $buildTabQuery('orgaos', $orgaosFilter, ['page_orgaos' => (int) ($orgaosPage['prev_page'] ?? 1)]))) ?>">Anterior</a>
                            <?php else: ?>
                                <span class="institution-pagination-link is-disabled">Anterior</span>
                            <?php endif; ?>
                            <span class="institution-pagination-status">
                                Pagina <?= e((string) ($orgaosPage['page'] ?? 1)) ?> de <?= e((string) ($orgaosPage['pages'] ?? 1)) ?>
                                - Exibindo <?= e((string) ($orgaosPage['from'] ?? 0)) ?>-<?= e((string) ($orgaosPage['to'] ?? 0)) ?> de <?= e((string) ($orgaosPage['total'] ?? 0)) ?>
                            </span>
                            <?php if ((bool) ($orgaosPage['has_next'] ?? false)): ?>
                                <a class="institution-pagination-link" href="<?= e(url('/admin/institucional?' . $buildTabQuery('orgaos', $orgaosFilter, ['page_orgaos' => (int) ($orgaosPage['next_page'] ?? 1)]))) ?>">Proxima</a>
                            <?php else: ?>
                                <span class="institution-pagination-link is-disabled">Proxima</span>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </article>
            </div>
        </section>
        <section id="tab-panel-unidades" class="institution-panel" data-tab-panel="unidades" role="tabpanel">
            <div class="institution-panel-grid">
                <article class="landing-card institution-form-card">
                    <h3>Nova unidade</h3>
                    <form method="post" action="<?= e(url('/admin/institucional/unidades')) ?>" data-guard-submit="true">
                        <?= App\Support\Csrf::field('admin_unidade_create') ?>
                        <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                        <div class="field">
                            <label for="unidade_orgao_id">Orgao</label>
                            <select id="unidade_orgao_id" name="orgao_id" required>
                                <option value="">Selecione</option>
                                <?php foreach (($options['orgaos'] ?? []) as $orgao): ?>
                                    <option value="<?= e((string) $orgao['id']) ?>"><?= e((string) $orgao['nome_oficial']) ?> - <?= e((string) ($orgao['uf_sigla'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field"><label for="unidade_nome_unidade">Nome da unidade</label><input id="unidade_nome_unidade" name="nome_unidade" type="text" required></div>
                        <div class="field"><label for="unidade_codigo_unidade">Codigo</label><input id="unidade_codigo_unidade" name="codigo_unidade" type="text"></div>
                        <div class="field"><label for="unidade_tipo_unidade">Tipo</label><input id="unidade_tipo_unidade" name="tipo_unidade" type="text" placeholder="SEDE, REGIONAL, BASE"></div>
                        <div class="field">
                            <label for="unidade_status_unidade">Status</label>
                            <select id="unidade_status_unidade" name="status_unidade">
                                <option value="ATIVA">ATIVA</option>
                                <option value="INATIVA">INATIVA</option>
                            </select>
                        </div>
                        <button type="submit">Salvar unidade</button>
                    </form>
                </article>

                <article class="landing-card institution-table-card">
                    <h3>Unidades cadastradas</h3>
                    <form method="get" action="<?= e(url('/admin/institucional')) ?>" class="institution-list-filter-form">
                        <input type="hidden" name="aba" value="unidades">
                        <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                        <div class="field">
                            <label for="f_unidade_nome">Nome da unidade</label>
                            <input id="f_unidade_nome" name="f_unidade_nome" type="text" value="<?= e((string) ($unidadesFilter['nome'] ?? '')) ?>" placeholder="Buscar por nome da unidade">
                        </div>
                        <div class="field">
                            <label for="f_unidade_codigo">Codigo da unidade</label>
                            <input id="f_unidade_codigo" name="f_unidade_codigo" type="text" value="<?= e((string) ($unidadesFilter['codigo'] ?? '')) ?>" placeholder="Buscar por codigo">
                        </div>
                        <div class="field">
                            <label for="f_unidade_status">Status</label>
                            <select id="f_unidade_status" name="f_unidade_status">
                                <option value="">Todos</option>
                                <option value="ATIVA" <?= ($unidadesFilter['status'] ?? '') === 'ATIVA' ? 'selected' : '' ?>>ATIVA</option>
                                <option value="INATIVA" <?= ($unidadesFilter['status'] ?? '') === 'INATIVA' ? 'selected' : '' ?>>INATIVA</option>
                            </select>
                        </div>
                        <div class="institution-list-filter-actions">
                            <button type="submit" class="button button-secondary">Filtrar</button>
                            <a class="button" href="<?= e(url('/admin/institucional?' . $buildTabQuery('unidades', []))) ?>">Limpar</a>
                        </div>
                    </form>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Orgao</th>
                                <th>Nome</th>
                                <th>Codigo</th>
                                <th>UF</th>
                                <th>Status</th>
                                <th>Acoes</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($unidades === []): ?>
                                <tr>
                                    <td colspan="7" class="center muted">Nenhuma unidade encontrada para o filtro aplicado.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($unidades as $row): ?>
                                <?php
                                    $statusAtual = (string) $row['status_unidade'];
                                    $novoStatus = $statusAtual === 'ATIVA' ? 'INATIVA' : 'ATIVA';
                                    $labelStatus = $statusAtual === 'ATIVA' ? 'Inativar' : 'Ativar';
                                ?>
                                <tr>
                                    <td><?= e((string) $row['id']) ?></td>
                                    <td><?= e((string) $row['orgao_nome']) ?></td>
                                    <td><?= e((string) $row['nome_unidade']) ?></td>
                                    <td><?= e((string) ($row['codigo_unidade'] ?? '')) ?></td>
                                    <td><?= e((string) ($row['uf_sigla'] ?? '')) ?></td>
                                    <td><?= e($statusAtual) ?></td>
                                    <td>
                                        <div class="institution-actions">
                                            <details>
                                                <summary>Ver detalhe</summary>
                                                <div class="institution-detail-box">
                                                    <p><strong>Tipo:</strong> <?= e((string) ($row['tipo_unidade'] ?? 'N/A')) ?></p>
                                                    <p><strong>Criado em:</strong> <?= e((string) ($row['created_at'] ?? 'N/A')) ?></p>
                                                </div>
                                            </details>
                                            <details>
                                                <summary>Editar</summary>
                                                <form method="post" action="<?= e(url('/admin/institucional/acoes')) ?>" data-guard-submit="true" class="institution-inline-form">
                                                    <?= App\Support\Csrf::field('admin_institucional_action') ?>
                                                    <input type="hidden" name="entity" value="unidade">
                                                    <input type="hidden" name="action" value="editar">
                                                    <input type="hidden" name="entity_id" value="<?= e((string) $row['id']) ?>">
                                                    <input type="hidden" name="aba" value="unidades">
                                                    <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                                                    <div class="field"><label>Nome da unidade</label><input type="text" name="nome_unidade" value="<?= e((string) $row['nome_unidade']) ?>" required></div>
                                                    <div class="field"><label>Codigo</label><input type="text" name="codigo_unidade" value="<?= e((string) ($row['codigo_unidade'] ?? '')) ?>"></div>
                                                    <div class="field"><label>Tipo</label><input type="text" name="tipo_unidade" value="<?= e((string) ($row['tipo_unidade'] ?? '')) ?>"></div>
                                                    <div class="field">
                                                        <label>Status</label>
                                                        <select name="status_unidade">
                                                            <option value="ATIVA" <?= $statusAtual === 'ATIVA' ? 'selected' : '' ?>>ATIVA</option>
                                                            <option value="INATIVA" <?= $statusAtual === 'INATIVA' ? 'selected' : '' ?>>INATIVA</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit">Salvar edicao</button>
                                                </form>
                                            </details>
                                            <form method="post" action="<?= e(url('/admin/institucional/acoes')) ?>" data-guard-submit="true">
                                                <?= App\Support\Csrf::field('admin_institucional_action') ?>
                                                <input type="hidden" name="entity" value="unidade">
                                                <input type="hidden" name="action" value="status">
                                                <input type="hidden" name="entity_id" value="<?= e((string) $row['id']) ?>">
                                                <input type="hidden" name="novo_status" value="<?= e($novoStatus) ?>">
                                                <input type="hidden" name="aba" value="unidades">
                                                <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                                                <button type="submit" class="button button-secondary"><?= e($labelStatus) ?></button>
                                            </form>
                                            <button type="button" class="button institution-delete-btn" data-delete-open data-entity="unidade" data-entity-id="<?= e((string) $row['id']) ?>" data-entity-label="Unidade <?= e((string) $row['nome_unidade']) ?>" data-tab="unidades">Excluir</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ((int) ($unidadesPage['pages'] ?? 1) > 1): ?>
                        <nav class="institution-pagination" aria-label="Paginacao de unidades">
                            <?php if ((bool) ($unidadesPage['has_prev'] ?? false)): ?>
                                <a class="institution-pagination-link" href="<?= e(url('/admin/institucional?' . $buildTabQuery('unidades', $unidadesFilter, ['page_unidades' => (int) ($unidadesPage['prev_page'] ?? 1)]))) ?>">Anterior</a>
                            <?php else: ?>
                                <span class="institution-pagination-link is-disabled">Anterior</span>
                            <?php endif; ?>
                            <span class="institution-pagination-status">
                                Pagina <?= e((string) ($unidadesPage['page'] ?? 1)) ?> de <?= e((string) ($unidadesPage['pages'] ?? 1)) ?>
                                - Exibindo <?= e((string) ($unidadesPage['from'] ?? 0)) ?>-<?= e((string) ($unidadesPage['to'] ?? 0)) ?> de <?= e((string) ($unidadesPage['total'] ?? 0)) ?>
                            </span>
                            <?php if ((bool) ($unidadesPage['has_next'] ?? false)): ?>
                                <a class="institution-pagination-link" href="<?= e(url('/admin/institucional?' . $buildTabQuery('unidades', $unidadesFilter, ['page_unidades' => (int) ($unidadesPage['next_page'] ?? 1)]))) ?>">Proxima</a>
                            <?php else: ?>
                                <span class="institution-pagination-link is-disabled">Proxima</span>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </article>
            </div>
        </section>
        <section id="tab-panel-usuarios" class="institution-panel" data-tab-panel="usuarios" role="tabpanel">
            <div class="institution-panel-grid">
                <article class="landing-card institution-form-card">
                    <div class="institution-card-header">
                        <h3>Novo usuario</h3>
                        <button type="button" class="button button-secondary institution-guide-trigger" data-profile-guide-open>Guia de perfis</button>
                    </div>
                    <form method="post" action="<?= e(url('/admin/institucional/usuarios')) ?>" data-guard-submit="true">
                        <?= App\Support\Csrf::field('admin_usuario_create') ?>
                        <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                        <div class="field">
                            <label for="usuario_conta_id">Conta</label>
                            <select id="usuario_conta_id" name="conta_id" required>
                                <option value="">Selecione</option>
                                <?php foreach (($options['contas'] ?? []) as $conta): ?>
                                    <option value="<?= e((string) $conta['id']) ?>"><?= e((string) $conta['nome_fantasia']) ?> - <?= e((string) ($conta['uf_sigla'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="usuario_orgao_id">Orgao</label>
                            <select id="usuario_orgao_id" name="orgao_id" required>
                                <option value="">Selecione</option>
                                <?php foreach (($options['orgaos'] ?? []) as $orgao): ?>
                                    <option value="<?= e((string) $orgao['id']) ?>"><?= e((string) $orgao['nome_oficial']) ?> - <?= e((string) ($orgao['uf_sigla'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="usuario_unidade_id">Unidade</label>
                            <select id="usuario_unidade_id" name="unidade_id">
                                <option value="">Sem unidade</option>
                                <?php foreach (($options['unidades'] ?? []) as $unidade): ?>
                                    <option value="<?= e((string) $unidade['id']) ?>"><?= e((string) $unidade['nome_unidade']) ?> - <?= e((string) ($unidade['uf_sigla'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field"><label for="usuario_nome_completo">Nome completo</label><input id="usuario_nome_completo" name="nome_completo" type="text" required></div>
                        <div class="field"><label for="usuario_email_login">Email/login</label><input id="usuario_email_login" name="email_login" type="email" required></div>
                        <div class="field"><label for="usuario_matricula_funcional">Matricula</label><input id="usuario_matricula_funcional" name="matricula_funcional" type="text"></div>
                        <div class="field"><label for="usuario_password">Senha inicial</label><input id="usuario_password" name="password" type="password" required></div>
                        <div class="field">
                            <label for="usuario_perfil_id">Perfil inicial (opcional)</label>
                            <select id="usuario_perfil_id" name="perfil_id">
                                <option value="">Sem perfil inicial</option>
                                <?php foreach (($options['perfis'] ?? []) as $perfil): ?>
                                    <option value="<?= e((string) $perfil['id']) ?>"><?= e((string) $perfil['nome_perfil']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="usuario_status_usuario">Status</label>
                            <select id="usuario_status_usuario" name="status_usuario">
                                <option value="ATIVO">ATIVO</option>
                                <option value="INATIVO">INATIVO</option>
                                <option value="BLOQUEADO">BLOQUEADO</option>
                            </select>
                        </div>
                        <button type="submit">Salvar usuario</button>
                    </form>
                </article>

                <article class="landing-card institution-table-card">
                    <h3>Usuarios cadastrados</h3>
                    <form method="get" action="<?= e(url('/admin/institucional')) ?>" class="institution-list-filter-form">
                        <input type="hidden" name="aba" value="usuarios">
                        <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                        <div class="field">
                            <label for="f_usuario_nome">Nome completo</label>
                            <input id="f_usuario_nome" name="f_usuario_nome" type="text" value="<?= e((string) ($usuariosFilter['nome'] ?? '')) ?>" placeholder="Buscar por nome">
                        </div>
                        <div class="field">
                            <label for="f_usuario_email">Email/login</label>
                            <input id="f_usuario_email" name="f_usuario_email" type="text" value="<?= e((string) ($usuariosFilter['email'] ?? '')) ?>" placeholder="Buscar por email/login">
                        </div>
                        <div class="field">
                            <label for="f_usuario_status">Status</label>
                            <select id="f_usuario_status" name="f_usuario_status">
                                <option value="">Todos</option>
                                <option value="ATIVO" <?= ($usuariosFilter['status'] ?? '') === 'ATIVO' ? 'selected' : '' ?>>ATIVO</option>
                                <option value="INATIVO" <?= ($usuariosFilter['status'] ?? '') === 'INATIVO' ? 'selected' : '' ?>>INATIVO</option>
                                <option value="BLOQUEADO" <?= ($usuariosFilter['status'] ?? '') === 'BLOQUEADO' ? 'selected' : '' ?>>BLOQUEADO</option>
                            </select>
                        </div>
                        <div class="institution-list-filter-actions">
                            <button type="submit" class="button button-secondary">Filtrar</button>
                            <a class="button" href="<?= e(url('/admin/institucional?' . $buildTabQuery('usuarios', []))) ?>">Limpar</a>
                        </div>
                    </form>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Login</th>
                                <th>Conta</th>
                                <th>Orgao</th>
                                <th>Status</th>
                                <th>Acoes</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($usuarios === []): ?>
                                <tr>
                                    <td colspan="7" class="center muted">Nenhum usuario encontrado para o filtro aplicado.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($usuarios as $row): ?>
                                <?php
                                    $statusAtual = (string) $row['status_usuario'];
                                    $novoStatus = $statusAtual === 'ATIVO' ? 'INATIVO' : 'ATIVO';
                                    $labelStatus = $statusAtual === 'ATIVO' ? 'Inativar' : 'Ativar';
                                ?>
                                <tr>
                                    <td><?= e((string) $row['id']) ?></td>
                                    <td><?= e((string) $row['nome_completo']) ?></td>
                                    <td><?= e((string) $row['email_login']) ?></td>
                                    <td><?= e((string) $row['conta_nome']) ?></td>
                                    <td><?= e((string) $row['orgao_nome']) ?></td>
                                    <td><?= e($statusAtual) ?></td>
                                    <td>
                                        <div class="institution-actions">
                                            <details>
                                                <summary>Ver detalhe</summary>
                                                <div class="institution-detail-box">
                                                    <p><strong>Unidade:</strong> <?= e((string) ($row['unidade_nome'] ?? 'N/A')) ?></p>
                                                    <p><strong>Matricula:</strong> <?= e((string) ($row['matricula_funcional'] ?? 'N/A')) ?></p>
                                                    <p><strong>UF:</strong> <?= e((string) ($row['uf_sigla'] ?? 'N/A')) ?></p>
                                                </div>
                                            </details>
                                            <details>
                                                <summary>Editar</summary>
                                                <form method="post" action="<?= e(url('/admin/institucional/acoes')) ?>" data-guard-submit="true" class="institution-inline-form">
                                                    <?= App\Support\Csrf::field('admin_institucional_action') ?>
                                                    <input type="hidden" name="entity" value="usuario">
                                                    <input type="hidden" name="action" value="editar">
                                                    <input type="hidden" name="entity_id" value="<?= e((string) $row['id']) ?>">
                                                    <input type="hidden" name="aba" value="usuarios">
                                                    <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                                                    <div class="field"><label>Nome completo</label><input type="text" name="nome_completo" value="<?= e((string) $row['nome_completo']) ?>" required></div>
                                                    <div class="field"><label>Email/login</label><input type="email" name="email_login" value="<?= e((string) $row['email_login']) ?>" required></div>
                                                    <div class="field"><label>Matricula</label><input type="text" name="matricula_funcional" value="<?= e((string) ($row['matricula_funcional'] ?? '')) ?>"></div>
                                                    <div class="field">
                                                        <label>Unidade</label>
                                                        <select name="unidade_id">
                                                            <option value="">Sem unidade</option>
                                                            <?php foreach (($options['unidades'] ?? []) as $optUnidade): ?>
                                                                <?php $idOpt = (int) $optUnidade['id']; ?>
                                                                <option value="<?= e((string) $idOpt) ?>" <?= $idOpt === (int) ($row['unidade_id'] ?? 0) ? 'selected' : '' ?>>
                                                                    <?= e((string) $optUnidade['nome_unidade']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="field">
                                                        <label>Status</label>
                                                        <select name="status_usuario">
                                                            <option value="ATIVO" <?= $statusAtual === 'ATIVO' ? 'selected' : '' ?>>ATIVO</option>
                                                            <option value="INATIVO" <?= $statusAtual === 'INATIVO' ? 'selected' : '' ?>>INATIVO</option>
                                                            <option value="BLOQUEADO" <?= $statusAtual === 'BLOQUEADO' ? 'selected' : '' ?>>BLOQUEADO</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit">Salvar edicao</button>
                                                </form>
                                            </details>
                                            <form method="post" action="<?= e(url('/admin/institucional/acoes')) ?>" data-guard-submit="true">
                                                <?= App\Support\Csrf::field('admin_institucional_action') ?>
                                                <input type="hidden" name="entity" value="usuario">
                                                <input type="hidden" name="action" value="status">
                                                <input type="hidden" name="entity_id" value="<?= e((string) $row['id']) ?>">
                                                <input type="hidden" name="novo_status" value="<?= e($novoStatus) ?>">
                                                <input type="hidden" name="aba" value="usuarios">
                                                <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                                                <button type="submit" class="button button-secondary"><?= e($labelStatus) ?></button>
                                            </form>
                                            <button type="button" class="button institution-delete-btn" data-delete-open data-entity="usuario" data-entity-id="<?= e((string) $row['id']) ?>" data-entity-label="Usuario <?= e((string) $row['nome_completo']) ?>" data-tab="usuarios">Excluir</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ((int) ($usuariosPage['pages'] ?? 1) > 1): ?>
                        <nav class="institution-pagination" aria-label="Paginacao de usuarios">
                            <?php if ((bool) ($usuariosPage['has_prev'] ?? false)): ?>
                                <a class="institution-pagination-link" href="<?= e(url('/admin/institucional?' . $buildTabQuery('usuarios', $usuariosFilter, ['page_usuarios' => (int) ($usuariosPage['prev_page'] ?? 1)]))) ?>">Anterior</a>
                            <?php else: ?>
                                <span class="institution-pagination-link is-disabled">Anterior</span>
                            <?php endif; ?>
                            <span class="institution-pagination-status">
                                Pagina <?= e((string) ($usuariosPage['page'] ?? 1)) ?> de <?= e((string) ($usuariosPage['pages'] ?? 1)) ?>
                                - Exibindo <?= e((string) ($usuariosPage['from'] ?? 0)) ?>-<?= e((string) ($usuariosPage['to'] ?? 0)) ?> de <?= e((string) ($usuariosPage['total'] ?? 0)) ?>
                            </span>
                            <?php if ((bool) ($usuariosPage['has_next'] ?? false)): ?>
                                <a class="institution-pagination-link" href="<?= e(url('/admin/institucional?' . $buildTabQuery('usuarios', $usuariosFilter, ['page_usuarios' => (int) ($usuariosPage['next_page'] ?? 1)]))) ?>">Proxima</a>
                            <?php else: ?>
                                <span class="institution-pagination-link is-disabled">Proxima</span>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </article>
            </div>
        </section>
        <section id="tab-panel-perfis" class="institution-panel" data-tab-panel="perfis" role="tabpanel">
            <div class="institution-panel-grid">
                <article class="landing-card institution-form-card">
                    <div class="institution-card-header">
                        <h3>Novo perfil</h3>
                        <button type="button" class="button button-secondary institution-guide-trigger" data-profile-guide-open>Guia de perfis</button>
                    </div>
                    <form method="post" action="<?= e(url('/admin/institucional/perfis')) ?>" data-guard-submit="true">
                        <?= App\Support\Csrf::field('admin_perfil_create') ?>
                        <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                        <div class="field"><label for="perfil_nome_perfil">Nome do perfil</label><input id="perfil_nome_perfil" name="nome_perfil" type="text" placeholder="GESTOR_REGIONAL" required></div>
                        <div class="field"><label for="perfil_descricao">Descricao</label><input id="perfil_descricao" name="descricao" type="text"></div>
                        <div class="field">
                            <label for="perfil_status_perfil">Status</label>
                            <select id="perfil_status_perfil" name="status_perfil">
                                <option value="ATIVO">ATIVO</option>
                                <option value="INATIVO">INATIVO</option>
                            </select>
                        </div>
                        <button type="submit">Salvar perfil</button>
                    </form>
                </article>

                <article class="landing-card institution-table-card">
                    <h3>Perfis cadastrados</h3>
                    <form method="get" action="<?= e(url('/admin/institucional')) ?>" class="institution-list-filter-form">
                        <input type="hidden" name="aba" value="perfis">
                        <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                        <div class="field">
                            <label for="f_perfil_nome">Nome do perfil</label>
                            <input id="f_perfil_nome" name="f_perfil_nome" type="text" value="<?= e((string) ($perfisFilter['nome'] ?? '')) ?>" placeholder="Buscar por nome do perfil">
                        </div>
                        <div class="field">
                            <label for="f_perfil_status">Status</label>
                            <select id="f_perfil_status" name="f_perfil_status">
                                <option value="">Todos</option>
                                <option value="ATIVO" <?= ($perfisFilter['status'] ?? '') === 'ATIVO' ? 'selected' : '' ?>>ATIVO</option>
                                <option value="INATIVO" <?= ($perfisFilter['status'] ?? '') === 'INATIVO' ? 'selected' : '' ?>>INATIVO</option>
                            </select>
                        </div>
                        <div class="institution-list-filter-actions">
                            <button type="submit" class="button button-secondary">Filtrar</button>
                            <a class="button" href="<?= e(url('/admin/institucional?' . $buildTabQuery('perfis', []))) ?>">Limpar</a>
                        </div>
                    </form>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Perfil</th>
                                <th>Descricao</th>
                                <th>Status</th>
                                <th>Acoes</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($perfis === []): ?>
                                <tr>
                                    <td colspan="5" class="center muted">Nenhum perfil encontrado para o filtro aplicado.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($perfis as $row): ?>
                                <?php
                                    $statusAtual = (string) $row['status_perfil'];
                                    $novoStatus = $statusAtual === 'ATIVO' ? 'INATIVO' : 'ATIVO';
                                    $labelStatus = $statusAtual === 'ATIVO' ? 'Inativar' : 'Ativar';
                                ?>
                                <tr>
                                    <td><?= e((string) $row['id']) ?></td>
                                    <td><?= e((string) $row['nome_perfil']) ?></td>
                                    <td><?= e((string) ($row['descricao'] ?? '')) ?></td>
                                    <td><?= e($statusAtual) ?></td>
                                    <td>
                                        <div class="institution-actions">
                                            <details>
                                                <summary>Ver detalhe</summary>
                                                <div class="institution-detail-box">
                                                    <p><strong>Criado em:</strong> <?= e((string) ($row['created_at'] ?? 'N/A')) ?></p>
                                                </div>
                                            </details>
                                            <details>
                                                <summary>Editar</summary>
                                                <form method="post" action="<?= e(url('/admin/institucional/acoes')) ?>" data-guard-submit="true" class="institution-inline-form">
                                                    <?= App\Support\Csrf::field('admin_institucional_action') ?>
                                                    <input type="hidden" name="entity" value="perfil">
                                                    <input type="hidden" name="action" value="editar">
                                                    <input type="hidden" name="entity_id" value="<?= e((string) $row['id']) ?>">
                                                    <input type="hidden" name="aba" value="perfis">
                                                    <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                                                    <div class="field"><label>Nome do perfil</label><input type="text" name="nome_perfil" value="<?= e((string) $row['nome_perfil']) ?>" required></div>
                                                    <div class="field"><label>Descricao</label><input type="text" name="descricao" value="<?= e((string) ($row['descricao'] ?? '')) ?>"></div>
                                                    <div class="field">
                                                        <label>Status</label>
                                                        <select name="status_perfil">
                                                            <option value="ATIVO" <?= $statusAtual === 'ATIVO' ? 'selected' : '' ?>>ATIVO</option>
                                                            <option value="INATIVO" <?= $statusAtual === 'INATIVO' ? 'selected' : '' ?>>INATIVO</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit">Salvar edicao</button>
                                                </form>
                                            </details>
                                            <form method="post" action="<?= e(url('/admin/institucional/acoes')) ?>" data-guard-submit="true">
                                                <?= App\Support\Csrf::field('admin_institucional_action') ?>
                                                <input type="hidden" name="entity" value="perfil">
                                                <input type="hidden" name="action" value="status">
                                                <input type="hidden" name="entity_id" value="<?= e((string) $row['id']) ?>">
                                                <input type="hidden" name="novo_status" value="<?= e($novoStatus) ?>">
                                                <input type="hidden" name="aba" value="perfis">
                                                <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                                                <button type="submit" class="button button-secondary"><?= e($labelStatus) ?></button>
                                            </form>
                                            <button type="button" class="button institution-delete-btn" data-delete-open data-entity="perfil" data-entity-id="<?= e((string) $row['id']) ?>" data-entity-label="Perfil <?= e((string) $row['nome_perfil']) ?>" data-tab="perfis">Excluir</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ((int) ($perfisPage['pages'] ?? 1) > 1): ?>
                        <nav class="institution-pagination" aria-label="Paginacao de perfis">
                            <?php if ((bool) ($perfisPage['has_prev'] ?? false)): ?>
                                <a class="institution-pagination-link" href="<?= e(url('/admin/institucional?' . $buildTabQuery('perfis', $perfisFilter, ['page_perfis' => (int) ($perfisPage['prev_page'] ?? 1)]))) ?>">Anterior</a>
                            <?php else: ?>
                                <span class="institution-pagination-link is-disabled">Anterior</span>
                            <?php endif; ?>
                            <span class="institution-pagination-status">
                                Pagina <?= e((string) ($perfisPage['page'] ?? 1)) ?> de <?= e((string) ($perfisPage['pages'] ?? 1)) ?>
                                - Exibindo <?= e((string) ($perfisPage['from'] ?? 0)) ?>-<?= e((string) ($perfisPage['to'] ?? 0)) ?> de <?= e((string) ($perfisPage['total'] ?? 0)) ?>
                            </span>
                            <?php if ((bool) ($perfisPage['has_next'] ?? false)): ?>
                                <a class="institution-pagination-link" href="<?= e(url('/admin/institucional?' . $buildTabQuery('perfis', $perfisFilter, ['page_perfis' => (int) ($perfisPage['next_page'] ?? 1)]))) ?>">Proxima</a>
                            <?php else: ?>
                                <span class="institution-pagination-link is-disabled">Proxima</span>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </article>
            </div>
        </section>
        <section id="tab-panel-vinculos" class="institution-panel" data-tab-panel="vinculos" role="tabpanel">
            <div class="institution-panel-grid">
                <article class="landing-card institution-form-card">
                    <div class="institution-card-header">
                        <h3>Vincular usuario-perfil</h3>
                        <button type="button" class="button button-secondary institution-guide-trigger" data-profile-guide-open>Guia de perfis</button>
                    </div>
                    <form method="post" action="<?= e(url('/admin/institucional/vinculos')) ?>" data-guard-submit="true">
                        <?= App\Support\Csrf::field('admin_usuario_perfil_bind') ?>
                        <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                        <div class="field">
                            <label for="vinculo_usuario_id">Usuario</label>
                            <select id="vinculo_usuario_id" name="usuario_id" required>
                                <option value="">Selecione</option>
                                <?php foreach (($options['usuarios'] ?? []) as $usuario): ?>
                                    <option value="<?= e((string) $usuario['id']) ?>"><?= e((string) $usuario['nome_completo']) ?> (<?= e((string) $usuario['email_login']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="field">
                            <label for="vinculo_perfil_id">Perfil</label>
                            <select id="vinculo_perfil_id" name="perfil_id" required>
                                <option value="">Selecione</option>
                                <?php foreach (($options['perfis'] ?? []) as $perfil): ?>
                                    <option value="<?= e((string) $perfil['id']) ?>"><?= e((string) $perfil['nome_perfil']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit">Salvar vinculo</button>
                    </form>
                </article>

                <article class="landing-card institution-table-card">
                    <h3>Vinculos ativos</h3>
                    <form method="get" action="<?= e(url('/admin/institucional')) ?>" class="institution-list-filter-form">
                        <input type="hidden" name="aba" value="vinculos">
                        <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                        <div class="field">
                            <label for="f_vinculo_usuario">Usuario</label>
                            <input id="f_vinculo_usuario" name="f_vinculo_usuario" type="text" value="<?= e((string) ($vinculosFilter['usuario'] ?? '')) ?>" placeholder="Buscar por nome do usuario">
                        </div>
                        <div class="field">
                            <label for="f_vinculo_perfil">Perfil</label>
                            <input id="f_vinculo_perfil" name="f_vinculo_perfil" type="text" value="<?= e((string) ($vinculosFilter['perfil'] ?? '')) ?>" placeholder="Buscar por perfil">
                        </div>
                        <div class="field">
                            <label for="f_vinculo_status">Status</label>
                            <select id="f_vinculo_status" name="f_vinculo_status">
                                <option value="">Todos</option>
                                <option value="ATIVO" <?= ($vinculosFilter['status'] ?? '') === 'ATIVO' ? 'selected' : '' ?>>ATIVO</option>
                                <option value="INATIVO" <?= ($vinculosFilter['status'] ?? '') === 'INATIVO' ? 'selected' : '' ?>>INATIVO</option>
                            </select>
                        </div>
                        <div class="institution-list-filter-actions">
                            <button type="submit" class="button button-secondary">Filtrar</button>
                            <a class="button" href="<?= e(url('/admin/institucional?' . $buildTabQuery('vinculos', []))) ?>">Limpar</a>
                        </div>
                    </form>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Perfil</th>
                                <th>Status</th>
                                <th>Acoes</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if ($vinculos === []): ?>
                                <tr>
                                    <td colspan="5" class="center muted">Nenhum vinculo encontrado para o filtro aplicado.</td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ($vinculos as $row): ?>
                                <?php
                                    $statusAtual = (string) ($row['status_vinculo'] ?? 'ATIVO');
                                    $novoStatus = $statusAtual === 'ATIVO' ? 'INATIVO' : 'ATIVO';
                                    $labelStatus = $statusAtual === 'ATIVO' ? 'Inativar' : 'Ativar';
                                ?>
                                <tr>
                                    <td><?= e((string) $row['id']) ?></td>
                                    <td><?= e((string) $row['usuario_nome']) ?></td>
                                    <td><?= e((string) $row['nome_perfil']) ?></td>
                                    <td><?= e($statusAtual) ?></td>
                                    <td>
                                        <div class="institution-actions">
                                            <details>
                                                <summary>Ver detalhe</summary>
                                                <div class="institution-detail-box">
                                                    <p><strong>Criado em:</strong> <?= e((string) ($row['created_at'] ?? 'N/A')) ?></p>
                                                </div>
                                            </details>
                                            <details>
                                                <summary>Editar</summary>
                                                <form method="post" action="<?= e(url('/admin/institucional/acoes')) ?>" data-guard-submit="true" class="institution-inline-form">
                                                    <?= App\Support\Csrf::field('admin_institucional_action') ?>
                                                    <input type="hidden" name="entity" value="vinculo">
                                                    <input type="hidden" name="action" value="editar">
                                                    <input type="hidden" name="entity_id" value="<?= e((string) $row['id']) ?>">
                                                    <input type="hidden" name="aba" value="vinculos">
                                                    <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                                                    <div class="field">
                                                        <label>Perfil</label>
                                                        <select name="perfil_id" required>
                                                            <?php foreach (($options['perfis'] ?? []) as $perfil): ?>
                                                                <option value="<?= e((string) $perfil['id']) ?>" <?= (int) $perfil['id'] === (int) ($row['perfil_id'] ?? 0) ? 'selected' : '' ?>>
                                                                    <?= e((string) $perfil['nome_perfil']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="field">
                                                        <label>Status</label>
                                                        <select name="status_vinculo">
                                                            <option value="ATIVO" <?= $statusAtual === 'ATIVO' ? 'selected' : '' ?>>ATIVO</option>
                                                            <option value="INATIVO" <?= $statusAtual === 'INATIVO' ? 'selected' : '' ?>>INATIVO</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit">Salvar edicao</button>
                                                </form>
                                            </details>
                                            <form method="post" action="<?= e(url('/admin/institucional/acoes')) ?>" data-guard-submit="true">
                                                <?= App\Support\Csrf::field('admin_institucional_action') ?>
                                                <input type="hidden" name="entity" value="vinculo">
                                                <input type="hidden" name="action" value="status">
                                                <input type="hidden" name="entity_id" value="<?= e((string) $row['id']) ?>">
                                                <input type="hidden" name="novo_status" value="<?= e($novoStatus) ?>">
                                                <input type="hidden" name="aba" value="vinculos">
                                                <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
                                                <button type="submit" class="button button-secondary"><?= e($labelStatus) ?></button>
                                            </form>
                                            <button type="button" class="button institution-delete-btn" data-delete-open data-entity="vinculo" data-entity-id="<?= e((string) $row['id']) ?>" data-entity-label="Vinculo de <?= e((string) $row['usuario_nome']) ?>" data-tab="vinculos">Excluir</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ((int) ($vinculosPage['pages'] ?? 1) > 1): ?>
                        <nav class="institution-pagination" aria-label="Paginacao de vinculos">
                            <?php if ((bool) ($vinculosPage['has_prev'] ?? false)): ?>
                                <a class="institution-pagination-link" href="<?= e(url('/admin/institucional?' . $buildTabQuery('vinculos', $vinculosFilter, ['page_vinculos' => (int) ($vinculosPage['prev_page'] ?? 1)]))) ?>">Anterior</a>
                            <?php else: ?>
                                <span class="institution-pagination-link is-disabled">Anterior</span>
                            <?php endif; ?>
                            <span class="institution-pagination-status">
                                Pagina <?= e((string) ($vinculosPage['page'] ?? 1)) ?> de <?= e((string) ($vinculosPage['pages'] ?? 1)) ?>
                                - Exibindo <?= e((string) ($vinculosPage['from'] ?? 0)) ?>-<?= e((string) ($vinculosPage['to'] ?? 0)) ?> de <?= e((string) ($vinculosPage['total'] ?? 0)) ?>
                            </span>
                            <?php if ((bool) ($vinculosPage['has_next'] ?? false)): ?>
                                <a class="institution-pagination-link" href="<?= e(url('/admin/institucional?' . $buildTabQuery('vinculos', $vinculosFilter, ['page_vinculos' => (int) ($vinculosPage['next_page'] ?? 1)]))) ?>">Proxima</a>
                            <?php else: ?>
                                <span class="institution-pagination-link is-disabled">Proxima</span>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </article>
            </div>
        </section>
    </div>
</section>

<div class="institution-modal" data-delete-modal hidden>
    <div class="institution-modal-backdrop" data-delete-close></div>
    <div class="institution-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="delete-modal-title">
        <h3 id="delete-modal-title">Excluir item</h3>
        <p class="muted" data-delete-entity-label>Selecione o item para exclusao logica.</p>
        <form method="post" action="<?= e(url('/admin/institucional/acoes')) ?>" data-guard-submit="true" class="institution-delete-form">
            <?= App\Support\Csrf::field('admin_institucional_action') ?>
            <input type="hidden" name="entity" value="" data-delete-entity>
            <input type="hidden" name="action" value="excluir">
            <input type="hidden" name="entity_id" value="" data-delete-entity-id>
            <input type="hidden" name="aba" value="" data-delete-tab>
            <?php if ($currentUfFilter !== null): ?><input type="hidden" name="uf" value="<?= e((string) $currentUfFilter) ?>"><?php endif; ?>
            <div class="field">
                <label for="motivo_exclusao">Motivo da exclusao logica</label>
                <textarea id="motivo_exclusao" name="motivo_exclusao" rows="4" maxlength="255" required></textarea>
            </div>
            <div class="institution-modal-actions">
                <button type="button" class="button button-secondary" data-delete-close>Cancelar</button>
                <button type="submit" class="button institution-delete-btn">Confirmar exclusao</button>
            </div>
        </form>
    </div>
</div>

<div class="institution-modal institution-modal-guide" data-profile-guide-modal hidden>
    <div class="institution-modal-backdrop" data-profile-guide-close></div>
    <div class="institution-modal-dialog institution-modal-dialog-guide" role="dialog" aria-modal="true" aria-labelledby="profile-guide-title">
        <h3 id="profile-guide-title">Guia de perfis e responsabilidades</h3>
        <p class="muted">Use este resumo para identificar o melhor perfil antes de vincular usuarios. Perfis novos criados pelo ADMIN_MASTER aparecem automaticamente aqui.</p>

        <?php if ($profileGuide === []): ?>
            <p class="muted">Nenhum perfil disponivel para exibicao no momento.</p>
        <?php else: ?>
            <div class="profile-guide-grid">
                <?php foreach ($profileGuide as $guide): ?>
                    <?php
                        $statusPerfil = strtoupper((string) ($guide['status_perfil'] ?? ''));
                        $statusClass = $statusPerfil === 'ATIVO' ? 'is-active' : 'is-inactive';
                        $pode = is_array($guide['pode'] ?? null) ? $guide['pode'] : [];
                        $naoPode = is_array($guide['nao_pode'] ?? null) ? $guide['nao_pode'] : [];
                    ?>
                    <article class="profile-guide-card">
                        <header class="profile-guide-card-header">
                            <h4><?= e((string) ($guide['nome_perfil'] ?? 'PERFIL')) ?></h4>
                            <span class="profile-guide-status <?= e($statusClass) ?>"><?= e($statusPerfil !== '' ? $statusPerfil : 'N/A') ?></span>
                        </header>
                        <p><?= e((string) ($guide['descricao'] ?? 'Sem descricao cadastrada.')) ?></p>

                        <div class="profile-guide-columns">
                            <section>
                                <h5>Pode</h5>
                                <ul class="profile-guide-list">
                                    <?php foreach ($pode as $item): ?>
                                        <li><?= e((string) $item) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </section>
                            <section>
                                <h5>Nao pode</h5>
                                <ul class="profile-guide-list">
                                    <?php foreach ($naoPode as $item): ?>
                                        <li><?= e((string) $item) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </section>
                        </div>

                        <?php if (trim((string) ($guide['observacao'] ?? '')) !== ''): ?>
                            <p class="profile-guide-note"><?= e((string) $guide['observacao']) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="institution-modal-actions">
            <button type="button" class="button button-secondary" data-profile-guide-close>Fechar</button>
        </div>
    </div>
</div>
