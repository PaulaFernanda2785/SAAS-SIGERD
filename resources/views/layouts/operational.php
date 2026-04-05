<?php

declare(strict_types=1);

$title = $title ?? 'Area Operacional';
$auth = $_SESSION['auth'] ?? [];
$scopeList = is_array($auth['escopos'] ?? null) ? $auth['escopos'] : [];
$scopeLabel = $scopeList !== [] ? implode(', ', $scopeList) : 'PROPRIO_ORGAO';
$currentUri = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$flash = App\Support\Flash::all();
$appVersion = (string) config('app.version', '1.0.0');
$basePath = app_base_path();
if (
    $basePath !== ''
    && ($currentUri === $basePath || str_starts_with($currentUri, $basePath . '/'))
) {
    $currentUri = substr($currentUri, strlen($basePath));
    $currentUri = $currentUri === '' ? '/' : $currentUri;
}
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
<body class="public-body app-shell-body">
<div class="app-shell">
    <aside id="operational-main-nav" class="app-sidebar" data-nav-track>
        <div class="app-sidebar-top">
            <a class="app-sidebar-brand" href="<?= e(url('/operational')) ?>" aria-label="Area operacional">
                <img src="<?= e(url('/assets/img/logo-SIGERD-02.png')) ?>" alt="Logo do sistema" class="app-sidebar-logo">
                <span>
                    <strong>Area Operacional</strong>
                    <small>SIGERD</small>
                </span>
            </a>
            <div class="app-sidebar-context">
                <span class="tag">OPERACAO</span>
                <p><?= e((string) ($auth['nome_completo'] ?? '')) ?></p>
                <p>Escopo <?= e($scopeLabel) ?></p>
            </div>
        </div>

        <nav class="app-sidebar-nav">
            <a class="<?= $currentUri === '/operational' ? 'is-active' : '' ?>" href="<?= e(url('/operational')) ?>">Dashboard</a>
            <a class="<?= str_starts_with($currentUri, '/operational/incidentes') ? 'is-active' : '' ?>" href="<?= e(url('/operational/incidentes')) ?>">Incidentes</a>
            <a class="<?= str_starts_with($currentUri, '/operational/plancon') ? 'is-active' : '' ?>" href="<?= e(url('/operational/plancon')) ?>">PLANCON</a>
            <a class="<?= str_starts_with($currentUri, '/operational/desastres') ? 'is-active' : '' ?>" href="<?= e(url('/operational/desastres')) ?>">Desastres</a>
            <a class="<?= str_starts_with($currentUri, '/operational/inteligencia') ? 'is-active' : '' ?>" href="<?= e(url('/operational/inteligencia')) ?>">Inteligencia</a>
            <a class="<?= str_starts_with($currentUri, '/operational/documentos') ? 'is-active' : '' ?>" href="<?= e(url('/operational/documentos')) ?>">Documentos</a>
            <a class="<?= str_starts_with($currentUri, '/operational/governanca') ? 'is-active' : '' ?>" href="<?= e(url('/operational/governanca')) ?>">Governanca</a>
            <a class="<?= str_starts_with($currentUri, '/operational/relatorios') ? 'is-active' : '' ?>" href="<?= e(url('/operational/relatorios/avancado')) ?>">Relatorios</a>
            <a href="<?= e(url('/')) ?>">Pagina publica</a>
        </nav>

        <div class="app-sidebar-bottom">
            <span class="app-shell-version">versao <?= e($appVersion) ?></span>
            <form method="post" action="<?= e(url('/logout')) ?>" class="inline-form">
                <?= App\Support\Csrf::field('auth_logout') ?>
                <button type="submit" class="app-sidebar-logout">Sair</button>
            </form>
        </div>
    </aside>

    <div class="app-shell-content">
        <header class="app-shell-header">
            <div class="app-shell-header-main">
                <button
                    class="menu-toggle app-shell-toggle"
                    type="button"
                    aria-expanded="false"
                    aria-controls="operational-main-nav"
                    data-menu-toggle
                    data-menu-target="operational-main-nav"
                    aria-label="Abrir menu principal"
                >
                    <span class="menu-toggle-lines" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
                <div class="app-shell-title">
                    <strong>Centro Operacional</strong>
                    <span>Incidentes, inteligencia, governanca e relatorios</span>
                </div>
            </div>
            <span class="app-shell-version app-shell-version--header">versao <?= e($appVersion) ?></span>
        </header>

        <main class="app-shell-main">
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

        <footer class="app-shell-footer">
            <span>SIGERD operacional</span>
            <span><?= e(date('Y')) ?>. Todos os direitos reservados.</span>
        </footer>
    </div>
</div>
<button type="button" class="scroll-top-btn" data-scroll-top aria-label="Voltar ao topo">
    <span aria-hidden="true">&#8593;</span>
</button>
<script src="<?= e(url('/assets/js/shared/form-guard.js')) ?>" defer></script>
<script src="<?= e(url('/assets/js/shared/uf-dynamic.js')) ?>" defer></script>
<script src="<?= e(url('/assets/js/shared/municipio-autocomplete.js')) ?>" defer></script>
<script src="<?= e(url('/assets/js/shared/ui-enhancements.js')) ?>" defer></script>
</body>
</html>
