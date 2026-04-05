<?php

declare(strict_types=1);

$title = $title ?? 'Area Administrativa';
$auth = $_SESSION['auth'] ?? [];
$flash = App\Support\Flash::all();
$appVersion = (string) config('app.version', '1.0.0');
$currentUri = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$basePath = app_base_path();
if (
    $basePath !== ''
    && ($currentUri === $basePath || str_starts_with($currentUri, $basePath . '/'))
) {
    $currentUri = substr($currentUri, strlen($basePath));
    $currentUri = $currentUri === '' ? '/' : $currentUri;
}

$isDashboard = $currentUri === '/admin';
$isInstitucional = str_starts_with($currentUri, '/admin/institucional');
$isComercial = str_starts_with($currentUri, '/admin/comercial');
$isEnterprise = str_starts_with($currentUri, '/admin/enterprise');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="icon" type="image/png" sizes="256x256" href="<?= e(url('/assets/img/favicon-sigerd.png')) ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= e(url('/assets/img/favicon-sigerd.png')) ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= e(url('/assets/img/favicon-sigerd.png')) ?>">
    <link rel="shortcut icon" type="image/png" href="<?= e(url('/assets/img/favicon-sigerd.png')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/shared/app.css')) ?>">
</head>
<body class="public-body">
<header class="public-header">
    <div class="container container-wide public-header-inner">
        <div class="public-brand-zone">
            <a class="public-brand" href="<?= e(url('/admin')) ?>" aria-label="Area administrativa SaaS - inicio">
                <img src="<?= e(url('/assets/img/logo-SIGERD-02.png')) ?>" alt="Logo do sistema" class="public-brand-logo">
                <span class="public-brand-text">
                    <strong>Area administrativa SaaS SIGERD</strong>
                    <small><?= e((string) ($auth['nome_completo'] ?? '')) ?> | UF <?= e((string) ($auth['uf_sigla'] ?? 'N/A')) ?></small>
                </span>
            </a>

            <button
                class="menu-toggle public-menu-toggle"
                type="button"
                aria-expanded="false"
                aria-controls="admin-main-nav"
                data-menu-toggle
                data-menu-target="admin-main-nav"
                aria-label="Abrir menu principal"
            >
                <span class="menu-toggle-lines" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
        </div>

        <nav id="admin-main-nav" class="public-nav" data-nav-track>
            <a class="<?= $isDashboard ? 'is-active' : '' ?>" href="<?= e(url('/admin')) ?>">Dashboard</a>
            <a class="<?= $isInstitucional ? 'is-active' : '' ?>" href="<?= e(url('/admin/institucional')) ?>">Institucional</a>
            <a class="<?= $isComercial ? 'is-active' : '' ?>" href="<?= e(url('/admin/comercial')) ?>">Comercial</a>
            <a class="<?= $isEnterprise ? 'is-active' : '' ?>" href="<?= e(url('/admin/enterprise')) ?>">Enterprise</a>
            <a href="<?= e(url('/')) ?>">Pagina publica</a>
        </nav>

        <div class="public-head-actions">
            <span class="app-version">versao <?= e($appVersion) ?></span>
            <form method="post" action="<?= e(url('/logout')) ?>" class="inline-form">
                <?= App\Support\Csrf::field('auth_logout') ?>
                <button type="submit" class="public-cta">Sair</button>
            </form>
        </div>
    </div>
</header>
<main class="public-main">
    <div class="container public-flash-stack">
        <?php if (isset($flash['success'])): ?>
            <div class="alert alert-success"><?= e((string) $flash['success']) ?></div>
        <?php endif; ?>
        <?php if (isset($flash['error'])): ?>
            <div class="alert alert-error"><?= e((string) $flash['error']) ?></div>
        <?php endif; ?>
        <?php if (isset($flash['warning'])): ?>
            <div class="alert alert-warning"><?= e((string) $flash['warning']) ?></div>
        <?php endif; ?>
    </div>
    <div class="container">
        <?= $content ?? '' ?>
    </div>
</main>
<footer class="public-footer">
    <div class="container container-wide public-footer-inner">
        <div class="public-footer-brand">
            <img src="<?= e(url('/assets/img/logo-SIGERD-02.png')) ?>" alt="Logo do sistema" class="public-footer-logo">
            <div class="public-footer-copy">
                <strong>Sistema Integrado de Gerenciamento de Riscos e Desastres</strong>
                <p>Area administrativa SaaS para governanca institucional, comercial e enterprise.</p>
            </div>
        </div>

        <div class="public-footer-links">
            <a href="<?= e(url('/admin')) ?>">Dashboard</a>
            <a href="<?= e(url('/admin/institucional')) ?>">Institucional</a>
            <a href="<?= e(url('/admin/comercial')) ?>">Comercial</a>
            <a href="<?= e(url('/admin/enterprise')) ?>">Enterprise</a>
        </div>

        <div class="public-footer-meta">
            <span>SIGERD administrativo</span>
            <span>versao <?= e($appVersion) ?></span>
            <span><?= e(date('Y')) ?>. Todos os direitos reservados.</span>
        </div>
    </div>
</footer>
<button type="button" class="scroll-top-btn" data-scroll-top aria-label="Voltar ao topo">
    <span aria-hidden="true">&#8593;</span>
</button>
<script src="<?= e(url('/assets/js/shared/form-guard.js')) ?>" defer></script>
<script src="<?= e(url('/assets/js/shared/uf-dynamic.js')) ?>" defer></script>
<script src="<?= e(url('/assets/js/shared/municipio-autocomplete.js')) ?>" defer></script>
<script src="<?= e(url('/assets/js/shared/ui-enhancements.js')) ?>" defer></script>
</body>
</html>
