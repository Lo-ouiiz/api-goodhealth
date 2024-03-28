<?php
header("Access-Control-Allow-Origin: https://app.louise-mendiburu.fr");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

header('Content-type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !empty($_POST['surname']) && !empty($_POST['firstname']) 
        && !empty($_POST['email']) && !empty($_POST['password'])
        && is_string($_POST['surname']) && is_string($_POST['firstname'])
        && is_string($_POST['email']) && is_string($_POST['password'])
    ) {        
        $surname = $_POST['surname'];
        $firstname = $_POST['firstname'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        require 'DBConnect.php';
        $SELECT_USER_SQL = "SELECT * FROM `Users` WHERE Users.email=:email";
        $duplicate_user_statement = $con->prepare($SELECT_USER_SQL);
        $duplicate_user_statement->bindParam(':email', $email, PDO::PARAM_STR);
        $duplicate_user_statement->execute();
        $duplicate_user_flag = $duplicate_user_statement->rowCount();

        if ($duplicate_user_flag > 0) {
            http_response_code(400);
            echo json_encode(array(
                "code" => 400,
                "status" => false,
                "message" => "This user is already registered."
            ));
        } else {
            $id = uniqid();
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $data_parameters = array(
                "id" => $id,
                "surname" => $surname,
                "firstname" => $firstname,
                "email" => $email,
                "password" => $password_hash
            );
            $INSERT_QUERY = "
                INSERT INTO `Users` (`id`, `surname`, `firstname`, `email`, `password`)
                VALUES (:id, :surname, :firstname, :email, :password)
            ";
            $insert_data_statement = $con->prepare($INSERT_QUERY);
            $insert_data_statement->execute($data_parameters);
            $insert_record_flag = $insert_data_statement->rowCount();

            if ($insert_record_flag > 0) {
                http_response_code(200);
                $user_object = array(
                    "id" => $id,
                    "surname" => $surname,
                    "firstname" => $firstname,
                    "email" => $email
                );
                echo json_encode(array(
                    "code" => 200,
                    "status" => true,
                    "message" => "User successfully created.",
                    "userData" => $user_object
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "code" => 500,
                    "status" => false,
                    "message" => "Failed to create user. Please try again."
                ));
            }
        }
    } else {
        http_response_code(400);
        echo json_encode(array(
            "code" => 400,
            "status" => false,
            "message" => "Invalid API parameters!"
        ));
    }
} else {
    http_response_code(400);
    echo json_encode(array(
        "code" => 400,
        "status" => false,
        "message" => "Bad Request"
    ));
}
?>