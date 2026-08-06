<?php
class AuthController {
    private $db;
    private $user;

    public function __construct() {
        include_once 'config/database.php';
        include_once 'models/User.php';
        include_once 'helpers/JWT.php';

        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        // Fallback jika json_decode gagal atau request menggunakan x-www-form-urlencoded
        $username = isset($data->username) ? $data->username : (isset($_POST['username']) ? $_POST['username'] : '');
        $password = isset($data->password) ? $data->password : (isset($_POST['password']) ? $_POST['password'] : '');

        error_log("LOGIN ATTEMPT - Username: '" . $username . "', Password: '" . $password . "'");

        if(!empty($username) && !empty($password)) {
            if($this->user->login($username, $password)) {
                
                // Generate Payload
                $payload = [
                    'id' => $this->user->id,
                    'username' => $this->user->username,
                    'nama_lengkap' => $this->user->nama_lengkap,
                    'role' => $this->user->role,
                    'exp' => time() + (60 * 60 * 24) // Expired dalam 24 jam
                ];

                // Encode jadi JWT
                $jwt = JWT::encode($payload);

                http_response_code(200);
                echo json_encode([
                    "message" => "Login sukses.",
                    "token" => $jwt,
                    "user" => [
                        "username" => $this->user->username,
                        "nama_lengkap" => $this->user->nama_lengkap,
                        "role" => $this->user->role
                    ]
                ]);
            } else {
                http_response_code(401);
                $debugMsg = "Login gagal. Debug: U='" . $username . "' P='" . $password . "' HASH='" . md5($password) . "'";
                echo json_encode(["message" => $debugMsg]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Username dan Password tidak boleh kosong. U='" . $username . "'"]);
        }
    }
}
?>
