<?php

class ProductController{

    public function __construct(private ProductGateway $gateway){

    }

    public function processRequest(string $method, ?string $id) : void {

        if ($id) {
            //acá trabaja con sólo un resource
            $this->processResourceRequest($method, $id);

        } else {
            // aca trabaja con una collection de datos
            $this->processCollectionRequest($method);
        }   

    }

    private function processResourceRequest(string $method, string $id) : void{

        $product = $this->gateway->get($id);

        if (!$product) {
            http_response_code(404);
            echo json_encode(["message" => "Producto no encontrado"]);
            return;
        }

        switch ($method) {
            case 'GET':
                echo json_encode($product);
                break;

            case 'PATCH': // PATCH se utiliza para modificar parcialmente, PUT para modificar completamente
                
                // se le pone (array) para la comprobación de si es NULL
                $data = (array) json_decode(file_get_contents("php://input"), true);       

                $errors = $this->getValidationErrors($data, false);

                if ( ! empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $rows = $this->gateway->update($product, $data);

                echo json_encode([
                    "message" => "Registro con ID $id actualizado correctamente!",
                    "rows" => $rows
                ]);
                break;

            case 'DELETE':
                $rows = $this->gateway->delete($id);
                echo json_encode([
                    "message" => "Registro con ID $id eliminado correctamente!",
                    "rows" => $rows
                ]);
                break;

            default: // en caso de que utilicen un método que no tenemos con ID 
                http_response_code(405);
                header("Allow: GET, PATCH, DELETE");
                break;
        }
    }

    private function processCollectionRequest(string $method) {
        switch ($method) {
            case 'GET': //listar
                echo json_encode($this->gateway->getAll());
                break;

            case 'POST': //crear
                // se le pone (array) para la comprobación de si es NULL
                $data = (array) json_decode(file_get_contents("php://input"), true);       

                $errors = $this->getValidationErrors($data);

                if ( ! empty($errors)) {
                    http_response_code(422);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $id = $this->gateway->create($data);

                // usar este response al crear
                http_response_code(201); 

                echo json_encode([
                    "message" => "Creado correctamente!",
                    "id" => $id
                ]);
                break;

            default: // en caso de que utilicen el método delete con la URL "http://localhost/products" osea sin ID
                http_response_code(405);
                header("Allow: GET, POST");
                break;
        }
    }

    private function getValidationErrors(array $data, bool $is_new = true): array{
        $errors = [];

        // validar si no está vacío
        if ($is_new && ($data["descripcion"])) {
            $errors[] = "Falta el parámetro descripcion";
        }

        // validar si es entero
        if(array_key_exists("cantidad", $data)){
            if (filter_var($data["cantidad"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "La cantidad debe ser de tipo entero";
            }
        }

        return $errors;
    }
}