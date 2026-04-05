<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Domain\Enum\BrazilUf;
use App\Domain\Enum\UserProfile;
use App\Repositories\SaaS\InstitutionRepository;
use App\Repositories\Territory\TerritoryRepository;
use App\Services\Audit\AuditService;
use App\Support\Flash;
use App\Support\Request;
use App\Support\Response;
use RuntimeException;
use Throwable;

final class InstitutionController
{
    private const TABS = ['contas', 'orgaos', 'unidades', 'usuarios', 'perfis', 'vinculos'];

    public function __construct(
        private readonly ?InstitutionRepository $institutionRepository = null,
        private readonly ?TerritoryRepository $territoryRepository = null,
        private readonly ?AuditService $auditService = null
    ) {
    }

    public function index(Request $request): Response
    {
        $repository = $this->repository();
        $auth = $_SESSION['auth'] ?? [];
        $isAdminMaster = $this->isAdminMaster($auth);
        $currentUf = $this->resolveUfFilter($request, $auth);
        $activeTab = $this->resolveTab((string) $request->input('aba', 'contas'));
        $profiles = $repository->perfis();

        return Response::view('admin/institutions', [
            'title' => 'Gestao Institucional',
            'auth' => $auth,
            'accounts' => $repository->accounts($currentUf),
            'orgaos' => $repository->orgaos($currentUf),
            'unidades' => $repository->unidades($currentUf),
            'usuarios' => $repository->usuarios($currentUf),
            'perfis' => $profiles,
            'profileGuide' => $this->buildProfileGuide($profiles),
            'vinculos' => $repository->vinculosUsuarioPerfil($currentUf),
            'options' => $repository->contextOptions($currentUf),
            'currentUfFilter' => $currentUf,
            'canSelectAllUf' => $isAdminMaster,
            'activeTab' => $activeTab,
        ], 'admin');
    }

    public function storeAccount(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $redirectUf = $this->resolveRedirectUf($request, $auth);
        $nomeFantasia = trim((string) $request->input('nome_fantasia', ''));
        if ($nomeFantasia === '') {
            Flash::set('error', 'Informe o nome fantasia da conta.');
            return $this->redirectWithTab('contas', $redirectUf);
        }

        $ufSigla = BrazilUf::normalize($request->input('uf_sigla'));
        if (!$this->isAdminMaster($auth)) {
            $ufSigla = BrazilUf::normalize($auth['uf_sigla'] ?? null);
        }

        if ($ufSigla === null) {
            Flash::set('error', 'Selecione o UF de origem da conta.');
            return $this->redirectWithTab('contas', $redirectUf);
        }

        if (!$this->canOperateUf($auth, $ufSigla)) {
            Flash::set('error', 'Seu perfil administrativo nao pode operar fora do UF de contexto.');
            return $this->redirectWithTab('contas', $redirectUf);
        }

        if (!(($this->territoryRepository ?? new TerritoryRepository())->ufExists($ufSigla))) {
            Flash::set('error', 'UF de origem invalido. Atualize a base territorial primeiro.');
            return $this->redirectWithTab('contas', $redirectUf);
        }

        try {
            $id = ($this->institutionRepository ?? new InstitutionRepository())->createAccount([
                'nome_fantasia' => $nomeFantasia,
                'razao_social' => $this->nullableText($request->input('razao_social')),
                'cpf_cnpj' => $this->nullableText($request->input('cpf_cnpj')),
                'uf_sigla' => $ufSigla,
                'email_principal' => $this->nullableText($request->input('email_principal')),
                'status_cadastral' => $this->sanitizeEnum((string) $request->input('status_cadastral', 'ATIVA'), ['ATIVA', 'INATIVA', 'BLOQUEADA'], 'ATIVA'),
            ]);

            $this->audit('CONTAS', 'CONTA_CREATE', 'contas', $id, ['nome_fantasia' => $nomeFantasia, 'uf_sigla' => $ufSigla], $request);
            Flash::set('success', 'Conta cadastrada com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar conta. Verifique dados duplicados.');
        }

        return $this->redirectWithTab('contas', $redirectUf);
    }

    public function storeOrgao(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $redirectUf = $this->resolveRedirectUf($request, $auth);
        $contaId = (int) $request->input('conta_id', 0);
        $nomeOficial = trim((string) $request->input('nome_oficial', ''));
        if ($contaId < 1 || $nomeOficial === '') {
            Flash::set('error', 'Informe conta e nome do orgao.');
            return $this->redirectWithTab('orgaos', $redirectUf);
        }

        $conta = ($this->institutionRepository ?? new InstitutionRepository())->accountById($contaId);
        if ($conta === null) {
            Flash::set('error', 'Conta selecionada nao encontrada.');
            return $this->redirectWithTab('orgaos', $redirectUf);
        }

        $ufSigla = BrazilUf::normalize($conta['uf_sigla'] ?? null);
        if ($ufSigla === null) {
            Flash::set('error', 'Conta selecionada sem UF de origem. Atualize o cadastro da conta.');
            return $this->redirectWithTab('orgaos', $redirectUf);
        }

        if (!$this->canOperateUf($auth, $ufSigla)) {
            Flash::set('error', 'Seu perfil administrativo nao pode operar fora do UF de contexto.');
            return $this->redirectWithTab('orgaos', $redirectUf);
        }

        try {
            $id = ($this->institutionRepository ?? new InstitutionRepository())->createOrgao([
                'conta_id' => $contaId,
                'nome_oficial' => $nomeOficial,
                'sigla' => $this->nullableText($request->input('sigla')),
                'cnpj' => $this->nullableText($request->input('cnpj')),
                'uf_sigla' => $ufSigla,
                'status_orgao' => $this->sanitizeEnum((string) $request->input('status_orgao', 'ATIVO'), ['ATIVO', 'INATIVO', 'BLOQUEADO'], 'ATIVO'),
            ]);

            $this->audit('ORGAOS', 'ORGAO_CREATE', 'orgaos', $id, ['conta_id' => $contaId, 'uf_sigla' => $ufSigla], $request);
            Flash::set('success', 'Orgao cadastrado com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar orgao.');
        }

        return $this->redirectWithTab('orgaos', $redirectUf);
    }

