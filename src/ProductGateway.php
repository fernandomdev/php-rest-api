<?php

class ProductGateway{

    // este bloque es para hacer que el $this->conn se aplique a todas los métodos
    private PDO $conn;
    public function __construct(Database $database){
        $this->conn = $database->getConnection();
    }

    public function getAll(): array{
        $sql = "SELECT * FROM products";
        $stmt = $this->conn->query($sql);
        $data = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $data[] = $row;
        }
        return $data;
    }

    public function create(array $data){

        $sql = "INSERT INTO products (prd_nombre, prd_cantidad, prd_disponible) 
                VALUES (:descripcion, :cantidad, :disponible)
        ";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":descripcion", $data['descripcion'], PDO::PARAM_STR);
        $stmt->bindValue(":cantidad", $data['cantidad'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(":disponible", $data['disponible'] ?? 0, PDO::PARAM_INT);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    public function update(array $current, array $new): int{

        $sql = "UPDATE products SET prd_nombre = :descripcion, prd_cantidad = :cantidad, prd_disponible = :disponible WHERE prd_id = :id";
        $stmt = $this->conn->prepare($sql);

        // esto se hace así porque es el método PATCH, si era PUT no se le hacía el IF ternario
        $stmt->bindValue(":descripcion", $new['descripcion'] ?? $current['prd_nombre'], PDO::PARAM_STR);
        $stmt->bindValue(":cantidad", $new['cantidad'] ?? $current['prd_cantidad'], PDO::PARAM_INT);
        $stmt->bindValue(":disponible", $new['disponible'] ?? $current['prd_disponible'], PDO::PARAM_INT);

        $stmt->bindValue(":id", $current['prd_id'], PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->rowCount();
    }

    public function get(string $id): array | false{

        $sql = "SELECT * FROM products WHERE prd_id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data;
    }

    public function delete(string $id): int{

        $sql = "DELETE FROM products WHERE prd_id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        $stmt->execute();
        
        return $stmt->rowCount();
    }
}
