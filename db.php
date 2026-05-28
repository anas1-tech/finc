<?php
// إعدادات قاعدة البيانات في الاستضافة
$host = 'localhost'; // غالباً تبقى localhost في معظم الاستضافات
$dbname = 'اسم_قاعدة_البيانات_هنا'; 
$username = 'اسم_مستخدم_قاعدة_البيانات_هنا';
$password = 'الرقم_السري_لقاعدة_البيانات';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(["status" => "error", "message" => "فشل الاتصال بقاعدة البيانات! يرجى التحقق من db.php"]));
}
?>