    public function storeUnidade(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $redirectUf = $this->resolveRedirectUf($request, $auth);
        $orgaoId = (int) $request->input('orgao_id', 0);
        $nomeUnidade = trim((string) $request->input('nome_unidade', ''));
        if ($orgaoId < 1 || $nomeUnidade === '') {
            Flash::set('error', 'Informe orgao e nome da unidade.');
            return $this->redirectWithTab('unidades', $redirectUf);
        }

        $orgao = ($this->institutionRepository ?? new InstitutionRepository())->orgaoById($orgaoId);
        if ($orgao === null) {
            Flash::set('error', 'Orgao selecionado nao encontrado.');
            return $this->redirectWithTab('unidades', $redirectUf);
        }

        $ufSigla = BrazilUf::normalize($orgao['uf_sigla'] ?? null);
        if ($ufSigla === null) {
            Flash::set('error', 'Orgao selecionado sem UF de origem. Ajuste o cadastro do orgao.');
            return $this->redirectWithTab('unidades', $redirectUf);
        }

        if (!$this->canOperateUf($auth, $ufSigla)) {
            Flash::set('error', 'Seu perfil administrativo nao pode operar fora do UF de contexto.');
            return $this->redirectWithTab('unidades', $redirectUf);
        }

        try {
            $id = ($this->institutionRepository ?? new InstitutionRepository())->createUnidade([
                'orgao_id' => $orgaoId,
                'unidade_superior_id' => $this->nullableInt($request->input('unidade_superior_id')),
                'codigo_unidade' => $this->nullableText($request->input('codigo_unidade')),
                'nome_unidade' => $nomeUnidade,
                'tipo_unidade' => $this->nullableText($request->input('tipo_unidade')),
                'uf_sigla' => $ufSigla,
                'status_unidade' => $this->sanitizeEnum((string) $request->input('status_unidade', 'ATIVA'), ['ATIVA', 'INATIVA'], 'ATIVA'),
            ]);

            $this->audit('UNIDADES', 'UNIDADE_CREATE', 'unidades', $id, ['orgao_id' => $orgaoId, 'uf_sigla' => $ufSigla], $request);
            Flash::set('success', 'Unidade cadastrada com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar unidade.');
        }

        return $this->redirectWithTab('unidades', $redirectUf);
    }

