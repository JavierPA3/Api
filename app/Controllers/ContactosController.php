<?php
namespace App\Controllers;

use App\Models\Contactos;

class ContactosController 
{
    private $requestMethod; 
    private $contactosId; 
    private $contactos;
    public function __construct($requestMethod, $contactosId) 
    {
        $this->requestMethod = $requestMethod;
        $this->contactosId = $contactosId;
        $this->contactos = contactos::getInstancia();
    }


    public function processRequest() 
    {
        switch($this->requestMethod) {
            case 'GET':
                if ($this->contactosId) { 
                    $response = $this->getContactos($this->contactosId);
                } else {
                    $response = $this->getAllContactos();
                };
                break;
            case 'POST':
                $response = $this->createContactosFromRequest();
                break;
            case 'PUT':
                $response = $this->updateContactosFromRequest($this->contactosId);
                break;
            case 'DELETE':
                if ($this->contactosId) { 
                    $response = $this->deleteContactos($this->contactosId);
                };
                break;
            default:
            $response = $this->notFoundResponse();
            break;
        }
        header($response['status_code_header']); 
        if ($response['body']) {
            echo $response['body'];
        }
    }
    private function getContactos($id) 
    {
        $result = $this->contactos->get($id);
        if (!$result) { // si no lo encuentra, mensaje de error
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

  
    private function notFoundResponse() : array
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = "No se encontró nada con ese id"; // null era el valor que estaba antes
        return $response;
    }


    private function getAllContactos() : array
    {
        $result = $this->contactos->getAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createContactosFromRequest() 
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (!$this->validateContacto($input)) {
            return $this->unprocessableEntityResponse();
        }
        $this->contactos->set($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $response['body'] = json_encode(array("mensaje"=>"Contacto creado"));
        return $response;
    }

    private function updateContactosFromRequest($id) 
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        $result = $this->contactos->get($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $this->contactos->edit($id, $input);
        $response['status_code_header'] = 'HTTP/1.1 200 Ok';
        $response['body'] = json_encode($input);
        return $response;
    }

    private function deleteContactos($id) 
    {
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        $result = $this->contactos->get($id);
        if (!$result) {
            return $this->notFoundResponse();
        }
        $this->contactos->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 201 Deleted';
        $response['body'] = json_encode(array("mensaje"=>"Contacto eliminado"));
        return $response;
    }

    private function validateContacto($input)
    {
        if (!isset($input['nombre'])) {
            return false;
        }
        if (!isset($input['telefono'])) {
            return false;
        }
        return true;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid onput'
        ]);
        return $response;
    }

}



