<?php

require_once 'BD.php';

class Water extends BDHandler {
    
    public function returnError($code, $status, $message) {
        $error = [
            "code" => $code,
            "status" => $status,
            "message" => $message,
        ];

        http_response_code($code);
        echo(json_encode($error));
    }

    public function getLastWaterRecord($userId) {
        if (strlen($userId) === 13) {
            $this->connect();
            $con = $this->con;

            $queryString = "SELECT * FROM `Water` WHERE user_id = ? ORDER BY `date` DESC LIMIT 1";
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
    
    public function getWaterRecords($userId, $page) {
        if (strlen($userId) === 13 && filter_var($page, FILTER_VALIDATE_INT) !== false) {
            $page = intval($page);
            $offset = ($page - 1) * 5;

            $this->connect();
            $con = $this->con;

            $queryString = "SELECT * FROM `Water` WHERE user_id = ? ORDER BY `date` DESC LIMIT 5 OFFSET ?";
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

    public function addWaterRecord($userId, $water, $date) {
        $this->connect();
        $con = $this->con;

        $SELECT_WATER = "SELECT * FROM `Water` WHERE Water.user_id=:userId AND Water.date = :recordDate";
        $duplicate_water_record = $con->prepare($SELECT_WATER);
        $duplicate_water_record->bindParam(':userId', $userId, PDO::PARAM_STR);
        $duplicate_water_record->bindParam(':recordDate', $date, PDO::PARAM_STR);
        $duplicate_water_record->execute();
        $duplicate_water_flag = $duplicate_water_record->rowCount();

        if ($duplicate_water_flag > 0) {
            $this->returnError(400, false, "Water record already exists.");
        } else {
            if (strlen($userId) === 13 && is_numeric($water) && strtotime($date)) {
                $data_parameters = array(
                    "user_id" => $userId,
                    "water" => $water,
                    "date" => $date
                );
                $INSERT_QUERY = "
                    INSERT INTO `Water` (`user_id`, `water_qty`, `date`)
                    VALUES (:user_id, :water, :date)
                ";

                $stmt = $con->prepare($INSERT_QUERY);
                $stmt->execute($data_parameters);
                $insert_record_flag = $stmt->rowCount();

                if ($insert_record_flag > 0) {
                    $this->returnData(true, "Water record successfully created.", []);
                } else {
                    $this->returnError(500, false, "Failed to create water record.");
                }

            } else {
                $this->returnError(400, false, "Invalid API parameters!");
            }
        }
    }

    public function updateWaterRecord($userId, $water, $date) {
        if (strlen($userId) === 13 && is_numeric($water) && strtotime($date)) {
            $this->connect();
            $con = $this->con;
    
            $data_parameters = array(
                "user_id" => $userId,
                "water" => $water,
                "date" => $date
            );
            $UPDATE_QUERY = "
                UPDATE `Water`
                SET `water_qty` = :water
                WHERE `user_id` = :user_id AND `date` = :date
            ";
    
            $stmt = $con->prepare($UPDATE_QUERY);
            $stmt->execute($data_parameters);
            $update_record_flag = $stmt->rowCount();
    
            if ($update_record_flag > 0) {
                $this->returnData(true, "Water record successfully updated.", []);
            } else {
                $this->returnError(500, false, "Failed to update water record.");
            }
    
        } else {
            $this->returnError(400, false, "Invalid API parameters!");
        }    
    }

    public function addTodayWaterRecord($userId, $date, $operation) {
        $this->connect();
        $con = $this->con;

        $water = 250;

        $SELECT_WATER = "SELECT * FROM `Water` WHERE Water.user_id=:userId AND Water.date = :recordDate";
        $duplicate_water_record = $con->prepare($SELECT_WATER);
        $duplicate_water_record->bindParam(':userId', $userId, PDO::PARAM_STR);
        $duplicate_water_record->bindParam(':recordDate', $date, PDO::PARAM_STR);
        $duplicate_water_record->execute();
        $existing_record = $duplicate_water_record->fetch(PDO::FETCH_ASSOC);

        if ($existing_record) {
            if ($operation == "add") {
                $new_water_qty = $existing_record['water_qty'] + $water;
            } else if ($operation == "remove") {
                $new_water_qty = $existing_record['water_qty'] - $water;
            } else {
                $new_water_qty = $existing_record['water_qty'];
            }

            if ($new_water_qty <= 0) {
                if (strlen($userId) === 13 && strtotime($date)) {
                    $this->connect();
                    $con = $this->con;
            
                    $data_parameters = array(
                        "user_id" => $userId,
                        "date" => $date
                    );
                    $DELETE_QUERY = "
                        DELETE FROM `Water`
                        WHERE `user_id` = :user_id AND `date` = :date
                    ";
            
                    $stmt = $con->prepare($DELETE_QUERY);
                    $stmt->execute($data_parameters);
                    $delete_record_flag = $stmt->rowCount();
            
                    if ($delete_record_flag > 0) {
                        $this->returnData(true, "Water record successfully deleted.", []);
                    } else {
                        $this->returnError(500, false, "Failed to delete water record.");
                    }
            
                } else {
                    $this->returnError(400, false, "Invalid API parameters!");
                }
                
            } else {
                if (strlen($userId) === 13 && is_numeric($water) && strtotime($date)) {
                    $this->connect();
                    $con = $this->con;
            
                    $data_parameters = array(
                        "user_id" => $userId,
                        "water" => $new_water_qty,
                        "date" => $date
                    );
                    $UPDATE_QUERY = "
                        UPDATE `Water`
                        SET `water_qty` = :water
                        WHERE `user_id` = :user_id AND `date` = :date
                    ";
            
                    $stmt = $con->prepare($UPDATE_QUERY);
                    $stmt->execute($data_parameters);
                    $update_record_flag = $stmt->rowCount();
            
                    if ($update_record_flag > 0) {
                        $this->returnData(true, "Water record successfully updated.", []);
                    } else {
                        $this->returnError(500, false, "Failed to update water record.");
                    }
            
                } else {
                    $this->returnError(400, false, "Invalid API parameters!");
                }
            }

        } else {
            if (strlen($userId) === 13 && is_numeric($water) && strtotime($date)) {
                $data_parameters = array(
                    "user_id" => $userId,
                    "water" => $water,
                    "date" => $date
                );
                $INSERT_QUERY = "
                    INSERT INTO `Water` (`user_id`, `water_qty`, `date`)
                    VALUES (:user_id, :water, :date)
                ";

                $stmt = $con->prepare($INSERT_QUERY);
                $stmt->execute($data_parameters);
                $insert_record_flag = $stmt->rowCount();

                if ($insert_record_flag > 0) {
                    $this->returnData(true, "Water record successfully created.", []);
                } else {
                    $this->returnError(500, false, "Failed to create water record.");
                }

            } else {
                $this->returnError(400, false, "Invalid API parameters!");
            }
        }
    }
    
    public function deleteWaterRecord($userId, $date) {
        if (strlen($userId) === 13 && strtotime($date)) {
            $this->connect();
            $con = $this->con;
    
            $data_parameters = array(
                "user_id" => $userId,
                "date" => $date
            );
            $DELETE_QUERY = "
                DELETE FROM `Water`
                WHERE `user_id` = :user_id AND `date` = :date
            ";
    
            $stmt = $con->prepare($DELETE_QUERY);
            $stmt->execute($data_parameters);
            $delete_record_flag = $stmt->rowCount();
    
            if ($delete_record_flag > 0) {
                $this->returnData(true, "Water record successfully deleted.", []);
            } else {
                $this->returnError(500, false, "Failed to delete water record.");
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
                $queryString = "SELECT COUNT(*) as count FROM `Water` WHERE user_id = ?";
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

        $queryString = "SELECT COUNT(*) as recordsQuantity FROM `Water` WHERE `user_id` = ?";
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