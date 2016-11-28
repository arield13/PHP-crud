<?php
/** 
 * @web http://www.cimit.co
 * @author Ariel Diaz
 */
namespace Status\DB;
class Messages {
    
    protected $mysqli;
    protected $pdo;
    private $dns;
    const LOCALHOST = 'localhost';
    const USER = 'root';
    const PASSWORD = '';
    const DATABASE = 'intraway';
    /**
     * Constructor de clase
     */
    public function __construct() {           
        try{
            //conexión a base de datos
            $this->dns = "mysql:host=localhost;dbname=".self::DATABASE.";unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock";
            $this->usuario = "root";
            $this->clave = "";
            $this->pdo = new \PDO($this->dns, self::USER, self::PASSWORD);
        }catch (mysqli_sql_exception $e){
            //Si no se puede realizar la conexión
             Messages::http_response_code(500);
            exit;
        }     
    } 
    
    /**
     * obtiene un solo registro dado su ID
     * @param int $id identificador unico de registro
     * @return Array array con los registros obtenidos de la base de datos
     */
    public function getMessageID($id=0){  
        $message = array();
        if($this->checkID($id)){
            $stmt = $this->pdo->prepare("SELECT * FROM messages WHERE id = ?");
            $stmt->execute(array($id));   
            $message = $stmt->fetchAll(\PDO::FETCH_ASSOC); 
        }else{
            $this->response(200, "error", "Registration does not exist");
            exit;
        }
        
        return $message;              
    }
    
