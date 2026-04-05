<?php

declare(strict_types=1);

namespace App\Repositories\Auth;

use App\Support\Database;
use PDO;

final class UserRepository
{
    public function __construct(private readonly ?PDO $connection = null)
    {
    }

    public function findByLogin(string $login): ?array
    {
        $sql = 'SELECT u.id, u.conta_id, u.orgao_id, u.unidade_id, u.uf_sigla, u.nome_completo, u.email_login, u.password_hash, u.status_usuario,
                       c.status_cadastral AS status_conta, c.uf_sigla AS conta_uf_sigla, o.status_orgao, o.uf_sigla AS orgao_uf_sigla
                FROM usuarios u
                INNER JOIN contas c ON c.id = u.conta_id
                INNER JOIN orgaos o ON o.id = u.orgao_id
                WHERE u.email_login = :login
                LIMIT 1';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute(['login' => $login]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function findById(int $userId): ?array
    {
        $sql = 'SELECT u.id, u.conta_id, u.orgao_id, u.unidade_id, u.uf_sigla, u.nome_completo, u.email_login, u.status_usuario,
                       c.status_cadastral AS status_conta, c.uf_sigla AS conta_uf_sigla, o.status_orgao, o.uf_sigla AS orgao_uf_sigla
                FROM usuarios u
                INNER JOIN contas c ON c.id = u.conta_id
                INNER JOIN orgaos o ON o.id = u.orgao_id
                WHERE u.id = :id
                LIMIT 1';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute(['id' => $userId]);
        $row = $statement->fetch();

        return $row !== false ? $row : null;
    }

    public function profileCodes(int $userId): array
    {
        $extraFilter = '';
        if ($this->columnExists('usuarios_perfis', 'status_vinculo')) {
            $extraFilter = ' AND up.status_vinculo = \'ATIVO\'';
        }

        $sql = 'SELECT p.nome_perfil
                FROM usuarios_perfis up
                INNER JOIN perfis p ON p.id = up.perfil_id
                WHERE up.usuario_id = :usuario_id
                  AND p.status_perfil = \'ATIVO\'' . $extraFilter;

        $statement = $this->pdo()->prepare($sql);
        $statement->execute(['usuario_id' => $userId]);
        $rows = $statement->fetchAll();

        return array_map(static fn(array $row): string => (string) $row['nome_perfil'], $rows);
    }

    public function scopeCodes(int $userId): array
    {
        if (!$this->tableExists('usuarios_escopos')) {
            return ['PROPRIO_ORGAO'];
        }

        $sql = 'SELECT ue.escopo_acesso
                FROM usuarios_escopos ue
                WHERE ue.usuario_id = :usuario_id
                  AND ue.status_escopo = \'ATIVO\'
                ORDER BY ue.id ASC';

        $statement = $this->pdo()->prepare($sql);
        $statement->execute(['usuario_id' => $userId]);
        $rows = $statement->fetchAll();

        $scopes = array_map(static fn(array $row): string => (string) $row['escopo_acesso'], $rows);

        return $scopes !== [] ? $scopes : ['PROPRIO_ORGAO'];
    }

    public function touchLastAccess(int $userId): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE usuarios SET ultimo_acesso_em = NOW(), updated_at = NOW() WHERE id = :id'
        );
        $statement->execute(['id' => $userId]);
    }

    public function updatePasswordById(int $userId, string $passwordHash): void
    {
        $statement = $this->pdo()->prepare(
            'UPDATE usuarios
             SET password_hash = :password_hash, updated_at = NOW()
             WHERE id = :id'
        );
        $statement->execute([
            'password_hash' => $passwordHash,
            'id' => $userId,
        ]);
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

    private function pdo(): PDO
    {
        return $this->connection ?? Database::connection();
    }
}
