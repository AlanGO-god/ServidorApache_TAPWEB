<?php
require_once '../config/database.php';
require_once '../models/PasswordPolicy.php';
require_once '../core/services/PasswordGenerator.php';
require_once '../core/services/PasswordValidator.php';

class PasswordResource
{
    private $db;
    public function __construct() { $this->db = (new Database())->getConnection(); }

    // POST /passwords/generate
    public function generate()
    {
        header("Content-Type: application/json");
        $raw = file_get_contents("php://input");
        $data = $raw ? (json_decode($raw, true) ?? []) : [];

        try {
            $passwords = PasswordGenerator::generate($data);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode([
                "error" => "invalid_parameters",
                "message" => "Debe habilitar al menos un tipo de carácter (mayúsculas, minúsculas, números o símbolos) y respetar los rangos de longitud/cantidad."
            ]);
            return;
        } catch (RuntimeException $e) {
            http_response_code(422);
            echo json_encode([
                "error" => "unprocessable_combination",
                "message" => "La combinación de parámetros no permite generar una contraseña válida."
            ]);
            return;
        }

        $criteria = array_merge([
            "length" => 16, "includeUppercase" => true, "includeLowercase" => true,
            "includeNumbers" => true, "includeSymbols" => true,
            "excludeSimilarCharacters" => false, "excludeAmbiguousSymbols" => false,
            "count" => 1
        ], $data);

        http_response_code(200);
        echo json_encode(["passwords" => $passwords, "criteria" => $criteria]);
    }

    // POST /passwords/validate
    public function validate()
    {
        header("Content-Type: application/json");
        $data = json_decode(file_get_contents("php://input"));

        if (empty($data->password)) {
            http_response_code(400);
            echo json_encode(["error" => "invalid_request", "message" => "El campo 'password' es obligatorio"]);
            return;
        }

        $policy = (new PasswordPolicy($this->db))->get();
        $result = PasswordValidator::validate($data->password, $policy, $data->username ?? null);

        http_response_code(200);
        echo json_encode($result);
    }

    // GET /passwords/policy
    public function policy()
    {
        header("Content-Type: application/json");
        $policy = (new PasswordPolicy($this->db))->get();
        http_response_code(200);
        echo json_encode($policy);
    }
}
?>