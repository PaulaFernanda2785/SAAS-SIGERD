<?php

declare(strict_types=1);

namespace App\Repositories\SaaS;

use App\Support\Database;
use PDO;

final class InstitutionRepository
{
    private static bool $lifecycleSchemaChecked = false;

    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function ensureInstitutionLifecycleSchema(): void
    {
        if (self::$lifecycleSchemaChecked) {
            return;
        }

        $this->ensureColumn('contas', 'excluido_em', 'ALTER TABLE contas ADD COLUMN excluido_em DATETIME NULL AFTER status_cadastral');
        $this->ensureColumn('contas', 'motivo_exclusao', 'ALTER TABLE contas ADD COLUMN motivo_exclusao VARCHAR(255) NULL AFTER excluido_em');
        $this->ensureColumn('contas', 'excluido_por_usuario_id', 'ALTER TABLE contas ADD COLUMN excluido_por_usuario_id BIGINT UNSIGNED NULL AFTER motivo_exclusao');

        $this->ensureColumn('orgaos', 'excluido_em', 'ALTER TABLE orgaos ADD COLUMN excluido_em DATETIME NULL AFTER status_orgao');
        $this->ensureColumn('orgaos', 'motivo_exclusao', 'ALTER TABLE orgaos ADD COLUMN motivo_exclusao VARCHAR(255) NULL AFTER excluido_em');
        $this->ensureColumn('orgaos', 'excluido_por_usuario_id', 'ALTER TABLE orgaos ADD COLUMN excluido_por_usuario_id BIGINT UNSIGNED NULL AFTER motivo_exclusao');

        $this->ensureColumn('unidades', 'excluido_em', 'ALTER TABLE unidades ADD COLUMN excluido_em DATETIME NULL AFTER status_unidade');
        $this->ensureColumn('unidades', 'motivo_exclusao', 'ALTER TABLE unidades ADD COLUMN motivo_exclusao VARCHAR(255) NULL AFTER excluido_em');
        $this->ensureColumn('unidades', 'excluido_por_usuario_id', 'ALTER TABLE unidades ADD COLUMN excluido_por_usuario_id BIGINT UNSIGNED NULL AFTER motivo_exclusao');

        $this->ensureColumn('usuarios', 'excluido_em', 'ALTER TABLE usuarios ADD COLUMN excluido_em DATETIME NULL AFTER status_usuario');
        $this->ensureColumn('usuarios', 'motivo_exclusao', 'ALTER TABLE usuarios ADD COLUMN motivo_exclusao VARCHAR(255) NULL AFTER excluido_em');
        $this->ensureColumn('usuarios', 'excluido_por_usuario_id', 'ALTER TABLE usuarios ADD COLUMN excluido_por_usuario_id BIGINT UNSIGNED NULL AFTER motivo_exclusao');

        $this->ensureColumn('perfis', 'excluido_em', 'ALTER TABLE perfis ADD COLUMN excluido_em DATETIME NULL AFTER status_perfil');
        $this->ensureColumn('perfis', 'motivo_exclusao', 'ALTER TABLE perfis ADD COLUMN motivo_exclusao VARCHAR(255) NULL AFTER excluido_em');
        $this->ensureColumn('perfis', 'excluido_por_usuario_id', 'ALTER TABLE perfis ADD COLUMN excluido_por_usuario_id BIGINT UNSIGNED NULL AFTER motivo_exclusao');

        $this->ensureColumn('usuarios_perfis', 'status_vinculo', "ALTER TABLE usuarios_perfis ADD COLUMN status_vinculo ENUM('ATIVO','INATIVO','EXCLUIDO') NOT NULL DEFAULT 'ATIVO' AFTER perfil_id");
        $this->ensureColumn('usuarios_perfis', 'updated_at', 'ALTER TABLE usuarios_perfis ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at');
        $this->ensureColumn('usuarios_perfis', 'excluido_em', 'ALTER TABLE usuarios_perfis ADD COLUMN excluido_em DATETIME NULL AFTER updated_at');
        $this->ensureColumn('usuarios_perfis', 'motivo_exclusao', 'ALTER TABLE usuarios_perfis ADD COLUMN motivo_exclusao VARCHAR(255) NULL AFTER excluido_em');
        $this->ensureColumn('usuarios_perfis', 'excluido_por_usuario_id', 'ALTER TABLE usuarios_perfis ADD COLUMN excluido_por_usuario_id BIGINT UNSIGNED NULL AFTER motivo_exclusao');

        self::$lifecycleSchemaChecked = true;
    }

