<?php

/**
 * @author Ariel Diaz
 */
require_once 'MessagesDB.php';
class StatusAPI extends Status\DB\Messages {

    public function API() {
        header('Content-Type: application/JSON');
        $method = $_SERVER['REQUEST_METHOD'];
        
        switch ($method) {
            case 'GET'://query
                StatusAPI::getMessages();
                break;
            case 'POST'://insert
                StatusAPI::publishMessage();
                break;
            case 'PUT'://update
                //StatusAPI::updateMessage();
                break;
            case 'DELETE'://delete
                StatusAPI::deleteMessage();
                break;
            default: //metodo NO soportado
                StatusAPI::response(404);
                break;
        }
    }

    public function __construct() {
        @set_exception_handler(array($this, 'exception_handler'));
        throw new Exception('DOH!!');
    }

    public function exception_handler($exception) {
        print "Exception Caught: ". $exception->getMessage() ."\n";
        $logger = new PHPErrorLog();
        $logger->write($exception->getMessage(),2,'AKKKKK');
    }
    /**
     *  Function that according to the value of "action" e "id":
     *  - show a array with all messages 
     *  - show a only array messages 
     *  - show a array empty
     */
    public function getMessages() {
        $parameters = StatusAPI::getParameters();
        if ($_GET['action'] == 'messages') {
            $db = new \Status\DB\Messages();
            if (isset($parameters['field']['q']) || isset($parameters['field']['id'])) {//show a only array messages ID                
                 if(isset($parameters['field']['id'])){ 
                     if(is_numeric($parameters['field']['id'])){
                        $response = $db->getMessageID($parameters['field']['id']);
                     }else{
                         StatusAPI::response(422, "error", "The id value is incorrect");
                         exit;
                     }
                 }else{   
                    $response = $db->getMessage($parameters);
                 }
                 print_r(json_encode($response));
            } else { //show a array with all messages  
                if(isset($_GET['id'])){
                    if(is_numeric($_GET['id'])){ 
                      $response = $db->getMessageID($_GET['id']);
                    }else{
                      StatusAPI::response(422, "error", "The id value is incorrect"); 
                      exit;
                    }
                }else{                   
                    $response = $db->getAllMessages();
                }
                print_r(json_encode($response));
            }
        } else {
            StatusAPI::response(400,"error", "Bad request");
        }
    }

    /**
     * Method for save a new message
     */
    public function publishMessage() {
        if ($_GET['action'] == 'publishMessage') {
            //Decodifica un string de JSON
            $obj = json_decode(file_get_contents('php://input'));
            $objArr = (array) $obj;
            if (empty($objArr)) {
                StatusAPI::response(422, "error", "Nothing to add. Check json");
            } else if (isset($obj->email) && !empty($obj->email) &&
                isset($obj->status) && !empty($obj->status) &&
                filter_var($obj->email, FILTER_VALIDATE_EMAIL) &&
                strlen($obj->status) <= 120 ) {
                $status = new \Status\DB\Messages();
                $status->insert($obj->email, $obj->status);
            } else {
                $errors  = array();
                if(!isset($obj->email)){
                    $errors['mail'] = "The email property is not defined  ";
                }
                if(!isset($obj->status)){
                    $errors['status'] = "The status property is not defined  ";
                }
                if(trim($obj->status) === ""){
                    $errors['status_field'] = "The status property is can not  empty ";
                }
                if(trim($obj->email) === ""){
                    $errors['mail_field'] = "The email property is can not  empty";
                }
                if(!filter_var($obj->email, FILTER_VALIDATE_EMAIL)){
                    $errors['mail_value'] = "The email value is incorrect";
                }
                if (strlen($obj->status) > 120) {
                     $errors['status_length'] = "the statust field has more than 120 characters";
                }
                StatusAPI::response(422, "error", $errors);
            }
        } else {
            StatusAPI::response(400);
        }
    }

    /**
     * delete message
     */
    public function deleteMessage() {
        $parameters = StatusAPI::getParameters();
        if (isset($parameters['field']['id'])) {//delete a register  if isset                
            if(is_numeric($parameters['field']['id'])){
                $db = new \Status\DB\Messages();
                $db->delete($parameters['field']['id']);
                StatusAPI::response(204,'success','Record '.$parameters['field']['id'].' delete');
                exit;
            }else{
                StatusAPI::response(422, "error", "The id value is incorrect");
                exit;
            }         
        } else if (isset($_GET['action']) && isset($_GET['id'])) {
            if ($_GET['action'] == 'messages') {
                $db = new \Status\DB\Messages();
                $db->delete($_GET['id']);
                StatusAPI::response(204,'success','Record '.$_GET['id'].' delete');
                exit;
            }
        }
        StatusAPI::response(400);
    }

    public function getParameters() {

        $field = array();
        $urlArr = parse_url($_SERVER['REQUEST_URI']);
        if (isset($urlArr['query'])) {
            parse_str($urlArr['query'], $field);
        }
        return compact('field');
    }

}

//end class


