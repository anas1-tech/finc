<?php
session_start();
require 'db.php';

header('Content-Type: application/json; charset=utf-8');
$data = json_decode(file_get_contents("php://input"), true);
$action = isset($_GET['action']) ? $_GET['action'] : ($data['action'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $user = trim($data['username']);
        $pass = $data['password'];
        if (empty($user) || empty($pass)) die(json_encode(["status" => "error", "message" => "أدخل البيانات المطلوبة"]));
        
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        try {
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$user, $hashed]);
            echo json_encode(["status" => "success", "message" => "تم إنشاء الحساب! سجل دخولك الآن."]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) echo json_encode(["status" => "error", "message" => "اسم المستخدم موجود مسبقاً!"]);
            else echo json_encode(["status" => "error", "message" => "خطأ: " . $e->getMessage()]);
        }
    } 
    elseif ($action === 'login') {
        $user = trim($data['username']);
        $pass = $data['password'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$user]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "اسم المستخدم أو كلمة المرور غير صحيحة!"]);
        }
    } 
    elseif ($action === 'logout') {
        session_destroy();
        echo json_encode(["status" => "success"]);
    }
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'check') {
    if (isset($_SESSION['user_id'])) echo json_encode(["status" => "logged_in", "username" => $_SESSION['username']]);
    else echo json_encode(["status" => "not_logged_in"]);
}
?>