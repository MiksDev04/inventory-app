<?php

$dsn = 'mysql:host=localhost;port=3306;dbname=inventory_system;charset=utf8mb4;';
$username = 'root';
$password = '1234';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // if ($pdo) {
    //     echo "Database connected";
    // }
} catch (\Throwable $th) {
    errorPage("Database connection failed: " . $th->getMessage());
}



function errorPage($message)
{
    http_response_code(500);
    echo "<pre>$message</pre>";
    die;
}

function isLogIn()
{
    return isset($_SESSION['user_id']) ? true : false;
}

function loginUser($username, $password)
{
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
    $stmt->execute(['username' => $username, 'password' => $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
}

function registerUser($username, $password, $role)
{
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
    $stmt->execute(['username' => $username, 'password' => $password, 'role' => $role]);
    return $stmt->rowCount() > 0;
}

function activeNav($page)
{
    $currentPage = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $arrPage = explode('/', $currentPage);
    $selectedPage =  $arrPage[1];
    return $selectedPage === $page ? 'active' : 'link-dark';
}

function get($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getTotal($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetchColumn();
    return $result !== false ? $result : 0;
}
