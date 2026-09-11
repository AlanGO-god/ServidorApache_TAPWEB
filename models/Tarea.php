<?php
class Tarea
{
    private $conn;
    private $table = 'tareas';

    public function __construct($db) { $this->conn = $db; }

    public function all()
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} ORDER BY id DESC");
        $stmt->execute();
        return $stmt;
    }

    public function find($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create($titulo, $completada)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO {$this->table} (titulo, completada) VALUES (:titulo, :completada)"
        );
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':completada', $completada, PDO::PARAM_BOOL);
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function update($id, $titulo, $completada)
    {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET titulo = :titulo, completada = :completada WHERE id = :id"
        );
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':completada', $completada, PDO::PARAM_BOOL);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>