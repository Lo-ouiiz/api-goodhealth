<?php

require_once 'BD.php';

class Advice extends BDHandler {
    
    public function returnError($code, $status, $message) {
        $error = [
            "code" => $code,
            "status" => $status,
            "message" => $message,
        ];

        http_response_code($code);
        echo(json_encode($error));
    }

    public function getRandomAdvice() {
        $this->connect();
        $con = $this->con;

        $queryString = "SELECT * FROM Advice ORDER BY RAND() LIMIT 1;";
        $stmt = $con->prepare($queryString);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if (!$data) {
            $this->returnError(404, false, "No advice to display");
        } else {
            $this->returnData(true, "Advice found", $data);
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