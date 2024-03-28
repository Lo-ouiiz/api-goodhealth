<?php

require_once 'BD.php';

class Weight extends BDHandler {
    
    public function returnError($code, $status, $message) {
        $error = [
            "code" => $code,
            "status" => $status,
            "message" => $message,
        ];

        http_response_code($code);
        echo(json_encode($error));
    }

    public function getLastWeightRecord($userId) {
        if (strlen($userId) === 13) {
            $this->connect();
            $con = $this->con;

            $queryString = "SELECT * FROM `Weight` WHERE user_id = ? ORDER BY `date` DESC LIMIT 1";
            $stmt = $con->prepare($queryString);
            $stmt->bindParam(1, $userId, PDO::PARAM_STR);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!$data) {
                $this->returnError(404, false, "No records to display");
            } else {
                $this->returnData(true, "Last record for user with id $userId found", $data);
            }
        } else {
            $this->returnError(400, false, "Invalid API parameters!");
        }
    }
    
    public function getWeightRecords($userId, $page) {
        if (strlen($userId) === 13 && filter_var($page, FILTER_VALIDATE_INT) !== false) {
            $page = intval($page);
            $offset = ($page - 1) * 5;

            $this->connect();
            $con = $this->con;

            $queryString = "SELECT * FROM `Weight` WHERE user_id = ? ORDER BY `date` DESC LIMIT 5 OFFSET ?";
            $stmt = $con->prepare($queryString);
            $stmt->bindParam(1, $userId, PDO::PARAM_STR);
            $stmt->bindParam(2, $offset, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $recordsQuantity = $this->getQuantity($userId);

            if ($recordsQuantity == 0) {
                $this->returnError(404, false, "No records to display");
            } else {
                $this->returnData(true, "Data for user with id $userId and page $page found", $data, $recordsQuantity);
            }
        } else {
            $this->returnError(400, false, "Invalid API parameters!");
        }
    }

    public function addWeightRecord($userId, $weight, $date) {
        $this->connect();
        $con = $this->con;

        $SELECT_WEIGHT = "SELECT * FROM `Weight` WHERE Weight.user_id=:userId AND Weight.date = :recordDate";
        $duplicate_weight_record = $con->prepare($SELECT_WEIGHT);
        $duplicate_weight_record->bindParam(':userId', $userId, PDO::PARAM_STR);
        $duplicate_weight_record->bindParam(':recordDate', $date, PDO::PARAM_STR);
        $duplicate_weight_record->execute();
        $duplicate_weight_flag = $duplicate_weight_record->rowCount();

        if ($duplicate_weight_flag > 0) {
            $this->returnError(400, false, "Weight record already exists.");
        } else {
            $weightFloat = floatval($weight);
            if (strlen($userId) === 13 && is_numeric($weightFloat) && is_float($weightFloat) && strtotime($date)) {
                $data_parameters = array(
                    "user_id" => $userId,
                    "weight" => $weightFloat,
                    "date" => $date
                );
                $INSERT_QUERY = "
                    INSERT INTO `Weight` (`user_id`, `weight`, `date`)
                    VALUES (:user_id, :weight, :date)
                ";

                $stmt = $con->prepare($INSERT_QUERY);
                $stmt->execute($data_parameters);
                $insert_record_flag = $stmt->rowCount();

                if ($insert_record_flag > 0) {
                    $this->returnData(true, "Weight record successfully created.", []);
                } else {
                    $this->returnError(500, false, "Failed to create weight record.");
                }

            } else {
                $this->returnError(400, false, "Invalid API parameters!");
            }
        }
    }

    public function updateWeightRecord($userId, $weight, $date) {
        $weightFloat = floatval($weight);
        if (strlen($userId) === 13 && is_numeric($weightFloat) && is_float($weightFloat) && strtotime($date)) {
            $this->connect();
            $con = $this->con;
    
            $data_parameters = array(
                "user_id" => $userId,
                "weight" => $weightFloat,
                "date" => $date
            );
            $UPDATE_QUERY = "
                UPDATE `Weight`
                SET `weight` = :weight
                WHERE `user_id` = :user_id AND `date` = :date
            ";
    
            $stmt = $con->prepare($UPDATE_QUERY);
            $stmt->execute($data_parameters);
            $update_record_flag = $stmt->rowCount();
    
            if ($update_record_flag > 0) {
                $this->returnData(true, "Weight record successfully updated.", []);
            } else {
                $this->returnError(500, false, "Failed to update weight record.");
            }
    
        } else {
            $this->returnError(400, false, "Invalid API parameters!");
        }
    }
    
    public function deleteWeightRecord($userId, $date) {
	  var_dump($date);
        if (strlen($userId) === 13 && strtotime($date)) {
            $this->connect();
            $con = $this->con;
    
            $data_parameters = array(
                "user_id" => $userId,
                "date" => $date
            );
            $DELETE_QUERY = "
                DELETE FROM `Weight`
                WHERE `user_id` = :user_id AND `date` = :date
            ";
    
            $stmt = $con->prepare($DELETE_QUERY);
            $stmt->execute($data_parameters);
            $delete_record_flag = $stmt->rowCount();
    
            if ($delete_record_flag > 0) {
                $this->returnData(true, "Weight record successfully deleted.", []);
            } else {
                $this->returnError(500, false, "Failed to delete weight record.");
            }
    
        } else {
            $this->returnError(400, false, "Invalid API parameters!");
        }
    } 
    
    public function getPageQty($userId) {
        if (strlen($userId) === 13) {
            $this->connect();
            $con = $this->con;
            
            $recordsQuantity = $this->getQuantity($userId);

            if ($recordsQuantity == 0) {
                $this->returnError(404, false, "No pages and no records.");
            } else {
                $queryString = "SELECT COUNT(*) as count FROM `Weight` WHERE user_id = ?";
                $stmt = $con->prepare($queryString);
                $stmt->bindParam(1, $userId, PDO::PARAM_STR);
                $stmt->execute();

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $count = $row['count'];
                $stmt->closeCursor();

                $pages = ceil($count / 5);

                $this->returnData(true, "$pages pages found for user with id $userId", $pages);
            }
        }
    }

    private function getQuantity($userId) {
        $this->connect();
        $con = $this->con;

        $queryString = "SELECT COUNT(*) as recordsQuantity FROM `Weight` WHERE `user_id` = ?";
        $stmt = $con->prepare($queryString);
        $stmt->bindParam(1, $userId, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $recordsQuantity = $result['recordsQuantity'];
        $stmt->closeCursor();

        return $recordsQuantity;
    }

    private function returnData($status, $message, $data, $quantity = null) {
        if ($quantity !== null && $quantity >= 0) {
            $response = [
                "code" => 200,
                "status" => $status,
                "message" => $message,
                "data" => $data,
                "qty" => $quantity,
            ];
        } else {
            $response = [
                "code" => 200,
                "status" => $status,
                "message" => $message,
                "data" => $data,
            ];
        }

        http_response_code($status ? 200 : 400);
        echo(json_encode($response));
    }
}

?>