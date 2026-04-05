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
                <strong><?= e((string) count($accounts)) ?> contas</strong>
                <span>Contratantes ativas no ambiente</span>
            </article>
            <article>
                <strong><?= e((string) count($usuarios)) ?> usuarios</strong>
                <span>Acessos institucionais em operacao</span>
            </article>
            <article>
                <strong><?= e((string) count($vinculos)) ?> vinculos</strong>
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
                </article>
            </div>
        </section>
        <section id="tab-panel-usuarios" class="institution-panel" data-tab-panel="usuarios" role="tabpanel">
            <div class="institution-panel-grid">
                <article class="landing-card institution-form-card">
                    <h3>Novo usuario</h3>
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
                </article>
            </div>
        </section>
        <section id="tab-panel-vinculos" class="institution-panel" data-tab-panel="vinculos" role="tabpanel">
            <div class="institution-panel-grid">
                <article class="landing-card institution-form-card">
                    <h3>Vincular usuario-perfil</h3>
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

<script>
(() => {
    const shell = document.querySelector('[data-institution-tabs]');
    if (shell) {
        const triggers = Array.from(shell.querySelectorAll('[data-tab-trigger]'));
        const panels = Array.from(shell.querySelectorAll('[data-tab-panel]'));
        const setActive = (tab) => {
            triggers.forEach((trigger) => {
                const selected = trigger.getAttribute('data-tab-trigger') === tab;
                trigger.classList.toggle('is-active', selected);
                trigger.setAttribute('aria-selected', selected ? 'true' : 'false');
            });
            panels.forEach((panel) => {
                panel.classList.toggle('is-active', panel.getAttribute('data-tab-panel') === tab);
            });
        };

        const initialTab = shell.getAttribute('data-active-tab') || 'contas';
        setActive(initialTab);

        triggers.forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const tab = trigger.getAttribute('data-tab-trigger') || 'contas';
                setActive(tab);
            });
        });
    }

    const modal = document.querySelector('[data-delete-modal]');
    if (modal) {
        const inputEntity = modal.querySelector('[data-delete-entity]');
        const inputEntityId = modal.querySelector('[data-delete-entity-id]');
        const inputTab = modal.querySelector('[data-delete-tab]');
        const label = modal.querySelector('[data-delete-entity-label]');

        const closeModal = () => {
            modal.hidden = true;
            modal.classList.remove('is-open');
        };

        document.querySelectorAll('[data-delete-open]').forEach((button) => {
            button.addEventListener('click', () => {
                inputEntity.value = button.getAttribute('data-entity') || '';
                inputEntityId.value = button.getAttribute('data-entity-id') || '';
                inputTab.value = button.getAttribute('data-tab') || 'contas';
                label.textContent = button.getAttribute('data-entity-label') || 'Item selecionado';
                modal.hidden = false;
                modal.classList.add('is-open');
            });
        });

        modal.querySelectorAll('[data-delete-close]').forEach((button) => {
            button.addEventListener('click', closeModal);
        });
    }

    const profileGuideModal = document.querySelector('[data-profile-guide-modal]');
    if (profileGuideModal) {
        const closeGuideModal = () => {
            profileGuideModal.hidden = true;
            profileGuideModal.classList.remove('is-open');
        };

        document.querySelectorAll('[data-profile-guide-open]').forEach((button) => {
            button.addEventListener('click', () => {
                profileGuideModal.hidden = false;
                profileGuideModal.classList.add('is-open');
            });
        });

        profileGuideModal.querySelectorAll('[data-profile-guide-close]').forEach((button) => {
            button.addEventListener('click', closeGuideModal);
        });
    }
})();
</script>
