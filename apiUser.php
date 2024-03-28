<?php
header("Access-Control-Allow-Origin: https://app.louise-mendiburu.fr");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

header('Content-type: application/json');

require 'User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!empty($_POST['method']) && is_string($_POST['method'])) {

        $user = new User();

        switch ($_POST['method']) {
            case "getGoal":
                if (
                    !empty($_POST['userId']) && !empty($_POST['goalType']) 
                    && is_string($_POST['userId']) && is_string($_POST['goalType'])
                ) {
                    $user->getUserGoal($_POST['userId'], $_POST['goalType']);
                } else {
                    $user->returnError(400, false, "Invalid API parameters!");
                }
                break;
            case "updateGoal":
                if (
                    !empty($_POST['userId']) && !empty($_POST['goalType']) && !empty($_POST['goalValue']) 
                    && is_string($_POST['userId']) && is_string($_POST['goalType'])
                ) {
                    $user->updateUserGoal($_POST['userId'], $_POST['goalType'], $_POST['goalValue']);
                } else {
                    $user->returnError(400, false, "Invalid API parameters!");
                }
                break;
            case "update":
                if (
                    !empty($_POST['userId']) && !empty($_POST['name'] && !empty($_POST['surname']))
                    && is_string($_POST['userId']) && is_string($_POST['name']) && is_string($_POST['surname'])
                ) {
                    $user->updateUser($_POST['userId'], $_POST['name'], $_POST['surname']);
                } else {
                    $user->returnError(400, false, "Invalid API parameters!");
                }
                break;
            case "updatePassword":
                if (
                    !empty($_POST['userId']) && !empty($_POST['oldPassword'] && !empty($_POST['newPassword']))
                    && is_string($_POST['userId']) && is_string($_POST['oldPassword']) && is_string($_POST['newPassword'])
                ) {
                    $user->updatePasswordUser($_POST['userId'], $_POST['oldPassword'], $_POST['newPassword']);
                } else {
                    $user->returnError(400, false, "Invalid API parameters!");
                }
                break;
            case "delete":
                if (!empty($_POST['userId']) && is_string($_POST['userId'])) {
                    $user->deleteUser($_POST['userId']);
                } else {
                    $user->returnError(400, false, "Invalid API parameters!");
                }
                break;
            default:
                $user->returnError(400, false, "Invalid API parameters : wrong method.");
        }
    }  else {
        http_response_code(400);
        $server_response_error = array(
            "code" => 400,
            "status" => false,
            "message" => "Missing API parameters : no method."
        );
        echo json_encode($server_response_error);
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