    public function storeUsuario(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $redirectUf = $this->resolveRedirectUf($request, $auth);
        $contaId = (int) $request->input('conta_id', 0);
        $orgaoId = (int) $request->input('orgao_id', 0);
        $nomeCompleto = trim((string) $request->input('nome_completo', ''));
        $emailLogin = trim(strtolower((string) $request->input('email_login', '')));
        $password = (string) $request->input('password', '');
        $minLength = (int) config('auth.password_min_length', 8);

        if ($contaId < 1 || $orgaoId < 1 || $nomeCompleto === '' || $emailLogin === '' || $password === '') {
            Flash::set('error', 'Preencha conta, orgao, nome, login e senha do usuario.');
            return $this->redirectWithTab('usuarios', $redirectUf);
        }

        if (strlen($password) < $minLength) {
            Flash::set('error', "A senha deve ter no minimo {$minLength} caracteres.");
            return $this->redirectWithTab('usuarios', $redirectUf);
        }

        $repository = $this->repository();
        $conta = $repository->accountById($contaId);
        $orgao = $repository->orgaoById($orgaoId);
        if ($conta === null || $orgao === null) {
            Flash::set('error', 'Conta ou orgao informado nao encontrado.');
            return $this->redirectWithTab('usuarios', $redirectUf);
        }
        if ((int) ($orgao['conta_id'] ?? 0) !== $contaId) {
            Flash::set('error', 'Orgao nao pertence a conta selecionada.');
            return $this->redirectWithTab('usuarios', $redirectUf);
        }

        $ufConta = BrazilUf::normalize($conta['uf_sigla'] ?? null);
        $ufOrgao = BrazilUf::normalize($orgao['uf_sigla'] ?? null);
        if ($ufConta === null || $ufOrgao === null || $ufConta !== $ufOrgao) {
            Flash::set('error', 'Conta e orgao precisam estar alinhados ao mesmo UF de origem.');
            return $this->redirectWithTab('usuarios', $redirectUf);
        }
        if (!$this->canOperateUf($auth, $ufOrgao)) {
            Flash::set('error', 'Seu perfil administrativo nao pode operar fora do UF de contexto.');
            return $this->redirectWithTab('usuarios', $redirectUf);
        }

        $unidadeId = $this->nullableInt($request->input('unidade_id'));
        if ($unidadeId !== null) {
            $unidade = $repository->unidadeById($unidadeId);
            if ($unidade === null || (int) ($unidade['orgao_id'] ?? 0) !== $orgaoId) {
                Flash::set('error', 'A unidade selecionada nao pertence ao orgao informado.');
                return $this->redirectWithTab('usuarios', $redirectUf);
            }
        }

        $perfilId = (int) $request->input('perfil_id', 0);
        if ($perfilId > 0) {
            $perfil = $repository->perfilById($perfilId);
            if ($perfil === null || ($perfil['excluido_em'] ?? null) !== null || (string) ($perfil['status_perfil'] ?? '') !== 'ATIVO') {
                Flash::set('error', 'Selecione um perfil ativo para vinculo inicial do usuario.');
                return $this->redirectWithTab('usuarios', $redirectUf);
            }
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if (!is_string($passwordHash) || $passwordHash === '') {
            Flash::set('error', 'Falha ao preparar credencial do usuario. Tente novamente.');
            return $this->redirectWithTab('usuarios', $redirectUf);
        }

        try {
            $id = $repository->createUsuario([
                'conta_id' => $contaId,
                'orgao_id' => $orgaoId,
                'unidade_id' => $unidadeId,
                'uf_sigla' => $ufOrgao,
                'nome_completo' => $nomeCompleto,
                'email_login' => $emailLogin,
                'matricula_funcional' => $this->nullableText($request->input('matricula_funcional')),
                'password_hash' => $passwordHash,
                'status_usuario' => $this->sanitizeEnum((string) $request->input('status_usuario', 'ATIVO'), ['ATIVO', 'INATIVO', 'BLOQUEADO'], 'ATIVO'),
            ]);

            if ($perfilId > 0) {
                $repository->vincularPerfilAoUsuario($id, $perfilId);
            }

            $this->audit(
                'USUARIOS',
                'USUARIO_CREATE',
                'usuarios',
                $id,
                ['conta_id' => $contaId, 'orgao_id' => $orgaoId, 'uf_sigla' => $ufOrgao],
                $request
            );
            Flash::set('success', 'Usuario cadastrado com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar usuario. Verifique login/matricula duplicados.');
        }

        return $this->redirectWithTab('usuarios', $redirectUf);
    }

    public function storePerfil(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $redirectUf = $this->resolveRedirectUf($request, $auth);
        $nomePerfil = strtoupper(trim((string) $request->input('nome_perfil', '')));
        if ($nomePerfil === '') {
            Flash::set('error', 'Informe o nome do perfil.');
            return $this->redirectWithTab('perfis', $redirectUf);
        }

        try {
            $id = ($this->institutionRepository ?? new InstitutionRepository())->createPerfil([
                'nome_perfil' => $nomePerfil,
                'descricao' => $this->nullableText($request->input('descricao')),
                'status_perfil' => $this->sanitizeEnum((string) $request->input('status_perfil', 'ATIVO'), ['ATIVO', 'INATIVO'], 'ATIVO'),
            ]);

            $this->audit('PERFIS', 'PERFIL_CREATE', 'perfis', $id, ['nome_perfil' => $nomePerfil], $request);
            Flash::set('success', 'Perfil cadastrado com sucesso.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao cadastrar perfil.');
        }

        return $this->redirectWithTab('perfis', $redirectUf);
    }

    public function attachPerfil(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $redirectUf = $this->resolveRedirectUf($request, $auth);
        $usuarioId = (int) $request->input('usuario_id', 0);
        $perfilId = (int) $request->input('perfil_id', 0);

        if ($usuarioId < 1 || $perfilId < 1) {
            Flash::set('error', 'Informe usuario e perfil para vinculo.');
            return $this->redirectWithTab('vinculos', $redirectUf);
        }

        $repository = $this->repository();
        $usuario = $repository->usuarioById($usuarioId);
        if ($usuario === null) {
            Flash::set('error', 'Usuario selecionado nao encontrado.');
            return $this->redirectWithTab('vinculos', $redirectUf);
        }
        if (!$this->canOperateUf($auth, BrazilUf::normalize($usuario['uf_sigla'] ?? null))) {
            Flash::set('error', 'Seu perfil administrativo nao pode vincular perfis fora do UF de contexto.');
            return $this->redirectWithTab('vinculos', $redirectUf);
        }

        $perfil = $repository->perfilById($perfilId);
        if ($perfil === null || ($perfil['excluido_em'] ?? null) !== null || (string) ($perfil['status_perfil'] ?? '') !== 'ATIVO') {
            Flash::set('error', 'Selecione um perfil ativo para vincular ao usuario.');
            return $this->redirectWithTab('vinculos', $redirectUf);
        }

        try {
            $repository->vincularPerfilAoUsuario($usuarioId, $perfilId);
            $this->audit('PERFIS', 'USUARIO_PERFIL_BIND', 'usuarios_perfis', null, ['usuario_id' => $usuarioId, 'perfil_id' => $perfilId], $request);
            Flash::set('success', 'Vinculo usuario-perfil atualizado.');
        } catch (Throwable) {
            Flash::set('error', 'Falha ao vincular usuario e perfil.');
        }

        return $this->redirectWithTab('vinculos', $redirectUf);
    }

    public function entityAction(Request $request): Response
    {
        $auth = $_SESSION['auth'] ?? [];
        $redirectUf = $this->resolveRedirectUf($request, $auth);
        $tab = $this->resolveTab((string) $request->input('aba', 'contas'));
        $entity = strtolower(trim((string) $request->input('entity', '')));
        $action = strtolower(trim((string) $request->input('action', '')));
        $entityId = (int) $request->input('entity_id', 0);

        if ($entity === '' || $action === '' || $entityId < 1) {
            Flash::set('error', 'Solicitacao invalida para acao institucional.');
            return $this->redirectWithTab($tab, $redirectUf);
        }

        $repository = $this->repository();

        try {
            switch ($entity) {
                case 'conta':
                case 'contas':
                    $this->handleContaAction($action, $entityId, $request, $auth, $repository);
                    break;

                case 'orgao':
                case 'orgaos':
                    $this->handleOrgaoAction($action, $entityId, $request, $auth, $repository);
                    break;

                case 'unidade':
                case 'unidades':
                    $this->handleUnidadeAction($action, $entityId, $request, $auth, $repository);
                    break;

                case 'usuario':
                case 'usuarios':
                    $this->handleUsuarioAction($action, $entityId, $request, $auth, $repository);
                    break;

                case 'perfil':
                case 'perfis':
                    $this->handlePerfilAction($action, $entityId, $request, $repository);
                    break;

                case 'vinculo':
                case 'vinculos':
                    $this->handleVinculoAction($action, $entityId, $request, $auth, $repository);
                    break;

                default:
                    throw new RuntimeException('Entidade invalida para acao institucional.');
            }
        } catch (RuntimeException $exception) {
            Flash::set('error', $exception->getMessage());
        } catch (Throwable) {
            Flash::set('error', 'Nao foi possivel concluir a acao solicitada.');
        }

        return $this->redirectWithTab($tab, $redirectUf);
    }

    private function handleContaAction(string $action, int $entityId, Request $request, array $auth, InstitutionRepository $repository): void
    {
        $conta = $repository->accountById($entityId);
        if ($conta === null) {
            throw new RuntimeException('Conta nao encontrada.');
        }

        $ufSigla = BrazilUf::normalize($conta['uf_sigla'] ?? null);
        if (!$this->canOperateUf($auth, $ufSigla)) {
            throw new RuntimeException('Seu perfil administrativo nao pode operar fora do UF de contexto.');
        }

        if ($action === 'editar') {
            $nomeFantasia = trim((string) $request->input('nome_fantasia', ''));
            if ($nomeFantasia === '') {
                throw new RuntimeException('Informe o nome fantasia da conta para editar.');
            }

            $repository->updateAccount($entityId, [
                'nome_fantasia' => $nomeFantasia,
                'razao_social' => $this->nullableText($request->input('razao_social')),
                'cpf_cnpj' => $this->nullableText($request->input('cpf_cnpj')),
                'email_principal' => $this->nullableText($request->input('email_principal')),
                'status_cadastral' => $this->sanitizeEnum((string) $request->input('status_cadastral', 'ATIVA'), ['ATIVA', 'INATIVA', 'BLOQUEADA'], 'ATIVA'),
            ]);

            $this->audit('CONTAS', 'CONTA_UPDATE', 'contas', $entityId, ['nome_fantasia' => $nomeFantasia], $request);
            Flash::set('success', 'Conta atualizada com sucesso.');
            return;
        }

        if ($action === 'status') {
            $novoStatus = $this->sanitizeEnum(
                (string) $request->input('novo_status', ''),
                ['ATIVA', 'INATIVA', 'BLOQUEADA'],
                $this->toggleStatus((string) ($conta['status_cadastral'] ?? 'ATIVA'), 'ATIVA', 'INATIVA')
            );

            $repository->setAccountStatus($entityId, $novoStatus);
            $this->audit('CONTAS', 'CONTA_STATUS_UPDATE', 'contas', $entityId, ['status_cadastral' => $novoStatus], $request);
            Flash::set('success', 'Status da conta atualizado para ' . $novoStatus . '.');
            return;
        }

        if ($action === 'excluir') {
            $reason = $this->requiredReason($request->input('motivo_exclusao'));
            $userId = (int) ($_SESSION['auth']['usuario_id'] ?? 0);

            $repository->softDeleteAccount($entityId, $userId, $reason);
            $this->audit('CONTAS', 'CONTA_SOFT_DELETE', 'contas', $entityId, ['motivo_exclusao' => $reason], $request);
            Flash::set('success', 'Conta movida para historico com sucesso.');
            return;
        }

        throw new RuntimeException('Acao invalida para conta.');
    }

    private function handleOrgaoAction(string $action, int $entityId, Request $request, array $auth, InstitutionRepository $repository): void
    {
        $orgao = $repository->orgaoById($entityId);
        if ($orgao === null) {
            throw new RuntimeException('Orgao nao encontrado.');
        }

        $ufSigla = BrazilUf::normalize($orgao['uf_sigla'] ?? null);
        if (!$this->canOperateUf($auth, $ufSigla)) {
            throw new RuntimeException('Seu perfil administrativo nao pode operar fora do UF de contexto.');
        }

        if ($action === 'editar') {
            $nomeOficial = trim((string) $request->input('nome_oficial', ''));
            if ($nomeOficial === '') {
                throw new RuntimeException('Informe o nome oficial do orgao para editar.');
            }

            $repository->updateOrgao($entityId, [
                'nome_oficial' => $nomeOficial,
                'sigla' => $this->nullableText($request->input('sigla')),
                'cnpj' => $this->nullableText($request->input('cnpj')),
                'status_orgao' => $this->sanitizeEnum((string) $request->input('status_orgao', 'ATIVO'), ['ATIVO', 'INATIVO', 'BLOQUEADO'], 'ATIVO'),
            ]);

            $this->audit('ORGAOS', 'ORGAO_UPDATE', 'orgaos', $entityId, ['nome_oficial' => $nomeOficial], $request);
            Flash::set('success', 'Orgao atualizado com sucesso.');
            return;
        }

        if ($action === 'status') {
            $novoStatus = $this->sanitizeEnum(
                (string) $request->input('novo_status', ''),
                ['ATIVO', 'INATIVO', 'BLOQUEADO'],
                $this->toggleStatus((string) ($orgao['status_orgao'] ?? 'ATIVO'), 'ATIVO', 'INATIVO')
            );

            $repository->setOrgaoStatus($entityId, $novoStatus);
            $this->audit('ORGAOS', 'ORGAO_STATUS_UPDATE', 'orgaos', $entityId, ['status_orgao' => $novoStatus], $request);
            Flash::set('success', 'Status do orgao atualizado para ' . $novoStatus . '.');
            return;
        }

        if ($action === 'excluir') {
            $reason = $this->requiredReason($request->input('motivo_exclusao'));
            $userId = (int) ($_SESSION['auth']['usuario_id'] ?? 0);

            $repository->softDeleteOrgao($entityId, $userId, $reason);
            $this->audit('ORGAOS', 'ORGAO_SOFT_DELETE', 'orgaos', $entityId, ['motivo_exclusao' => $reason], $request);
            Flash::set('success', 'Orgao movido para historico com sucesso.');
            return;
        }

        throw new RuntimeException('Acao invalida para orgao.');
    }

    private function handleUnidadeAction(string $action, int $entityId, Request $request, array $auth, InstitutionRepository $repository): void
    {
        $unidade = $repository->unidadeById($entityId);
        if ($unidade === null) {
            throw new RuntimeException('Unidade nao encontrada.');
        }

        $ufSigla = BrazilUf::normalize($unidade['uf_sigla'] ?? null);
        if (!$this->canOperateUf($auth, $ufSigla)) {
            throw new RuntimeException('Seu perfil administrativo nao pode operar fora do UF de contexto.');
        }

        if ($action === 'editar') {
            $nomeUnidade = trim((string) $request->input('nome_unidade', ''));
            if ($nomeUnidade === '') {
                throw new RuntimeException('Informe o nome da unidade para editar.');
            }

            $repository->updateUnidade($entityId, [
                'codigo_unidade' => $this->nullableText($request->input('codigo_unidade')),
                'nome_unidade' => $nomeUnidade,
                'tipo_unidade' => $this->nullableText($request->input('tipo_unidade')),
                'status_unidade' => $this->sanitizeEnum((string) $request->input('status_unidade', 'ATIVA'), ['ATIVA', 'INATIVA'], 'ATIVA'),
            ]);

            $this->audit('UNIDADES', 'UNIDADE_UPDATE', 'unidades', $entityId, ['nome_unidade' => $nomeUnidade], $request);
            Flash::set('success', 'Unidade atualizada com sucesso.');
            return;
        }

        if ($action === 'status') {
            $novoStatus = $this->sanitizeEnum(
                (string) $request->input('novo_status', ''),
                ['ATIVA', 'INATIVA'],
                $this->toggleStatus((string) ($unidade['status_unidade'] ?? 'ATIVA'), 'ATIVA', 'INATIVA')
            );

            $repository->setUnidadeStatus($entityId, $novoStatus);
            $this->audit('UNIDADES', 'UNIDADE_STATUS_UPDATE', 'unidades', $entityId, ['status_unidade' => $novoStatus], $request);
            Flash::set('success', 'Status da unidade atualizado para ' . $novoStatus . '.');
            return;
        }

        if ($action === 'excluir') {
            $reason = $this->requiredReason($request->input('motivo_exclusao'));
            $userId = (int) ($_SESSION['auth']['usuario_id'] ?? 0);

            $repository->softDeleteUnidade($entityId, $userId, $reason);
            $this->audit('UNIDADES', 'UNIDADE_SOFT_DELETE', 'unidades', $entityId, ['motivo_exclusao' => $reason], $request);
            Flash::set('success', 'Unidade movida para historico com sucesso.');
            return;
        }

        throw new RuntimeException('Acao invalida para unidade.');
    }

    private function handleUsuarioAction(string $action, int $entityId, Request $request, array $auth, InstitutionRepository $repository): void
    {
        $usuario = $repository->usuarioById($entityId);
        if ($usuario === null) {
            throw new RuntimeException('Usuario nao encontrado.');
        }

        $ufSigla = BrazilUf::normalize($usuario['uf_sigla'] ?? null);
        if (!$this->canOperateUf($auth, $ufSigla)) {
            throw new RuntimeException('Seu perfil administrativo nao pode operar fora do UF de contexto.');
        }

        if ($action === 'editar') {
            $nomeCompleto = trim((string) $request->input('nome_completo', ''));
            $emailLogin = trim(strtolower((string) $request->input('email_login', '')));

            if ($nomeCompleto === '' || $emailLogin === '') {
                throw new RuntimeException('Informe nome e email do usuario para editar.');
            }

            if (!filter_var($emailLogin, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Informe um email valido para o usuario.');
            }

            $unidadeId = $this->nullableInt($request->input('unidade_id'));
            if ($unidadeId !== null) {
                $unidade = $repository->unidadeById($unidadeId);
                if ($unidade === null || (int) ($unidade['orgao_id'] ?? 0) !== (int) $usuario['orgao_id']) {
                    throw new RuntimeException('A unidade selecionada nao pertence ao orgao do usuario.');
                }
            }

            $status = $this->sanitizeEnum((string) $request->input('status_usuario', 'ATIVO'), ['ATIVO', 'INATIVO', 'BLOQUEADO'], 'ATIVO');
            $repository->updateUsuario($entityId, [
                'unidade_id' => $unidadeId,
                'nome_completo' => $nomeCompleto,
                'email_login' => $emailLogin,
                'matricula_funcional' => $this->nullableText($request->input('matricula_funcional')),
                'status_usuario' => $status,
            ]);

            $this->audit('USUARIOS', 'USUARIO_UPDATE', 'usuarios', $entityId, ['nome_completo' => $nomeCompleto, 'status_usuario' => $status], $request);
            Flash::set('success', 'Usuario atualizado com sucesso.');
            return;
        }

        if ($action === 'status') {
            $novoStatus = $this->sanitizeEnum(
                (string) $request->input('novo_status', ''),
                ['ATIVO', 'INATIVO', 'BLOQUEADO'],
                $this->toggleStatus((string) ($usuario['status_usuario'] ?? 'ATIVO'), 'ATIVO', 'INATIVO')
            );

            $repository->setUsuarioStatus($entityId, $novoStatus);
            $this->audit('USUARIOS', 'USUARIO_STATUS_UPDATE', 'usuarios', $entityId, ['status_usuario' => $novoStatus], $request);
            Flash::set('success', 'Status do usuario atualizado para ' . $novoStatus . '.');
            return;
        }

        if ($action === 'excluir') {
            $reason = $this->requiredReason($request->input('motivo_exclusao'));
            $userId = (int) ($_SESSION['auth']['usuario_id'] ?? 0);

            $repository->softDeleteUsuario($entityId, $userId, $reason);
            $this->audit('USUARIOS', 'USUARIO_SOFT_DELETE', 'usuarios', $entityId, ['motivo_exclusao' => $reason], $request);
            Flash::set('success', 'Usuario movido para historico com sucesso.');
            return;
        }

        throw new RuntimeException('Acao invalida para usuario.');
    }

    private function handlePerfilAction(string $action, int $entityId, Request $request, InstitutionRepository $repository): void
    {
        $perfil = $repository->perfilById($entityId);
        if ($perfil === null) {
            throw new RuntimeException('Perfil nao encontrado.');
        }

        if ($action === 'editar') {
            $nomePerfil = strtoupper(trim((string) $request->input('nome_perfil', '')));
            if ($nomePerfil === '') {
                throw new RuntimeException('Informe o nome do perfil para editar.');
            }

            $status = $this->sanitizeEnum((string) $request->input('status_perfil', 'ATIVO'), ['ATIVO', 'INATIVO'], 'ATIVO');
            $repository->updatePerfil($entityId, [
                'nome_perfil' => $nomePerfil,
                'descricao' => $this->nullableText($request->input('descricao')),
                'status_perfil' => $status,
            ]);

            $this->audit('PERFIS', 'PERFIL_UPDATE', 'perfis', $entityId, ['nome_perfil' => $nomePerfil, 'status_perfil' => $status], $request);
            Flash::set('success', 'Perfil atualizado com sucesso.');
            return;
        }

        if ($action === 'status') {
            $novoStatus = $this->sanitizeEnum(
                (string) $request->input('novo_status', ''),
                ['ATIVO', 'INATIVO'],
                $this->toggleStatus((string) ($perfil['status_perfil'] ?? 'ATIVO'), 'ATIVO', 'INATIVO')
            );

            $repository->setPerfilStatus($entityId, $novoStatus);
            $this->audit('PERFIS', 'PERFIL_STATUS_UPDATE', 'perfis', $entityId, ['status_perfil' => $novoStatus], $request);
            Flash::set('success', 'Status do perfil atualizado para ' . $novoStatus . '.');
            return;
        }

        if ($action === 'excluir') {
            $reason = $this->requiredReason($request->input('motivo_exclusao'));
            $userId = (int) ($_SESSION['auth']['usuario_id'] ?? 0);

            $repository->softDeletePerfil($entityId, $userId, $reason);
            $this->audit('PERFIS', 'PERFIL_SOFT_DELETE', 'perfis', $entityId, ['motivo_exclusao' => $reason], $request);
            Flash::set('success', 'Perfil movido para historico com sucesso.');
            return;
        }

        throw new RuntimeException('Acao invalida para perfil.');
    }

    private function handleVinculoAction(string $action, int $entityId, Request $request, array $auth, InstitutionRepository $repository): void
    {
        $vinculo = $repository->vinculoById($entityId);
        if ($vinculo === null) {
            throw new RuntimeException('Vinculo nao encontrado.');
        }

        $ufSigla = BrazilUf::normalize($vinculo['uf_sigla'] ?? null);
        if (!$this->canOperateUf($auth, $ufSigla)) {
            throw new RuntimeException('Seu perfil administrativo nao pode operar fora do UF de contexto.');
        }

        if ($action === 'editar') {
            $perfilId = (int) $request->input('perfil_id', 0);
            if ($perfilId < 1) {
                throw new RuntimeException('Informe o perfil para atualizar o vinculo.');
            }

            $perfil = $repository->perfilById($perfilId);
            if ($perfil === null || ($perfil['excluido_em'] ?? null) !== null || (string) ($perfil['status_perfil'] ?? '') !== 'ATIVO') {
                throw new RuntimeException('Selecione um perfil ativo para o vinculo.');
            }

            $status = $this->sanitizeEnum((string) $request->input('status_vinculo', 'ATIVO'), ['ATIVO', 'INATIVO'], 'ATIVO');
            $repository->updateVinculo($entityId, $perfilId, $status);

            $this->audit('PERFIS', 'USUARIO_PERFIL_UPDATE', 'usuarios_perfis', $entityId, ['perfil_id' => $perfilId, 'status_vinculo' => $status], $request);
            Flash::set('success', 'Vinculo atualizado com sucesso.');
            return;
        }

        if ($action === 'status') {
            $novoStatus = $this->sanitizeEnum(
                (string) $request->input('novo_status', ''),
                ['ATIVO', 'INATIVO'],
                $this->toggleStatus((string) ($vinculo['status_vinculo'] ?? 'ATIVO'), 'ATIVO', 'INATIVO')
            );

            $repository->setVinculoStatus($entityId, $novoStatus);
            $this->audit('PERFIS', 'USUARIO_PERFIL_STATUS_UPDATE', 'usuarios_perfis', $entityId, ['status_vinculo' => $novoStatus], $request);
            Flash::set('success', 'Status do vinculo atualizado para ' . $novoStatus . '.');
            return;
        }

        if ($action === 'excluir') {
            $reason = $this->requiredReason($request->input('motivo_exclusao'));
            $userId = (int) ($_SESSION['auth']['usuario_id'] ?? 0);

            $repository->softDeleteVinculo($entityId, $userId, $reason);
            $this->audit('PERFIS', 'USUARIO_PERFIL_SOFT_DELETE', 'usuarios_perfis', $entityId, ['motivo_exclusao' => $reason], $request);
            Flash::set('success', 'Vinculo movido para historico com sucesso.');
            return;
        }

        throw new RuntimeException('Acao invalida para vinculo.');
    }

    private function buildProfileGuide(array $profiles): array
    {
        $catalog = [
            UserProfile::ADMIN_MASTER => [
                'descricao' => 'Administrador master da plataforma com governanca ampla.',
                'pode' => [
                    'Gerenciar estrutura institucional (contas, orgaos, unidades, usuarios e vinculos).',
                    'Atuar em multiplos UFs e manter padroes institucionais da plataforma.',
                    'Gerenciar recursos administrativos, enterprise e operacionais quando habilitados.',
                ],
                'nao_pode' => [
                    'Nao ignora modulos nao contratados para a conta.',
                    'Nao substitui trilha de auditoria nem politicas de seguranca.',
                ],
                'observacao' => 'Perfil recomendado para administracao central do SaaS.',
            ],
            UserProfile::ADMIN_ORGAO => [
                'descricao' => 'Administrador institucional focado na operacao do orgao.',
                'pode' => [
                    'Gerenciar estrutura institucional e usuarios do contexto permitido.',
                    'Atualizar perfis, vinculos e configuracoes administrativas do orgao.',
                    'Operar recursos enterprise liberados para o orgao.',
                ],
                'nao_pode' => [
                    'Nao opera fora do UF de contexto definido.',
                    'Nao possui escopo global da plataforma como ADMIN_MASTER.',
                ],
                'observacao' => 'Ideal para equipe de administracao local do orgao.',
            ],
            UserProfile::FINANCEIRO => [
                'descricao' => 'Perfil focado em rotinas financeiras, SLA e suporte institucional.',
                'pode' => [
                    'Acessar visoes financeiras, analytics e governanca de SLA/suporte.',
                    'Acompanhar indicadores executivos permitidos para a conta.',
                    'Atuar em fluxos enterprise ligados a contratos e suporte.',
                ],
                'nao_pode' => [
                    'Nao gerencia API, automacoes e integracoes tecnicas avancadas.',
                    'Nao administra estrutura institucional completa como ADMIN_MASTER.',
                ],
                'observacao' => 'Recomendado para responsaveis financeiros e contratos.',
            ],
            UserProfile::SUPORTE => [
                'descricao' => 'Perfil tecnico para suporte, API, integracoes e automacoes.',
                'pode' => [
                    'Gerenciar API enterprise, integracoes externas e automacoes.',
                    'Atuar em SLA, tickets e assinatura digital conforme modulos liberados.',
                    'Apoiar ajustes tecnicos operacionais e administrativos.',
                ],
                'nao_pode' => [
                    'Nao substitui governanca global do ADMIN_MASTER.',
                    'Nao acessa modulos nao contratados para a conta.',
                ],
                'observacao' => 'Indicado para equipe de suporte tecnico e sustentacao.',
            ],
            UserProfile::GESTOR => [
                'descricao' => 'Perfil de lideranca da operacao com amplo alcance funcional.',
                'pode' => [
                    'Abrir incidentes e registrar briefing, comando, periodos e diarios operacionais.',
                    'Gerenciar PLANCON, governanca operacional, documentos e relatorios avancados.',
                    'Acessar inteligencia operacional e acompanhar execucao tatico-estrategica.',
                ],
                'nao_pode' => [
                    'Nao gerencia estrutura administrativa do SaaS por padrao.',
                    'Nao acessa modulos operacionais nao liberados pelo plano.',
                ],
                'observacao' => 'Recomendado para decisores e lideres de resposta.',
            ],
            UserProfile::COORDENADOR => [
                'descricao' => 'Coordena resposta operacional com forte capacidade de execucao.',
                'pode' => [
                    'Abrir incidentes e manter registros operacionais completos.',
                    'Gerenciar comando, PLANCON, governanca e documentos da resposta.',
                    'Acompanhar inteligencia operacional e relatorios.',
                ],
                'nao_pode' => [
                    'Nao possui governanca administrativa global da plataforma.',
                    'Nao acessa recursos fora do escopo liberado para a conta.',
                ],
                'observacao' => 'Perfil indicado para coordenacao diaria de operacoes.',
            ],
            UserProfile::ANALISTA => [
                'descricao' => 'Perfil analitico com atuacao tecnica e documental robusta.',
                'pode' => [
                    'Abrir incidentes, periodos e registros operacionais com apoio analitico.',
                    'Atuar em PLANCON, inteligencia, governanca e documentos.',
                    'Ler documentos privados de outros usuarios conforme politica operacional.',
                ],
                'nao_pode' => [
                    'Nao possui autoridade administrativa de conta/orgao por padrao.',
                    'Nao executa recursos fora dos modulos contratados.',
                ],
                'observacao' => 'Indicado para analise tecnica e monitoramento.',
            ],
            UserProfile::OPERADOR => [
                'descricao' => 'Perfil de execucao operacional com foco em campo e registro.',
                'pode' => [
                    'Abrir incidentes e registrar briefing, periodos, diarios e documentos.',
                    'Atuar em modulos operacionais e acompanhamento de indicadores.',
                    'Contribuir com atualizacao continua da situacao operacional.',
                ],
                'nao_pode' => [
                    'Nao acessa governanca operacional restrita (aceites/termos) por padrao.',
                    'Nao possui acesso administrativo institucional amplo.',
                ],
                'observacao' => 'Perfil recomendado para equipes de execucao operacional.',
            ],
            UserProfile::LEITOR => [
                'descricao' => 'Perfil de consulta para acompanhamento e leitura.',
                'pode' => [
                    'Visualizar incidentes, PLANCON, inteligencia, documentos e relatorios.',
                    'Acompanhar indicadores e historicos sem alterar fluxos principais.',
                ],
                'nao_pode' => [
                    'Nao abre incidentes nem cria registros operacionais.',
                    'Nao envia documentos nem altera configuracoes.',
                ],
                'observacao' => 'Ideal para monitoramento, auditoria e visao executiva.',
            ],
            UserProfile::CONVIDADO => [
                'descricao' => 'Acesso restrito para consulta controlada.',
                'pode' => [
                    'Autenticar e acessar recursos explicitamente liberados ao convite.',
                    'Consultar informacoes basicas quando permitido por modulo e escopo.',
                ],
                'nao_pode' => [
                    'Nao administra estrutura institucional.',
                    'Nao opera fluxos operacionais completos por padrao.',
                ],
                'observacao' => 'Use em acessos temporarios ou acompanhamentos pontuais.',
            ],
        ];

        $guide = [];
        foreach ($profiles as $profile) {
            $profileName = strtoupper(trim((string) ($profile['nome_perfil'] ?? '')));
            if ($profileName === '') {
                continue;
            }

            $meta = $catalog[$profileName] ?? [
                'descricao' => 'Perfil personalizado criado para necessidades especificas da instituicao.',
                'pode' => [
                    'Atuar conforme os modulos contratados e configuracoes aplicadas ao perfil.',
                ],
                'nao_pode' => [
                    'Nao acessa funcionalidades sem permissao explicita ou fora do escopo definido.',
                ],
                'observacao' => 'Valide os vinculos e o escopo antes de atribuir este perfil a usuarios.',
            ];

            $databaseDescription = trim((string) ($profile['descricao'] ?? ''));
            $guide[] = [
                'nome_perfil' => $profileName,
                'descricao' => $databaseDescription !== '' ? $databaseDescription : (string) $meta['descricao'],
                'status_perfil' => (string) ($profile['status_perfil'] ?? ''),
                'pode' => is_array($meta['pode']) ? $meta['pode'] : [],
                'nao_pode' => is_array($meta['nao_pode']) ? $meta['nao_pode'] : [],
                'observacao' => (string) ($meta['observacao'] ?? ''),
            ];
        }

        return $guide;
    }

    private function repository(): InstitutionRepository
    {
        $repository = $this->institutionRepository ?? new InstitutionRepository();
        $repository->ensureInstitutionLifecycleSchema();

        return $repository;
    }

    private function redirectWithTab(string $tab, ?string $ufSigla = null): Response
    {
        $query = ['aba' => $this->resolveTab($tab)];
        if ($ufSigla !== null) {
            $query['uf'] = $ufSigla;
        }

        return Response::redirect('/admin/institucional?' . http_build_query($query));
    }

    private function resolveTab(string $tab): string
    {
        $tab = strtolower(trim($tab));
        if (!in_array($tab, self::TABS, true)) {
            return self::TABS[0];
        }

        return $tab;
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) $value);
        return $text === '' ? null : $text;
    }

