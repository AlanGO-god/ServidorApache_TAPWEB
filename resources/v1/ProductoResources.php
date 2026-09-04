<?php
require_once __DIR__ . '/../../models/Product.php';

class ProductResource {
    private $db;
    private $product;

    public function __construct($database) {
        $this->db = $database;
        $this->product = new Product($this->db);
    }

    public function index() {
        $result = $this->product->getAll();
        http_response_code(200);
        echo json_encode(["status" => "success", "data" => $result]);
    }

    public function show($id) {
        $result = $this->product->getById($id);
        if ($result) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $result]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Producto no encontrado"]);
        }
    }

    public function store() {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->sku) && !empty($data->name) && isset($data->price)) {
            $this->product->sku = $data->sku;
            $this->product->name = $data->name;
            $this->product->description = $data->description ?? '';
            $this->product->price = $data->price;
            $this->product->stock = $data->stock ?? 0;

            $newId = $this->product->create();
            if ($newId) {
                http_response_code(201);
                echo json_encode(["status" => "success", "message" => "Producto creado exitosamente", "id" => $newId]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "No se pudo crear el producto. Verifique que el SKU sea único."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Datos incompletos. Se requiere SKU, nombre y precio."]);
        }
    }

    public function update($id) {
        $data = json_decode(file_get_contents("php://input"));
        $existing = $this->product->getById($id);

        if (!$existing) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Producto no encontrado"]);
            return;
        }

        $this->product->id = $id;
        $this->product->sku = $data->sku ?? $existing['sku'];
        $this->product->name = $data->name ?? $existing['name'];
        $this->product->description = $data->description ?? $existing['description'];
        $this->product->price = $data->price ?? $existing['price'];
        $this->product->stock = $data->stock ?? $existing['stock'];

        if ($this->product->update()) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "Producto actualizado correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Error al actualizar el producto"]);
        }
    }

    public function destroy($id) {
        if ($this->product->getById($id)) {
            if ($this->product->delete($id)) {
                http_response_code(200);
                echo json_encode(["status" => "success", "message" => "Producto eliminado"]);
            } else {
                http_response_code(500);
                echo json_encode(["status" => "error", "message" => "Error al eliminar el producto"]);
            }
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Producto no encontrado"]);
        }
    }
}
?>