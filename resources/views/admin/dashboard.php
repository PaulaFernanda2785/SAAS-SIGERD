<?php

declare(strict_types=1);
?>
<section class="landing-hero">
    <div class="landing-hero-inner reveal-on-scroll">
        <span class="landing-badge">Area administrativa SaaS</span>
        <h1>Painel Administrativo SaaS</h1>
        <p>
            Centro de comando da Fase 1 para gestao institucional, comercial e status contratual,
            com visao consolidada do ambiente e acessos operacionais.
        </p>
        <div class="landing-actions">
            <a class="button" href="<?= e(url('/admin/institucional')) ?>">Gestao institucional</a>
            <a class="button button-secondary" href="<?= e(url('/admin/comercial')) ?>">Gestao comercial</a>
            <a class="button button-secondary" href="<?= e(url('/admin/enterprise')) ?>">Recursos enterprise</a>
        </div>
        <div class="landing-metrics">
            <article>
                <strong><?= e((string) ($summary['contas'] ?? 0)) ?> contas</strong>
                <span>Contas contratantes cadastradas</span>
            </article>
            <article>
                <strong><?= e((string) ($summary['usuarios'] ?? 0)) ?> usuarios</strong>
                <span>Usuarios ativos no ecossistema administrativo</span>
            </article>
            <article>
                <strong><?= e((string) ($summary['assinaturas_ativas'] ?? 0)) ?> assinaturas ativas</strong>
                <span>Status contratual em operacao</span>
            </article>
        </div>
    </div>
</section>

<section class="landing-section">
    <header class="landing-section-header">
        <span>Visao geral</span>
        <h2>Resumo administrativo e de acesso</h2>
    </header>
    <div class="landing-grid-3">
        <article class="landing-card">
            <h3>Usuario autenticado</h3>
            <p><strong>Nome:</strong> <?= e((string) ($auth['nome_completo'] ?? '')) ?></p>
            <p><strong>Email:</strong> <?= e((string) ($auth['email_login'] ?? '')) ?></p>
            <p><strong>Perfil:</strong> <?= e((string) ($auth['perfil_primario'] ?? '')) ?></p>
            <p><strong>UF de contexto:</strong> <?= e((string) ($auth['uf_sigla'] ?? 'N/A')) ?></p>
            <p><strong>Status assinatura:</strong> <?= e((string) ($auth['status_assinatura'] ?? 'N/A')) ?></p>
        </article>
        <article class="landing-card">
            <h3>Estrutura institucional</h3>
            <p><strong>Contas:</strong> <?= e((string) ($summary['contas'] ?? 0)) ?></p>
            <p><strong>Orgaos:</strong> <?= e((string) ($summary['orgaos'] ?? 0)) ?></p>
            <p><strong>Unidades:</strong> <?= e((string) ($summary['unidades'] ?? 0)) ?></p>
            <p><strong>Usuarios:</strong> <?= e((string) ($summary['usuarios'] ?? 0)) ?></p>
            <p><strong>Perfis:</strong> <?= e((string) ($summary['perfis'] ?? 0)) ?></p>
        </article>
        <article class="landing-card">
            <h3>Comercial e contratos</h3>
            <p><strong>Planos:</strong> <?= e((string) ($summary['planos'] ?? 0)) ?></p>
            <p><strong>Assinaturas ativas:</strong> <?= e((string) ($summary['assinaturas_ativas'] ?? 0)) ?></p>
            <p><strong>Acesso enterprise:</strong> <?= e((string) ($auth['assinatura_enterprise'] ?? 'N/A')) ?></p>
        </article>
    </div>
</section>

<section class="landing-section">
    <header class="landing-section-header">
        <span>Atalhos</span>
        <h2>Entradas rapidas para operacao do SaaS</h2>
    </header>
    <div class="landing-grid-2">
        <article class="landing-feature">
            <h3>Institucional</h3>
            <p>Cadastro e manutencao de contas, orgaos, unidades, usuarios e perfis.</p>
            <ul>
                <li><a href="<?= e(url('/admin/institucional')) ?>">Ir para modulo institucional</a></li>
            </ul>
        </article>
        <article class="landing-feature">
            <h3>Comercial</h3>
            <p>Gestao de catalogo de planos, assinaturas, modulos e regras de contratacao.</p>
            <ul>
                <li><a href="<?= e(url('/admin/comercial')) ?>">Ir para modulo comercial</a></li>
            </ul>
        </article>
        <article class="landing-feature">
            <h3>Enterprise</h3>
            <p>Configuracao de features avancadas, apps de API, integracoes e automacoes.</p>
            <ul>
                <li><a href="<?= e(url('/admin/enterprise')) ?>">Ir para modulo enterprise</a></li>
            </ul>
        </article>
        <article class="landing-feature">
            <h3>Experiencia publica</h3>
            <p>Validacao da pagina de planos visivel para contas interessadas no servico.</p>
            <ul>
                <li><a href="<?= e(url('/planos')) ?>">Abrir pagina publica de planos</a></li>
            </ul>
        </article>
    </div>
</section>

<section class="landing-cta-strip">
    <h2>Base administrativa pronta para escalar com seguranca</h2>
    <p>Use os modulos institucional, comercial e enterprise para manter operacao, governanca e contratos alinhados.</p>
    <a class="button" href="<?= e(url('/admin/comercial')) ?>">Continuar na administracao SaaS</a>
</section>
