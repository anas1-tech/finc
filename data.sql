-- 1. جدول المستخدمين (Users)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    monthly_budget DECIMAL(10,2) DEFAULT 5000.00,  
    salary_day INT DEFAULT 27,                     
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. جدول الديون (Debts)
CREATE TABLE IF NOT EXISTS debts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                          
    type ENUM('toMe', 'otherDebts') NOT NULL,      
    name VARCHAR(255) NOT NULL,
    reason VARCHAR(255),
    debt_date VARCHAR(50),
    amount DECIMAL(10,2) NOT NULL,
    is_paid BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. جدول أقساط السيارة (Car Installments)
CREATE TABLE IF NOT EXISTS car_installments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                          
    name VARCHAR(255) NOT NULL,                    
    amount DECIMAL(10,2) NOT NULL,
    delay_reason VARCHAR(255),                     
    due_date VARCHAR(50),                          
    is_paid BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. جدول المصروفات (Expenses)
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                          
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date VARCHAR(100) NOT NULL,            
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. جدول الفواتير الثابتة (Fixed Bills)
CREATE TABLE IF NOT EXISTS fixed_bills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,                          
    name VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;