<?php
header("Access-Control-Allow-Origin: https://app.louise-mendiburu.fr");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

header('Content-type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password) && is_string($email) && is_string($password)) {
        require 'DBConnect.php';

        $SELECT_USER_DATA = "SELECT * FROM `Users` WHERE Users.email=:email";
        $select_user_statement = $con->prepare($SELECT_USER_DATA);
        $select_user_statement->bindParam(':email', $email, PDO::PARAM_STR);
        $select_user_statement->execute();
        $user_flag = $select_user_statement->rowCount();

        if ($user_flag > 0) {
            $user_data = $select_user_statement->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user_data['password'])) {
                http_response_code(200);
                $user_object = array(
                    "id" => $user_data['id'],
                    "surname" => $user_data['surname'],
                    "firstname" => $user_data['firstname'],
                    "email" => $user_data['email']
                );
                $server_response_success = array(
                    "code" => 200,
                    "status" => true,
                    "message" => "User Verified",
                    "userData" => $user_object
                );
                echo json_encode($server_response_success);
            } else {
                http_response_code(404);
                $server_response_error = array(
                    "code" => 404,
                    "status" => false,
                    "message" => "Oops!! Incorrect Password"
                );
                echo json_encode($server_response_error);
            }
        } else {
            http_response_code(404);
            $server_response_error = array(
                "code" => 404,
                "status" => false,
                "message" => "Oops!! Incorrect Login Credentials"
            );
            echo json_encode($server_response_error);
        }
    } else {
        http_response_code(400);
        $server_response_error = array(
            "code" => 400,
            "status" => false,
            "message" => "Invalid API parameters!"
        );
        echo json_encode($server_response_error);
    }
} else {
    http_response_code(400);
    $server_response_error = array(
        "code" => 400,
        "status" => false,
        "message" => "Bad Request"
    );
    echo json_encode($server_response_error);
}
?>