<?php

declare(strict_types=1);

class SupplierRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $sql = '
            INSERT INTO suppliers (
                name,
                email,
                phone,
                address,
                contact_person
            )
            VALUES (
                :name,
                :email,
                :phone,
                :address,
                :contact_person
            )
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'contact_person' => $data['contact_person'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function findAll(): array
    {
        $sql = '
            SELECT
                id,
                name,
                email,
                phone,
                address,
                contact_person,
                created_at
            FROM suppliers
            ORDER BY id
        ';

        $statement = $this->pdo->query($sql);

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $sql = '
            SELECT
                id,
                name,
                email,
                phone,
                address,
                contact_person,
                created_at
            FROM suppliers
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        $statement->execute([
            'id' => $id,
        ]);

        $supplier = $statement->fetch();

        return $supplier === false ? null : $supplier;
    }

    public function update(int $id, array $data): bool
    {
        $sql = '
            UPDATE suppliers
            SET
                name = :name,
                email = :email,
                phone = :phone,
                address = :address,
                contact_person = :contact_person
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'contact_person' => $data['contact_person'],
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $sql = '
            DELETE FROM suppliers
            WHERE id = :id
        ';

        $statement = $this->pdo->prepare($sql);

        return $statement->execute([
            'id' => $id,
        ]);
    }
}