    private function nullableInt(mixed $value): ?int
    {
        $intValue = (int) $value;
        return $intValue > 0 ? $intValue : null;
    }

    private function sanitizeEnum(string $value, array $allowed, string $default): string
    {
        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function requiredReason(mixed $value): string
    {
        $reason = trim((string) $value);
        if ($reason === '') {
            throw new RuntimeException('Informe o motivo da exclusao para concluir a acao.');
        }

        return mb_substr($reason, 0, 255);
    }

    private function toggleStatus(string $currentStatus, string $activeStatus, string $inactiveStatus): string
    {
        return $currentStatus === $activeStatus ? $inactiveStatus : $activeStatus;
    }

    private function resolveRedirectUf(Request $request, array $auth): ?string
    {
        if ($this->isAdminMaster($auth)) {
            return BrazilUf::normalize($request->input('uf'));
        }

        return BrazilUf::normalize($auth['uf_sigla'] ?? null);
    }

    private function resolveUfFilter(Request $request, array $auth): ?string
    {
        $requestedUf = BrazilUf::normalize($request->input('uf'));
        if ($this->isAdminMaster($auth)) {
            return $requestedUf;
        }

        return BrazilUf::normalize($auth['uf_sigla'] ?? null);
    }

    private function isAdminMaster(array $auth): bool
    {
        $profiles = is_array($auth['perfis'] ?? null) ? $auth['perfis'] : [];
        return in_array(UserProfile::ADMIN_MASTER, $profiles, true);
    }

    private function canOperateUf(array $auth, ?string $targetUf): bool
    {
        if ($this->isAdminMaster($auth)) {
            return true;
        }

        $userUf = BrazilUf::normalize($auth['uf_sigla'] ?? null);
        if ($userUf === null || $targetUf === null) {
            return false;
        }

        return $userUf === $targetUf;
    }

    private function audit(string $modulo, string $acao, string $entidadeTipo, ?int $entidadeId, array $detalhes, Request $request): void
    {
        ($this->auditService ?? new AuditService())->log([
            'conta_id' => $_SESSION['auth']['conta_id'] ?? null,
            'orgao_id' => $_SESSION['auth']['orgao_id'] ?? null,
            'unidade_id' => $_SESSION['auth']['unidade_id'] ?? null,
            'usuario_id' => $_SESSION['auth']['usuario_id'] ?? null,
            'modulo_codigo' => $modulo,
            'acao' => $acao,
            'resultado' => 'SUCESSO',
            'entidade_tipo' => $entidadeTipo,
            'entidade_id' => $entidadeId,
            'detalhes' => $detalhes,
            'ip_address' => $request->ipAddress(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
