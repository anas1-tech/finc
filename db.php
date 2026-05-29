<?php
// إعدادات قاعدة بيانات Supabase (PostgreSQL)
$host = 'db.scqkhbfezttqawsgvage.supabase.co';
$port = '5432';
$dbname = 'postgres';
$username = 'postgres';
$password = 'HdCgphSFvUpQCVhu';

try {
    // استخدمنا pgsql بدلاً من mysql
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch(PDOException $e) {
    die(json_encode(["status" => "error", "message" => "فشل الاتصال بـ Supabase: " . $e->getMessage()]));
}
?>
