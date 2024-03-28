<?php

require_once 'BD.php';

class Sleep extends BDHandler {
    
    public function returnError($code, $status, $message) {
        $error = [
            "code" => $code,
            "status" => $status,
            "message" => $message,
        ];

        http_response_code($code);
        echo(json_encode($error));
    }

    public function getLastSleepRecord($userId) {
        if (strlen($userId) === 13) {
            $this->connect();
            $con = $this->con;

            $queryString = "SELECT * FROM `Sleep` WHERE user_id = ? ORDER BY `date` DESC LIMIT 1";
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
    
    public function getSleepRecords($userId, $page) {
        if (strlen($userId) === 13 && filter_var($page, FILTER_VALIDATE_INT) !== false) {
            $page = intval($page);
            $offset = ($page - 1) * 5;

            $this->connect();
            $con = $this->con;

            $queryString = "SELECT * FROM `Sleep` WHERE user_id = ? ORDER BY `date` DESC LIMIT 5 OFFSET ?";
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

    public function addSleepRecord($userId, $date, $bedtime, $waketime) {
        $this->connect();
        $con = $this->con;
    
        $SELECT_SLEEP = "SELECT * FROM `Sleep` WHERE user_id=:userId AND date=:date";
        $duplicate_sleep_record = $con->prepare($SELECT_SLEEP);
        $duplicate_sleep_record->bindParam(':userId', $userId, PDO::PARAM_STR);
        $duplicate_sleep_record->bindParam(':date', $date, PDO::PARAM_STR);
        $duplicate_sleep_record->execute();
        $duplicate_sleep_flag = $duplicate_sleep_record->rowCount();
    
        if ($duplicate_sleep_flag > 0) {
            $this->returnError(400, false, "Sleep record already exists.");
        } else {
            if (strlen($userId) === 13 && strtotime($date) && strtotime($bedtime) && strtotime($waketime)) {
                $bedtimeObj = new DateTime($bedtime);
                $waketimeObj = new DateTime($waketime);

                $diff = $waketimeObj->diff($bedtimeObj);
                $sleepTimeHours = $diff->h;
                $sleepTimeMinutes = $diff->i;
                
                $sleepTimeHoursFormatted = sprintf("%02d", $sleepTimeHours);
                $sleepTimeMinutesFormatted = sprintf("%02d", $sleepTimeMinutes);
                
                $sleepTimeArray = "[".$sleepTimeHoursFormatted.",".$sleepTimeMinutesFormatted."]";
    
                $data_parameters = array(
                    "user_id" => $userId,
                    "date" => $date,
                    "bedtime" => $bedtime,
                    "waketime" => $waketime,
                    "sleeptime" => $sleepTimeArray
                );
                $INSERT_QUERY = "
                    INSERT INTO `Sleep` (`user_id`, `date`, `bedtime`, `waketime`, `sleeptime`)
                    VALUES (:user_id, :date, :bedtime, :waketime, :sleeptime)
                ";
    
                $stmt = $con->prepare($INSERT_QUERY);
                $stmt->execute($data_parameters);
                $insert_record_flag = $stmt->rowCount();
    
                if ($insert_record_flag > 0) {
                    $this->returnData(true, "Sleep record successfully created.", []);
                } else {
                    $this->returnError(500, false, "Failed to create sleep record.");
                }
            } else {
                $this->returnError(400, false, "Invalid API parameters!");
            }
        }
    }
    
    public function updateSleepRecord($userId, $date, $bedtime, $waketime) {
        if (strlen($userId) === 13 && strtotime($date) && strtotime($bedtime) && strtotime($waketime)) {
            $bedtimeObj = new DateTime($bedtime);
            $waketimeObj = new DateTime($waketime);

            $diff = $waketimeObj->diff($bedtimeObj);
            $sleepTimeHours = $diff->h;
            $sleepTimeMinutes = $diff->i;
            
            $sleepTimeHoursFormatted = sprintf("%02d", $sleepTimeHours);
            $sleepTimeMinutesFormatted = sprintf("%02d", $sleepTimeMinutes);
            
            $sleepTimeArray = "[".$sleepTimeHoursFormatted.",".$sleepTimeMinutesFormatted."]";

            $this->connect();
            $con = $this->con;
    
            $data_parameters = array(
                "user_id" => $userId,
                "date" => $date,
                "bedtime" => $bedtime,
                "waketime" => $waketime,
                "sleeptime" => $sleepTimeArray
            );
            $UPDATE_QUERY = "
                UPDATE `Sleep`
                SET `bedtime` = :bedtime, `waketime` = :waketime, `sleeptime` = :sleeptime, 
                WHERE `user_id` = :user_id AND `date` = :date
            ";
    
            $stmt = $con->prepare($UPDATE_QUERY);
            $stmt->execute($data_parameters);
            $update_record_flag = $stmt->rowCount();
    
            if ($update_record_flag > 0) {
                $this->returnData(true, "Sleep record successfully updated.", []);
            } else {
                $this->returnError(500, false, "Failed to update sleep record.");
            }
    
        } else {
            $this->returnError(400, false, "Invalid API parameters!");
        }
    }
    
    public function deleteSleepRecord($userId, $date) {
        if (strlen($userId) === 13 && strtotime($date)) {
            $this->connect();
            $con = $this->con;
    
            $data_parameters = array(
                "user_id" => $userId,
                "date" => $date
            );
            $DELETE_QUERY = "
                DELETE FROM `Sleep`
                WHERE `user_id` = :user_id AND `date` = :date
            ";
    
            $stmt = $con->prepare($DELETE_QUERY);
            $stmt->execute($data_parameters);
            $delete_record_flag = $stmt->rowCount();
    
            if ($delete_record_flag > 0) {
                $this->returnData(true, "Sleep record successfully deleted.", []);
            } else {
                $this->returnError(500, false, "Failed to delete sleep record.");
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
                $queryString = "SELECT COUNT(*) as count FROM `Sleep` WHERE user_id = ?";
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

        $queryString = "SELECT COUNT(*) as recordsQuantity FROM `Sleep` WHERE `user_id` = ?";
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