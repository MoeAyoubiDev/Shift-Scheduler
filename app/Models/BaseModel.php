<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/config.php';

class BaseModel
{
    protected string $table = '';
    protected array $fillable = [];

    public function all(): array
    {
        $stmt = db()->query(sprintf('SELECT * FROM %s', $this->table));
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = db()->prepare(sprintf('SELECT * FROM %s WHERE id = :id LIMIT 1', $this->table));
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function where(string $column, mixed $value): array
    {
        $stmt = db()->prepare(sprintf('SELECT * FROM %s WHERE %s = :value', $this->table, $column));
        $stmt->execute(['value' => $value]);
        return $stmt->fetchAll();
    }

    public function firstWhere(string $column, mixed $value): ?array
    {
        $stmt = db()->prepare(sprintf('SELECT * FROM %s WHERE %s = :value LIMIT 1', $this->table, $column));
        $stmt->execute(['value' => $value]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $data = $this->filterFillable($data);

        if (!$data) {
            throw new InvalidArgumentException('No fillable fields provided for insert.');
        }

        $columns = array_keys($data);
        $placeholders = array_map(static fn(string $column) => ':' . $column, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = db()->prepare($sql);
        $stmt->execute($data);

        return (int) db()->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);

        if (!$data) {
            throw new InvalidArgumentException('No fillable fields provided for update.');
        }

        $assignments = [];
        foreach ($data as $column => $value) {
            $assignments[] = sprintf('%s = :%s', $column, $column);
        }

        $data['id'] = $id;

        $sql = sprintf('UPDATE %s SET %s WHERE id = :id', $this->table, implode(', ', $assignments));
        $stmt = db()->prepare($sql);

        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = db()->prepare(sprintf('DELETE FROM %s WHERE id = :id', $this->table));
        return $stmt->execute(['id' => $id]);
    }

    protected function filterFillable(array $data): array
    {
        if (!$this->fillable) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }
}