    public function accounts(?string $ufSigla = null): array
    {
        $this->ensureInstitutionLifecycleSchema();

        $where = ['c.excluido_em IS NULL'];
        $params = [];
        $this->applyUfWhere('c', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT c.id, c.nome_fantasia, c.razao_social, c.cpf_cnpj, c.uf_sigla, c.email_principal, c.status_cadastral, c.created_at
             FROM contas c
             {$whereSql}
             ORDER BY c.id DESC
             LIMIT 150"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function accountById(int $contaId): ?array
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'SELECT id, nome_fantasia, razao_social, cpf_cnpj, uf_sigla, email_principal, status_cadastral, excluido_em
             FROM contas
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $contaId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function createAccount(array $data): int
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'INSERT INTO contas (nome_fantasia, razao_social, cpf_cnpj, uf_sigla, email_principal, status_cadastral, excluido_em, motivo_exclusao, excluido_por_usuario_id, created_at, updated_at)
             VALUES (:nome_fantasia, :razao_social, :cpf_cnpj, :uf_sigla, :email_principal, :status_cadastral, NULL, NULL, NULL, NOW(), NOW())'
        );
        $statement->execute([
            'nome_fantasia' => $data['nome_fantasia'],
            'razao_social' => $data['razao_social'] ?? null,
            'cpf_cnpj' => $data['cpf_cnpj'] ?? null,
            'uf_sigla' => $data['uf_sigla'],
            'email_principal' => $data['email_principal'] ?? null,
            'status_cadastral' => $data['status_cadastral'] ?? 'ATIVA',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function updateAccount(int $contaId, array $data): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE contas
             SET nome_fantasia = :nome_fantasia,
                 razao_social = :razao_social,
                 cpf_cnpj = :cpf_cnpj,
                 email_principal = :email_principal,
                 status_cadastral = :status_cadastral,
                 excluido_em = NULL,
                 motivo_exclusao = NULL,
                 excluido_por_usuario_id = NULL,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $contaId,
            'nome_fantasia' => $data['nome_fantasia'],
            'razao_social' => $data['razao_social'] ?? null,
            'cpf_cnpj' => $data['cpf_cnpj'] ?? null,
            'email_principal' => $data['email_principal'] ?? null,
            'status_cadastral' => $data['status_cadastral'],
        ]);
    }

