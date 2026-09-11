<?php
require_once '../config/database.php';
require_once '../models/Tarea.php';

class TareaResource
{
    private $db;
    public function __construct() { $this->db = (new Database())->getConnection(); }

    // GET /tareas
    public function index()
    {
        header("Content-Type: application/json");
        $tarea = new Tarea($this->db);
        $stmt = $tarea->all();
        $tareas = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tareas[] = $this->format($row);
        }
        http_response_code(200);
        echo json_encode($tareas);
    }

    // GET /tareas/{id}
    public function show($id)
    {
        header("Content-Type: application/json");
        $tarea = new Tarea($this->db);
        $row = $tarea->find($id);
        if (!$row) {
            http_response_code(404);
            echo json_encode(["message" => "Tarea no encontrada"]);
            return;
        }
        http_response_code(200);
        echo json_encode($this->format($row));
    }

    // POST /tareas
    public function store()
    {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->titulo)) {
            http_response_code(400);
            echo json_encode(["message" => "El campo 'titulo' es obligatorio"]);
            return;
        }

        $completada = isset($data->completada) ? (bool)$data->completada : false;

        $tarea = new Tarea($this->db);
        $id = $tarea->create($data->titulo, $completada);
        $row = $tarea->find($id);

        http_response_code(201);
        echo json_encode($this->format($row));
    }

    // PUT /tareas/{id}
    public function update($id)
    {
        header("Content-Type: application/json");
        $tarea = new Tarea($this->db);
        $existing = $tarea->find($id);

        if (!$existing) {
            http_response_code(404);
            echo json_encode(["message" => "Tarea no encontrada"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));
        $titulo = $data->titulo ?? $existing['titulo'];
        $completada = isset($data->completada) ? (bool)$data->completada : (bool)$existing['completada'];

        $tarea->update($id, $titulo, $completada);
        $row = $tarea->find($id);

        http_response_code(200);
        echo json_encode($this->format($row));
    }

    private function format(array $row): array
    {
        return [
            "id" => (int)$row['id'],
            "titulo" => $row['titulo'],
            "completada" => (bool)$row['completada'],
            "fecha_creacion" => str_replace(' ', 'T', $row['fecha_creacion']) . 'Z'
        ];
    }
}
?>