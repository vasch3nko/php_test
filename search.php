<?php

require 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE . ";charset=" . DB_CHARSET,
        DB_USER, DB_PASSWORD, DB_SSL_FLAG === MYSQLI_CLIENT_SSL ? [
            PDO::MYSQL_ATTR_SSL_CA => DB_SSL_CA,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
        ] : [PDO::MYSQL_ATTR_MULTI_STATEMENTS => false]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("Ошибка подключения к БД: " . $e->getMessage());
}

if (!isset($_POST['query']) || strlen($_POST['query']) < 3) {
    exit("<h1>Введите хотя бы 3 символа для поиска.</h1>");
}

$query = "%" . $_POST['query'] . "%";

$stmt = $pdo->prepare("
    SELECT posts.id AS post_id, posts.title AS post_title, comments.body AS comment_body
    FROM comments
    JOIN posts ON comments.post_id = posts.id
    WHERE comments.body LIKE ?
") or exit('Ошибка подготовки запроса для поиска постов по комментарию');

$stmt->execute([$query]) or exit('Ошибка выполнения запроса для поиска постов по комментарию');

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($rows) <= 0) exit("<h1>Ничего не найдено.</h1>");

echo "<h1>Результаты поиска по слову " . $_POST['query'] . ":</h1>";
$prevId = null;
foreach ($rows as $row) {
    if ($prevId !== $row['post_id']) {
        if ($prevId !== null) {
            echo "</ol>";
        }
        echo "<h3>Пост \"" . $row['post_title'] . "\":</h3><ol>";
    }

    echo "<li>" . $row['comment_body'] . "</li>";

    $prevId = $row['post_id'];
}
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');

    body {
        font-family: Roboto, sans-serif;
        font-style: normal;
        padding: 20px;
    }

    h1 {
        font-size: 32px;
        font-weight: 700;
        justify-self: center;
    }

    h3 {
        font-size: 24px;
        font-weight: 400;
    }

    li {
        font-size: 16px;
        font-weight: 300;
    }
</style>
