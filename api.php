<?php
session_start();
require 'db.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["status" => "error", "message" => "يجب تسجيل الدخول"]));
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$action = isset($_GET['action']) ? $_GET['action'] : ($data['action'] ?? '');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_data') {
        $stmt = $conn->prepare("SELECT monthly_budget, salary_day FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare("SELECT * FROM debts WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $debts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $toMe = []; $otherDebts = [];
        foreach ($debts as $d) {
            $d['is_paid'] = (bool)$d['is_paid'];
            if ($d['type'] === 'toMe') $toMe[] = $d; else $otherDebts[] = $d;
        }

        $stmt = $conn->prepare("SELECT * FROM car_installments WHERE user_id = ? ORDER BY due_date ASC");
        $stmt->execute([$user_id]);
        $car = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($car as &$c) $c['is_paid'] = (bool)$c['is_paid'];

        $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$user_id]);
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $conn->prepare("SELECT * FROM fixed_bills WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(["status" => "success", "settings" => $settings, "debts" => ["toMe" => $toMe, "otherDebts" => $otherDebts, "car" => $car], "expenses" => $expenses, "bills" => $bills]);
    }
    
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($action === 'add_debt') {
            if ($data['type'] === 'car') {
                $stmt = $conn->prepare("INSERT INTO car_installments (user_id, name, amount, delay_reason, due_date) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $data['name'], $data['amount'], $data['reason'] ?? '', $data['due_date'] ?? '']);
            } else {
                $stmt = $conn->prepare("INSERT INTO debts (user_id, type, name, reason, debt_date, amount) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $data['type'], $data['name'], $data['reason'] ?? '', $data['date'] ?? '', $data['amount']]);
            }
            echo json_encode(["status" => "success"]);
        }
        elseif ($action === 'add_expense') {
            $stmt = $conn->prepare("INSERT INTO expenses (user_id, name, amount, expense_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $data['name'], $data['amount'], $data['date']]);
            echo json_encode(["status" => "success"]);
        }
        elseif ($action === 'add_bill') {
            $stmt = $conn->prepare("INSERT INTO fixed_bills (user_id, name, amount) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $data['name'], $data['amount']]);
            echo json_encode(["status" => "success"]);
        }
        elseif ($action === 'toggle_paid') {
            $table = ($data['type'] === 'car') ? 'car_installments' : 'debts';
            $stmt = $conn->prepare("UPDATE $table SET is_paid = NOT is_paid WHERE id = ? AND user_id = ?");
            $stmt->execute([$data['id'], $user_id]);
            echo json_encode(["status" => "success"]);
        }
        elseif ($action === 'delete_item') {
            $t = ''; if (in_array($data['type'], ['toMe', 'otherDebts'])) $t = 'debts'; elseif ($data['type'] === 'car') $t = 'car_installments'; elseif ($data['type'] === 'bill') $t = 'fixed_bills'; elseif ($data['type'] === 'expense') $t = 'expenses';
            $stmt = $conn->prepare("DELETE FROM $t WHERE id = ? AND user_id = ?");
            $stmt->execute([$data['id'], $user_id]);
            echo json_encode(["status" => "success"]);
        }
        elseif ($action === 'edit_item') {
            if (in_array($data['type'], ['toMe', 'otherDebts'])) {
                $stmt = $conn->prepare("UPDATE debts SET name=?, amount=?, reason=? WHERE id=? AND user_id=?");
                $stmt->execute([$data['name'], $data['amount'], $data['reason'], $data['id'], $user_id]);
            } elseif ($data['type'] === 'car') {
                $stmt = $conn->prepare("UPDATE car_installments SET name=?, amount=?, delay_reason=?, due_date=? WHERE id=? AND user_id=?");
                $stmt->execute([$data['name'], $data['amount'], $data['reason'], $data['due_date'], $data['id'], $user_id]);
            } elseif ($data['type'] === 'bill') {
                $stmt = $conn->prepare("UPDATE fixed_bills SET name=?, amount=? WHERE id=? AND user_id=?");
                $stmt->execute([$data['name'], $data['amount'], $data['id'], $user_id]);
            } elseif ($data['type'] === 'expense') {
                $stmt = $conn->prepare("UPDATE expenses SET name=?, amount=? WHERE id=? AND user_id=?");
                $stmt->execute([$data['name'], $data['amount'], $data['id'], $user_id]);
            }
            echo json_encode(["status" => "success"]);
        }
        elseif ($action === 'update_settings') {
            $stmt = $conn->prepare("UPDATE users SET monthly_budget=?, salary_day=? WHERE id=?");
            $stmt->execute([$data['budget'], $data['salary_day'], $user_id]);
            echo json_encode(["status" => "success"]);
        }
        elseif ($action === 'reset_month') {
            $conn->prepare("DELETE FROM expenses WHERE user_id=?")->execute([$user_id]);
            $conn->prepare("UPDATE debts SET is_paid=0 WHERE user_id=?")->execute([$user_id]);
            $conn->prepare("UPDATE car_installments SET is_paid=0 WHERE user_id=?")->execute([$user_id]);
            echo json_encode(["status" => "success"]);
        }
        elseif ($action === 'add_car_plan') {
            $stmt = $conn->prepare("INSERT INTO car_installments (user_id, name, amount, due_date) VALUES (?, ?, ?, ?)");
            foreach ($data['plan'] as $item) {
                $stmt->execute([$user_id, $item['name'], $item['amount'], $item['due_date']]);
            }
            echo json_encode(["status" => "success"]);
        }
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>