    public function setAccountStatus(int $contaId, string $status): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE contas
             SET status_cadastral = :status_cadastral,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $contaId,
            'status_cadastral' => $status,
        ]);
    }

    public function softDeleteAccount(int $contaId, int $userId, string $reason): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE contas
             SET status_cadastral = :status_cadastral,
                 excluido_em = NOW(),
                 motivo_exclusao = :motivo_exclusao,
                 excluido_por_usuario_id = :excluido_por_usuario_id,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $contaId,
            'status_cadastral' => 'BLOQUEADA',
            'motivo_exclusao' => $reason,
            'excluido_por_usuario_id' => $userId > 0 ? $userId : null,
        ]);
    }

    public function orgaos(?string $ufSigla = null): array
    {
        $this->ensureInstitutionLifecycleSchema();

        $where = ['o.excluido_em IS NULL'];
        $params = [];
        $this->applyUfWhere('o', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT o.id, o.conta_id, c.nome_fantasia AS conta_nome, o.nome_oficial, o.sigla, o.cnpj, o.uf_sigla, o.status_orgao, o.created_at
             FROM orgaos o
             INNER JOIN contas c ON c.id = o.conta_id
             {$whereSql}
             ORDER BY o.id DESC
             LIMIT 200"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function orgaoById(int $orgaoId): ?array
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'SELECT o.id, o.conta_id, o.nome_oficial, o.sigla, o.cnpj, o.uf_sigla, o.status_orgao, o.excluido_em
             FROM orgaos o
             WHERE o.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $orgaoId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function createOrgao(array $data): int
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'INSERT INTO orgaos (conta_id, nome_oficial, sigla, cnpj, uf_sigla, status_orgao, excluido_em, motivo_exclusao, excluido_por_usuario_id, created_at, updated_at)
             VALUES (:conta_id, :nome_oficial, :sigla, :cnpj, :uf_sigla, :status_orgao, NULL, NULL, NULL, NOW(), NOW())'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'nome_oficial' => $data['nome_oficial'],
            'sigla' => $data['sigla'] ?? null,
            'cnpj' => $data['cnpj'] ?? null,
            'uf_sigla' => $data['uf_sigla'],
            'status_orgao' => $data['status_orgao'] ?? 'ATIVO',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function updateOrgao(int $orgaoId, array $data): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE orgaos
             SET nome_oficial = :nome_oficial,
                 sigla = :sigla,
                 cnpj = :cnpj,
                 status_orgao = :status_orgao,
                 excluido_em = NULL,
                 motivo_exclusao = NULL,
                 excluido_por_usuario_id = NULL,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $orgaoId,
            'nome_oficial' => $data['nome_oficial'],
            'sigla' => $data['sigla'] ?? null,
            'cnpj' => $data['cnpj'] ?? null,
            'status_orgao' => $data['status_orgao'],
        ]);
    }

    public function setOrgaoStatus(int $orgaoId, string $status): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE orgaos
             SET status_orgao = :status_orgao,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $orgaoId,
            'status_orgao' => $status,
        ]);
    }

    public function softDeleteOrgao(int $orgaoId, int $userId, string $reason): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE orgaos
             SET status_orgao = :status_orgao,
                 excluido_em = NOW(),
                 motivo_exclusao = :motivo_exclusao,
                 excluido_por_usuario_id = :excluido_por_usuario_id,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $orgaoId,
            'status_orgao' => 'BLOQUEADO',
            'motivo_exclusao' => $reason,
            'excluido_por_usuario_id' => $userId > 0 ? $userId : null,
        ]);
    }

    public function unidades(?string $ufSigla = null): array
    {
        $this->ensureInstitutionLifecycleSchema();

        $where = ['u.excluido_em IS NULL'];
        $params = [];
        $this->applyUfWhere('u', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT u.id, u.orgao_id, o.nome_oficial AS orgao_nome, u.unidade_superior_id, u.codigo_unidade, u.nome_unidade,
                    u.tipo_unidade, u.uf_sigla, u.status_unidade, u.created_at
             FROM unidades u
             INNER JOIN orgaos o ON o.id = u.orgao_id
             {$whereSql}
             ORDER BY u.id DESC
             LIMIT 250"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function unidadeById(int $unidadeId): ?array
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'SELECT u.id, u.orgao_id, o.conta_id, u.codigo_unidade, u.nome_unidade, u.tipo_unidade, u.uf_sigla, u.status_unidade, u.excluido_em
             FROM unidades u
             INNER JOIN orgaos o ON o.id = u.orgao_id
             WHERE u.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $unidadeId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function createUnidade(array $data): int
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'INSERT INTO unidades (orgao_id, unidade_superior_id, codigo_unidade, nome_unidade, tipo_unidade, uf_sigla, status_unidade, excluido_em, motivo_exclusao, excluido_por_usuario_id, created_at, updated_at)
             VALUES (:orgao_id, :unidade_superior_id, :codigo_unidade, :nome_unidade, :tipo_unidade, :uf_sigla, :status_unidade, NULL, NULL, NULL, NOW(), NOW())'
        );
        $statement->execute([
            'orgao_id' => $data['orgao_id'],
            'unidade_superior_id' => $data['unidade_superior_id'] ?? null,
            'codigo_unidade' => $data['codigo_unidade'] ?? null,
            'nome_unidade' => $data['nome_unidade'],
            'tipo_unidade' => $data['tipo_unidade'] ?? null,
            'uf_sigla' => $data['uf_sigla'],
            'status_unidade' => $data['status_unidade'] ?? 'ATIVA',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function updateUnidade(int $unidadeId, array $data): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE unidades
             SET codigo_unidade = :codigo_unidade,
                 nome_unidade = :nome_unidade,
                 tipo_unidade = :tipo_unidade,
                 status_unidade = :status_unidade,
                 excluido_em = NULL,
                 motivo_exclusao = NULL,
                 excluido_por_usuario_id = NULL,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $unidadeId,
            'codigo_unidade' => $data['codigo_unidade'] ?? null,
            'nome_unidade' => $data['nome_unidade'],
            'tipo_unidade' => $data['tipo_unidade'] ?? null,
            'status_unidade' => $data['status_unidade'],
        ]);
    }

    public function setUnidadeStatus(int $unidadeId, string $status): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE unidades
             SET status_unidade = :status_unidade,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $unidadeId,
            'status_unidade' => $status,
        ]);
    }

    public function softDeleteUnidade(int $unidadeId, int $userId, string $reason): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE unidades
             SET status_unidade = :status_unidade,
                 excluido_em = NOW(),
                 motivo_exclusao = :motivo_exclusao,
                 excluido_por_usuario_id = :excluido_por_usuario_id,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $unidadeId,
            'status_unidade' => 'INATIVA',
            'motivo_exclusao' => $reason,
            'excluido_por_usuario_id' => $userId > 0 ? $userId : null,
        ]);
    }

    public function usuarios(?string $ufSigla = null): array
    {
        $this->ensureInstitutionLifecycleSchema();

        $where = ['u.excluido_em IS NULL'];
        $params = [];
        $this->applyUfWhere('u', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT u.id, u.nome_completo, u.email_login, u.conta_id, c.nome_fantasia AS conta_nome,
                    u.orgao_id, o.nome_oficial AS orgao_nome, u.unidade_id, un.nome_unidade AS unidade_nome,
                    u.matricula_funcional, u.uf_sigla, u.status_usuario, u.created_at
             FROM usuarios u
             INNER JOIN contas c ON c.id = u.conta_id
             INNER JOIN orgaos o ON o.id = u.orgao_id
             LEFT JOIN unidades un ON un.id = u.unidade_id
             {$whereSql}
             ORDER BY u.id DESC
             LIMIT 300"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function createUsuario(array $data): int
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'INSERT INTO usuarios
                (conta_id, orgao_id, unidade_id, uf_sigla, nome_completo, email_login, matricula_funcional, password_hash, status_usuario, excluido_em, motivo_exclusao, excluido_por_usuario_id, created_at, updated_at)
             VALUES
                (:conta_id, :orgao_id, :unidade_id, :uf_sigla, :nome_completo, :email_login, :matricula_funcional, :password_hash, :status_usuario, NULL, NULL, NULL, NOW(), NOW())'
        );
        $statement->execute([
            'conta_id' => $data['conta_id'],
            'orgao_id' => $data['orgao_id'],
            'unidade_id' => $data['unidade_id'] ?? null,
            'uf_sigla' => $data['uf_sigla'],
            'nome_completo' => $data['nome_completo'],
            'email_login' => $data['email_login'],
            'matricula_funcional' => $data['matricula_funcional'] ?? null,
            'password_hash' => $data['password_hash'],
            'status_usuario' => $data['status_usuario'] ?? 'ATIVO',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function usuarioById(int $usuarioId): ?array
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'SELECT u.id, u.conta_id, u.orgao_id, u.unidade_id, u.uf_sigla, u.nome_completo, u.email_login, u.matricula_funcional, u.status_usuario, u.excluido_em
             FROM usuarios u
             WHERE u.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $usuarioId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function updateUsuario(int $usuarioId, array $data): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE usuarios
             SET unidade_id = :unidade_id,
                 nome_completo = :nome_completo,
                 email_login = :email_login,
                 matricula_funcional = :matricula_funcional,
                 status_usuario = :status_usuario,
                 excluido_em = NULL,
                 motivo_exclusao = NULL,
                 excluido_por_usuario_id = NULL,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $usuarioId,
            'unidade_id' => $data['unidade_id'] ?? null,
            'nome_completo' => $data['nome_completo'],
            'email_login' => $data['email_login'],
            'matricula_funcional' => $data['matricula_funcional'] ?? null,
            'status_usuario' => $data['status_usuario'],
        ]);
    }

    public function setUsuarioStatus(int $usuarioId, string $status): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE usuarios
             SET status_usuario = :status_usuario,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $usuarioId,
            'status_usuario' => $status,
        ]);
    }

    public function softDeleteUsuario(int $usuarioId, int $userId, string $reason): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE usuarios
             SET status_usuario = :status_usuario,
                 excluido_em = NOW(),
                 motivo_exclusao = :motivo_exclusao,
                 excluido_por_usuario_id = :excluido_por_usuario_id,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $usuarioId,
            'status_usuario' => 'BLOQUEADO',
            'motivo_exclusao' => $reason,
            'excluido_por_usuario_id' => $userId > 0 ? $userId : null,
        ]);
    }

    public function perfis(): array
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->query(
            'SELECT id, nome_perfil, descricao, status_perfil, created_at
             FROM perfis
             WHERE excluido_em IS NULL
             ORDER BY id DESC
             LIMIT 120'
        );

        return $statement->fetchAll();
    }

    public function perfilById(int $perfilId): ?array
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'SELECT id, nome_perfil, descricao, status_perfil, excluido_em
             FROM perfis
             WHERE id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $perfilId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function createPerfil(array $data): int
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'INSERT INTO perfis (nome_perfil, descricao, status_perfil, excluido_em, motivo_exclusao, excluido_por_usuario_id, created_at, updated_at)
             VALUES (:nome_perfil, :descricao, :status_perfil, NULL, NULL, NULL, NOW(), NOW())'
        );
        $statement->execute([
            'nome_perfil' => $data['nome_perfil'],
            'descricao' => $data['descricao'] ?? null,
            'status_perfil' => $data['status_perfil'] ?? 'ATIVO',
        ]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function updatePerfil(int $perfilId, array $data): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE perfis
             SET nome_perfil = :nome_perfil,
                 descricao = :descricao,
                 status_perfil = :status_perfil,
                 excluido_em = NULL,
                 motivo_exclusao = NULL,
                 excluido_por_usuario_id = NULL,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $perfilId,
            'nome_perfil' => $data['nome_perfil'],
            'descricao' => $data['descricao'] ?? null,
            'status_perfil' => $data['status_perfil'],
        ]);
    }

    public function setPerfilStatus(int $perfilId, string $status): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE perfis
             SET status_perfil = :status_perfil,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $perfilId,
            'status_perfil' => $status,
        ]);
    }

    public function softDeletePerfil(int $perfilId, int $userId, string $reason): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE perfis
             SET status_perfil = :status_perfil,
                 excluido_em = NOW(),
                 motivo_exclusao = :motivo_exclusao,
                 excluido_por_usuario_id = :excluido_por_usuario_id,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $perfilId,
            'status_perfil' => 'INATIVO',
            'motivo_exclusao' => $reason,
            'excluido_por_usuario_id' => $userId > 0 ? $userId : null,
        ]);
    }

    public function vinculosUsuarioPerfil(?string $ufSigla = null): array
    {
        $this->ensureInstitutionLifecycleSchema();

        $where = ['up.excluido_em IS NULL', "up.status_vinculo <> 'EXCLUIDO'", 'p.excluido_em IS NULL'];
        $params = [];
        $this->applyUfWhere('u', $ufSigla, $where, $params);
        $whereSql = $this->whereClause($where);

        $statement = $this->pdo()->prepare(
            "SELECT up.id, up.usuario_id, u.nome_completo AS usuario_nome, up.perfil_id, p.nome_perfil, up.status_vinculo, up.created_at
             FROM usuarios_perfis up
             INNER JOIN usuarios u ON u.id = up.usuario_id
             INNER JOIN perfis p ON p.id = up.perfil_id
             {$whereSql}
             ORDER BY up.id DESC
             LIMIT 300"
        );
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function vinculoById(int $vinculoId): ?array
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'SELECT up.id, up.usuario_id, up.perfil_id, up.status_vinculo, up.excluido_em,
                    u.uf_sigla, u.nome_completo, p.nome_perfil
             FROM usuarios_perfis up
             INNER JOIN usuarios u ON u.id = up.usuario_id
             INNER JOIN perfis p ON p.id = up.perfil_id
             WHERE up.id = :id
             LIMIT 1'
        );
        $statement->execute(['id' => $vinculoId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function profileIdByName(string $profileName): ?int
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'SELECT id
             FROM perfis
             WHERE nome_perfil = :nome_perfil
               AND status_perfil = \'ATIVO\'
               AND excluido_em IS NULL
             LIMIT 1'
        );
        $statement->execute([
            'nome_perfil' => trim($profileName),
        ]);

        $id = $statement->fetchColumn();
        if ($id === false) {
            return null;
        }

        $value = (int) $id;
        return $value > 0 ? $value : null;
    }

    public function vincularPerfilAoUsuario(int $usuarioId, int $perfilId): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'INSERT INTO usuarios_perfis (usuario_id, perfil_id, status_vinculo, created_at, updated_at, excluido_em, motivo_exclusao, excluido_por_usuario_id)
             VALUES (:usuario_id, :perfil_id, :status_vinculo, NOW(), NOW(), NULL, NULL, NULL)
             ON DUPLICATE KEY UPDATE
                perfil_id = VALUES(perfil_id),
                status_vinculo = VALUES(status_vinculo),
                excluido_em = NULL,
                motivo_exclusao = NULL,
                excluido_por_usuario_id = NULL,
                updated_at = NOW()'
        );
        $statement->execute([
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfilId,
            'status_vinculo' => 'ATIVO',
        ]);
    }

    public function updateVinculo(int $vinculoId, int $perfilId, string $status): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE usuarios_perfis
             SET perfil_id = :perfil_id,
                 status_vinculo = :status_vinculo,
                 excluido_em = NULL,
                 motivo_exclusao = NULL,
                 excluido_por_usuario_id = NULL,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $vinculoId,
            'perfil_id' => $perfilId,
            'status_vinculo' => $status,
        ]);
    }

    public function setVinculoStatus(int $vinculoId, string $status): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE usuarios_perfis
             SET status_vinculo = :status_vinculo,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $vinculoId,
            'status_vinculo' => $status,
        ]);
    }

    public function softDeleteVinculo(int $vinculoId, int $userId, string $reason): void
    {
        $this->ensureInstitutionLifecycleSchema();

        $statement = $this->pdo()->prepare(
            'UPDATE usuarios_perfis
             SET status_vinculo = :status_vinculo,
                 excluido_em = NOW(),
                 motivo_exclusao = :motivo_exclusao,
                 excluido_por_usuario_id = :excluido_por_usuario_id,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $vinculoId,
            'status_vinculo' => 'EXCLUIDO',
            'motivo_exclusao' => $reason,
            'excluido_por_usuario_id' => $userId > 0 ? $userId : null,
        ]);
    }

    public function contextOptions(?string $ufSigla = null): array
    {
        $this->ensureInstitutionLifecycleSchema();

        $where = ['c.excluido_em IS NULL'];
        $params = [];
        $this->applyUfWhere('c', $ufSigla, $where, $params);
        $accountsWhere = $this->whereClause($where);

        $whereOrgaos = ['o.excluido_em IS NULL'];
        $paramsOrgaos = [];
        $this->applyUfWhere('o', $ufSigla, $whereOrgaos, $paramsOrgaos);
        $orgaosWhere = $this->whereClause($whereOrgaos);

        $whereUnidades = ['u.excluido_em IS NULL'];
        $paramsUnidades = [];
        $this->applyUfWhere('u', $ufSigla, $whereUnidades, $paramsUnidades);
        $unidadesWhere = $this->whereClause($whereUnidades);

        $whereUsuarios = ['u.excluido_em IS NULL'];
        $paramsUsuarios = [];
        $this->applyUfWhere('u', $ufSigla, $whereUsuarios, $paramsUsuarios);
        $usuariosWhere = $this->whereClause($whereUsuarios);

        $ufs = [];
        if ($this->tableExists('territorios_ufs')) {
            $ufs = $this->pdo()->query(
                'SELECT sigla, nome
                 FROM territorios_ufs
                 ORDER BY nome ASC'
            )->fetchAll();
        }

        $contasStatement = $this->pdo()->prepare(
            "SELECT c.id, c.nome_fantasia, c.uf_sigla, c.status_cadastral
             FROM contas c
             {$accountsWhere}
             ORDER BY c.nome_fantasia ASC"
        );
        $contasStatement->execute($params);

        $orgaosStatement = $this->pdo()->prepare(
            "SELECT o.id, o.conta_id, o.nome_oficial, o.uf_sigla, o.status_orgao
             FROM orgaos o
             {$orgaosWhere}
             ORDER BY o.nome_oficial ASC"
        );
        $orgaosStatement->execute($paramsOrgaos);

        $unidadesStatement = $this->pdo()->prepare(
            "SELECT u.id, u.orgao_id, u.nome_unidade, u.uf_sigla, u.status_unidade
             FROM unidades u
             {$unidadesWhere}
             ORDER BY u.nome_unidade ASC"
        );
        $unidadesStatement->execute($paramsUnidades);

        $usuariosStatement = $this->pdo()->prepare(
            "SELECT u.id, u.nome_completo, u.email_login, u.uf_sigla, u.status_usuario
             FROM usuarios u
             {$usuariosWhere}
             ORDER BY u.nome_completo ASC"
        );
        $usuariosStatement->execute($paramsUsuarios);

        return [
            'ufs' => $ufs,
            'contas' => $contasStatement->fetchAll(),
            'orgaos' => $orgaosStatement->fetchAll(),
            'unidades' => $unidadesStatement->fetchAll(),
            'usuarios' => $usuariosStatement->fetchAll(),
            'perfis' => $this->pdo()->query(
                'SELECT id, nome_perfil, status_perfil FROM perfis WHERE excluido_em IS NULL ORDER BY nome_perfil ASC'
            )->fetchAll(),
        ];
    }

    private function applyUfWhere(string $alias, ?string $ufSigla, array &$where, array &$params): void
    {
        $ufSigla = $this->sanitizeUf($ufSigla);
        if ($ufSigla === null) {
            return;
        }

        $where[] = "{$alias}.uf_sigla = :uf_sigla";
        $params['uf_sigla'] = $ufSigla;
    }

    private function whereClause(array $where): string
    {
        if ($where === []) {
            return '';
        }

        return 'WHERE ' . implode(' AND ', $where);
    }

    private function sanitizeUf(?string $ufSigla): ?string
    {
        $uf = strtoupper(trim((string) $ufSigla));
        return strlen($uf) === 2 ? $uf : null;
    }

    private function tableExists(string $tableName): bool
    {
        $statement = $this->pdo()->prepare(
            'SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = :table_name'
        );
        $statement->execute(['table_name' => $tableName]);

        return ((int) $statement->fetchColumn()) > 0;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        $statement = $this->pdo()->prepare(
            'SELECT COUNT(*)
             FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = :table_name
               AND column_name = :column_name'
        );
        $statement->execute([
            'table_name' => $tableName,
            'column_name' => $columnName,
        ]);

        return ((int) $statement->fetchColumn()) > 0;
    }

    private function ensureColumn(string $tableName, string $columnName, string $alterSql): void
    {
        if ($this->columnExists($tableName, $columnName)) {
            return;
        }

        $this->pdo()->exec($alterSql);
    }

    private function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }
}
