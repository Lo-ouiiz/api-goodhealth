<?php

require_once 'BD.php';

class User extends BDHandler {
    
    public function returnError($code, $status, $message) {
        $error = [
            "code" => $code,
            "status" => $status,
            "message" => $message,
        ];

        http_response_code($code);
        echo(json_encode($error));
    }

    public function getUserGoal($userId, $goalType) {
        if (strlen($userId) === 13) {
            $this->connect();
            $con = $this->con;

            switch ($goalType) {
                case "weight":
                    $queryString = "SELECT `goal_weight` FROM `Users` WHERE id = ?";
                    $stmt = $con->prepare($queryString);
                    $stmt->bindParam(1, $userId, PDO::PARAM_STR);
                    $stmt->execute();

                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                    break;
                case "sleep":
                    $queryString = "SELECT `goal_sleep` FROM `Users` WHERE id = ?";
                    $stmt = $con->prepare($queryString);
                    $stmt->bindParam(1, $userId, PDO::PARAM_STR);
                    $stmt->execute();

                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                    break;
                case "water":
                    $queryString = "SELECT `goal_water` FROM `Users` WHERE id = ?";
                    $stmt = $con->prepare($queryString);
                    $stmt->bindParam(1, $userId, PDO::PARAM_STR);
                    $stmt->execute();

                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                    break;
                default:
                    $this->returnError(400, false, "Invalid API parameters!");
            }          

            if (!$data) {
                $this->returnError(404, false, "No goal to display");
            } else {
                $this->returnData(true, "Goal $goalType for user with id $userId found", $data);
            }
        } else {
            $this->returnError(400, false, "Invalid API parameters!");
        }
    }

    public function updateUserGoal($userId, $goalType, $goalValue) {
        if (strlen($userId) === 13) {
            $this->connect();
            $con = $this->con;

            switch ($goalType) {
                case "weight":
                    $weightFloat = floatval($goalValue);
                    if (is_numeric($weightFloat) && is_float($weightFloat)) {
                        $data_parameters = array(
                            "user_id" => $userId,
                            "goal_weight" => $weightFloat
                        );
                        $UPDATE_QUERY = "
                            UPDATE `Users`
                            SET `goal_weight` = :goal_weight
                            WHERE `id` = :user_id
                        ";
                
                        $stmt = $con->prepare($UPDATE_QUERY);
                        $stmt->execute($data_parameters);
                        $update_goal_flag = $stmt->rowCount();

                        if ($update_goal_flag > 0) {
                            $this->returnData(true, "Weight goal successfully updated.", []);
                        } else {
                            $this->returnError(500, false, "Failed to update weight goal.");
                        }
                    } else {
                        $this->returnError(400, false, "Invalid API parameters!");
                    }
                    break;
                case "sleep":
                    if (is_string($goalValue)) {
                        $data_parameters = array(
                            "user_id" => $userId,
                            "goal_sleep" => $goalValue
                        );
                        $UPDATE_QUERY = "
                            UPDATE `Users`
                            SET `goal_sleep` = :goal_sleep
                            WHERE `id` = :user_id
                        ";
                
                        $stmt = $con->prepare($UPDATE_QUERY);
                        $stmt->execute($data_parameters);
                        $update_goal_flag = $stmt->rowCount();

                        if ($update_goal_flag > 0) {
                            $this->returnData(true, "Sleep goal successfully updated.", []);
                        } else {
                            $this->returnError(500, false, "Failed to update sleep goal.");
                        }
                    } else {
                        $this->returnError(400, false, "Invalid API parameters!");
                    }
                    break;
                case "water":
                    if (is_numeric($goalValue)) {
                        $data_parameters = array(
                            "user_id" => $userId,
                            "goal_water" => $goalValue
                        );
                        $UPDATE_QUERY = "
                            UPDATE `Users`
                            SET `goal_water` = :goal_water
                            WHERE `id` = :user_id
                        ";
                
                        $stmt = $con->prepare($UPDATE_QUERY);
                        $stmt->execute($data_parameters);
                        $update_goal_flag = $stmt->rowCount();

                        if ($update_goal_flag > 0) {
                            $this->returnData(true, "Water goal successfully updated.", []);
                        } else {
                            $this->returnError(500, false, "Failed to update water goal.");
                        }
                    } else {
                        $this->returnError(400, false, "Invalid API parameters!");
                    }
                    break;
                default:
                    $this->returnError(400, false, "Invalid API parameters!");
            }
        } else {
            $this->returnError(400, false, "Invalid API parameters!");
        }
    }