    /**
     * obtiene todos los registros de la tabla "people"
     * @return Array array con los registros obtenidos de la base de datos
     */
     public function getAllMessages(){        
        $result  = $this->pdo->query('SELECT * FROM messages ORDER BY created_at  DESC LIMIT 20');          
        $messages = $result->fetchAll(\PDO::FETCH_ASSOC);   
        return $messages; 
     }
     /**
     * obtiene todos los registros de la tabla "people"
     * @return Array array con los registros obtenidos de la base de datos
     */
     public function getMessage($parameters){
        //CUENTA EL NUMERO DE PALABRAS 
        $q = $parameters['field']['q'];
        $trozos = explode(" ",$q); 

        $limit = isset($parameters['field']['r']) && is_numeric($parameters['field']['r']) ?
                $parameters['field']['r'] : 20;
        $page = (isset($parameters['field']['p']) && is_numeric($parameters['field']['p']) ?
                (($parameters['field']['p'] > 1 ? $parameters['field']['p'] -1 : $parameters['field']['p']) *
                ($parameters['field']['p'] > 1 ? $limit : 1 )  ) : 1);
        $page_url = (isset($parameters['field']['p']) && is_numeric($parameters['field']['p']) ?
                ($parameters['field']['p'] + 1 ) : 2);
        
        
        $total = count($trozos); 
        $messages = array();
      
        if ($total >= 1 && count($q) <= 3) { 
            //SI SOLO HAY UNA PALABRA DE BUSQUEDA SE ESTABLECE UNA INSTRUCION CON LIKE 
            $stmt = $this->pdo->prepare("SELECT * FROM messages WHERE status LIKE ?  
                ORDER BY created_at  DESC LIMIT ".$page." , ".$limit."");
            $string = "%$q%";
            $stmt->execute(array($string));
            $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);        
        } else { 
            //SI HAY UNA FRASE SE UTILIZA EL ALGORTIMO DE BUSQUEDA AVANZADO DE MATCH AGAINST 
            $result = $this->pdo->query('SELECT * FROM messages
                                            WHERE 
                                                MATCH(status) against("'.$q.'" IN BOOLEAN MODE)
                                            ORDER BY created_at  DESC LIMIT '.$page.' ,  '.$limit.'');          
            $messages = $result->fetchAll(\PDO::FETCH_ASSOC);          
        } 
        $next = count($messages) == $limit ? 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' .
                $_SERVER['HTTP_HOST'].'/intraway/v1/messages?q='.$q.'&p='.$page_url.'&r='.$limit :
                "No data";
        
        $messages[] = array('next' => $next);
      
        return $messages;
    }
    /**
     * añade un nuevo registro en la tabla persona
     * @param String $name nombre completo de persona
     * @return bool TRUE|FALSE 
     */
    public function insert($email='', $message = '' ){
        $stmt = $this->pdo->prepare("INSERT INTO messages(email,status) VALUES (?,?); ");
         
        if(!$stmt->execute(array($email,$message))){
            $logger = new \PHPErrorLog();
            $error = $stmt->errorInfo();
            $logger->write($error[2],3,'ERROR QUERY');
            print_r($stmt->errorInfo());
            exit;
            
        }
        $this->response(200, "success", "new record added"); 
        exit;
    }
    
    /**
     * elimina un registro dado el ID
     * @param int $id Identificador unico de registro
     * @return Bool TRUE|FALSE
     */
    public function delete($id=0) {
        $r = array();
        if($this->checkID($id)){
            $stmt = $this->pdo->prepare("DELETE FROM messages WHERE id = :id ; ");
            if($r = !$stmt->execute(array(':id' => $id))){
                $logger = new \PHPErrorLog();
                $error = $stmt->errorInfo();
                $logger->write($error[2],3,'ERROR QUERY');
                $this->response(200, "error", $error[2]);
                exit;
            }
        }else{
            Messages::response(200, "error", "Registration does not exist");
            exit;
        }
        return $r;
    }
        
    /**
     * verifica si un ID existe
     * @param int $id Identificador unico de registro
     * @return Bool TRUE|FALSE
     */
    public function checkID($id){
        $stmt = $this->pdo->prepare("SELECT * FROM messages WHERE id=?");
        if($stmt->execute(array($id))){
            $dat = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            if (!empty($dat[0]['id'])){                
                return true;
            }
        }        
        return false;
    }
    /**
     * Respuesta al cliente
     * @param int $code Codigo de respuesta HTTP
     * @param String $status indica el estado de la respuesta puede ser "success" o "error"
     * @param String $message Descripcion de lo ocurrido
     */
    public function response($code = 200, $status = "", $message = "") {
        Messages::http_response_code($code);
        if (!empty($status) && !empty($message)) {
            $response[] = array("status" => $status, "message" => $message);
            print_r(json_encode($response));
        }
    }
    public function http_response_code($code = NULL) {

        if ($code !== NULL) {

            switch ($code) {
                case 100: $text = 'Continue';
                    break;
                case 101: $text = 'Switching Protocols';
                    break;
                case 200: $text = 'OK';
                    break;
                case 201: $text = 'Created';
                    break;
                case 202: $text = 'Accepted';
                    break;
                case 203: $text = 'Non-Authoritative Information';
                    break;
                case 204: $text = 'No Content';
                    break;
                case 205: $text = 'Reset Content';
                    break;
                case 206: $text = 'Partial Content';
                    break;
                case 300: $text = 'Multiple Choices';
                    break;
                case 301: $text = 'Moved Permanently';
                    break;
                case 302: $text = 'Moved Temporarily';
                    break;
                case 303: $text = 'See Other';
                    break;
                case 304: $text = 'Not Modified';
                    break;
                case 305: $text = 'Use Proxy';
                    break;
                case 400: $text = 'Bad Request';
                    break;
                case 401: $text = 'Unauthorized';
                    break;
                case 402: $text = 'Payment Required';
                    break;
                case 403: $text = 'Forbidden';
                    break;
                case 404: $text = 'Not Found';
                    break;
                case 405: $text = 'Method Not Allowed';
                    break;
                case 406: $text = 'Not Acceptable';
                    break;
                case 407: $text = 'Proxy Authentication Required';
                    break;
                case 408: $text = 'Request Time-out';
                    break;
                case 409: $text = 'Conflict';
                    break;
                case 410: $text = 'Gone';
                    break;
                case 411: $text = 'Length Required';
                    break;
                case 412: $text = 'Precondition Failed';
                    break;
                case 413: $text = 'Request Entity Too Large';
                    break;
                case 414: $text = 'Request-URI Too Large';
                    break;
                case 415: $text = 'Unsupported Media Type';
                    break;
                case 422: $text = 'Validation errors saving data';
                    break;
                case 500: $text = 'Internal Server Error';
                    break;
                case 501: $text = 'Not Implemented';
                    break;
                case 502: $text = 'Bad Gateway';
                    break;
                case 503: $text = 'Service Unavailable';
                    break;
                case 504: $text = 'Gateway Time-out';
                    break;
                case 505: $text = 'HTTP Version not supported';
                    break;
                default:
                    exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
            }

            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

            header($protocol . ' ' . $code . ' ' . $text);
            $response[] = array("status" => $code, "message" => $text);
           // print_r(json_encode($response));

            $GLOBALS['http_response_code'] = $code;
        } else {

            $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
        }

        return $code;
    }
    
}