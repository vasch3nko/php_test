<?php

require 'config.php';

const POSTS_URL = 'https://jsonplaceholder.typicode.com/posts';
const COMMENTS_URL = 'https://jsonplaceholder.typicode.com/comments';

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

$posts = json_decode(file_get_contents(POSTS_URL), true);
$comments = json_decode(file_get_contents(COMMENTS_URL), true);

$stmt = $pdo->prepare("
    INSERT INTO posts (id, user_id, title, body) 
    VALUES (:id, :user_id, :title, :body)
") or exit('Ошибка подготовки запроса на вставку постов');

foreach ($posts as $post) {
    $stmt->execute([
        'id' => $post['id'],
        'user_id' => $post['userId'],
        'title' => $post['title'],
        'body' => $post['body']
    ]) or exit('Ошибка выполнения запроса на вставку постов');
}

$stmt = $pdo->prepare("
    INSERT INTO comments (id, post_id, name, email, body) 
    VALUES (:id, :post_id, :name, :email, :body)
") or exit('Ошибка подготовки запроса на вставку комментариев');

foreach ($comments as $comment) {
    $stmt->execute([
        'id' => $comment['id'],
        'post_id' => $comment['postId'],
        'name' => $comment['name'],
        'email' => $comment['email'],
        'body' => $comment['body']
    ]);
}

echo "Загружено " . count($posts) . " записей и " . count($comments) . " комментариев\n";