    public function updateUser($userId, $name, $surname) {
        if (strlen($userId) === 13) {
            $this->connect();
            $con = $this->con;
            
            $data_parameters = array(
                "user_id" => $userId,
                "surname" => $name,
                "firstname" => $surname
            );
            $UPDATE_QUERY = "
                UPDATE `Users`
                SET `surname` = :surname, `firstname` = :firstname
                WHERE `id` = :user_id
            ";
    
            $stmt = $con->prepare($UPDATE_QUERY);
            $stmt->execute($data_parameters);
            $update_goal_flag = $stmt->rowCount();

            if ($update_goal_flag > 0) {
                $this->returnData(true, "User successfully updated.", []);
            } else {
                $this->returnError(500, false, "Failed to update user.");
            }
        } else {
            $this->returnError(400, false, "Invalid API parameters!");
        }
    }

    public function updatePasswordUser($userId, $oldPassword, $newPassword) {
        if (strlen($userId) === 13) {
            $this->connect();
            $con = $this->con;
            
            $SELECT_USER_DATA = "SELECT * FROM `Users` WHERE Users.id=:userId";
            $select_user_statement = $con->prepare($SELECT_USER_DATA);
            $select_user_statement->bindParam(':userId', $userId, PDO::PARAM_STR);
            $select_user_statement->execute();
            $user_flag = $select_user_statement->rowCount();

            if ($user_flag > 0) {
                $user_data = $select_user_statement->fetch(PDO::FETCH_ASSOC);
                if (password_verify($oldPassword, $user_data['password'])) {
                    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

                    $data_parameters = array(
                        "user_id" => $userId,
                        "newPassword" => $newPasswordHash
                    );
                    $UPDATE_QUERY = "
                        UPDATE `Users`
                        SET `password` = :newPassword
                        WHERE `id` = :user_id
                    ";
            
                    $stmt = $con->prepare($UPDATE_QUERY);
                    $stmt->execute($data_parameters);
                    $update_goal_flag = $stmt->rowCount();

                    if ($update_goal_flag > 0) {
                        $this->returnData(true, "User password successfully updated.", []);
                    } else {
                        $this->returnError(500, false, "Failed to update user password.");
                    }

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
                    "message" => "No user found."
                );
                echo json_encode($server_response_error);
            }
        } else {
            $this->returnError(400, false, "Invalid API parameters!");
        }
    }

    public function deleteUser($userId) {
        if (strlen($userId) === 13) {
            $this->connect();
            $con = $this->con;
    
            $DELETE_USER_QUERY = "DELETE FROM `Users` WHERE `id` = :user_id";
            $DELETE_USER_WEIGHT_QUERY = "DELETE FROM `Weight` WHERE `user_id` = :user_id";
            $DELETE_USER_SLEEP_QUERY = "DELETE FROM `Sleep` WHERE `user_id` = :user_id";
    
            $stmt = $con->prepare($DELETE_USER_QUERY);
            $stmt->execute(array(':user_id' => $userId));
            $delete_user_flag = $stmt->rowCount();
    
            $stmt = $con->prepare($DELETE_USER_WEIGHT_QUERY);
            $stmt->execute(array(':user_id' => $userId));
    
            $stmt = $con->prepare($DELETE_USER_SLEEP_QUERY);
            $stmt->execute(array(':user_id' => $userId));
    
            if ($delete_user_flag > 0) {
                $this->returnData(true, "User successfully deleted.", []);
            } else {
                $this->returnError(500, false, "Failed to delete user.");
            }
        } else {
            $this->returnError(400, false, "Invalid API parameters!");
        }
    }    

    private function returnData($status, $message, $data) {
        $response = [
            "code" => 200,
            "status" => $status,
            "message" => $message,
            "data" => $data,
        ];

        http_response_code($status ? 200 : 400);
        echo(json_encode($response));
    }
}

?>