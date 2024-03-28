<?php
header("Access-Control-Allow-Origin: https://app.louise-mendiburu.fr");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

header('Content-type: application/json');

require 'Weight.php';
require 'Sleep.php';
require 'Water.php';
require 'Advice.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !empty($_POST['type']) && !empty($_POST['method']) 
        && is_string($_POST['type']) && is_string($_POST['method'])
    ) {
        
        switch ($_POST['type']) {
            case "weight":
                $weight = new Weight();
    
                switch ($_POST['method']) {
                    case "add":
                        if (
                            !empty($_POST['userId']) && !empty($_POST['weight']) && !empty($_POST['date']) 
                            && is_string($_POST['userId']) && is_string($_POST['weight']) && is_string($_POST['date'])
                        ) {
                            $weight->addWeightRecord($_POST['userId'], $_POST['weight'], $_POST['date']);
                        } else {
                            $weight->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "list":
                        if (!empty($_POST['userId']) && !empty($_POST['page']) && is_string($_POST['userId'])) {
                            $weight->getWeightRecords($_POST['userId'], $_POST['page']);
                        } else {
                            $weight->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "listLastOne":
                        if (!empty($_POST['userId']) && is_string($_POST['userId'])) {
                            $weight->getLastWeightRecord($_POST['userId']);
                        } else {
                            $weight->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "update":
                        if (
                            !empty($_POST['userId']) && !empty($_POST['weight']) && !empty($_POST['date']) 
                            && is_string($_POST['userId']) && is_string($_POST['weight']) && is_string($_POST['date'])
                        ) {
                            $weight->updateWeightRecord($_POST['userId'], $_POST['weight'], $_POST['date']);
                        } else {
                            $weight->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "delete":
                        if (
                            !empty($_POST['userId']) && !empty($_POST['date']) 
                            && is_string($_POST['userId']) && is_string($_POST['date'])
                        ) {
                            $weight->deleteWeightRecord($_POST['userId'], $_POST['date']);
                        } else {
                            $weight->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "getPageQuantity":
                        if (!empty($_POST['userId']) && is_string($_POST['userId'])) {
                            $weight->getPageQty($_POST['userId']);
                        } else {
                            $weight->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    default:
                        $weight->returnError(400, false, "Invalid API parameters : wrong method.");
                }
    
                break;

            case "sleep":
                $sleep = new Sleep();
    
                switch ($_POST['method']) {
                    case "add":
                        if (
                            !empty($_POST['userId']) && !empty($_POST['date']) && !empty($_POST['bedtime']) && !empty($_POST['waketime'])
                            && is_string($_POST['userId']) && is_string($_POST['date']) && is_string($_POST['bedtime']) && is_string($_POST['waketime'])
                        ) {
                            $sleep->addSleepRecord($_POST['userId'], $_POST['date'], $_POST['bedtime'], $_POST['waketime']);
                        } else {
                            $sleep->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "list":
                        if (!empty($_POST['userId']) && !empty($_POST['page']) && is_string($_POST['userId'])) {
                            $sleep->getSleepRecords($_POST['userId'], $_POST['page']);
                        } else {
                            $sleep->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "listLastOne":
                        if (!empty($_POST['userId']) && is_string($_POST['userId'])) {
                            $sleep->getLastSleepRecord($_POST['userId']);
                        } else {
                            $sleep->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "update":
                        if (
                            !empty($_POST['userId']) && !empty($_POST['date']) && !empty($_POST['bedtime']) && !empty($_POST['waketime'])
                            && is_string($_POST['userId']) && is_string($_POST['date']) && is_string($_POST['bedtime']) && is_string($_POST['waketime'])
                        ) {
                            $sleep->updateSleepRecord($_POST['userId'], $_POST['date'], $_POST['bedtime'], $_POST['waketime']);
                        } else {
                            $sleep->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "delete":
                        if (
                            !empty($_POST['userId']) && !empty($_POST['date']) 
                            && is_string($_POST['userId']) && is_string($_POST['date'])
                        ) {
                            $sleep->deleteSleepRecord($_POST['userId'], $_POST['date']);
                        } else {
                            $sleep->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "getPageQuantity":
                        if (!empty($_POST['userId']) && is_string($_POST['userId'])) {
                            $sleep->getPageQty($_POST['userId']);
                        } else {
                            $sleep->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    default:
                        $sleep->returnError(400, false, "Invalid API parameters : wrong method.");
                }
                break;
            
            case "water":
                $water = new Water();
    
                switch ($_POST['method']) {
                    case "add":
                        if (
                            !empty($_POST['userId']) && !empty($_POST['water']) && !empty($_POST['date']) 
                            && is_string($_POST['userId']) && is_string($_POST['water']) && is_string($_POST['date'])
                        ) {
                            $water->addWaterRecord($_POST['userId'], $_POST['water'], $_POST['date']);
                        } else {
                            $water->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "addToday":
                        if (
                            !empty($_POST['userId']) && !empty($_POST['date']) && !empty($_POST['operation']) 
                            && is_string($_POST['userId']) && is_string($_POST['date']) && is_string($_POST['operation'])
                        ) {
                            $water->addTodayWaterRecord($_POST['userId'], $_POST['date'], $_POST['operation']);
                        } else {
                            $water->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "list":
                        if (!empty($_POST['userId']) && !empty($_POST['page']) && is_string($_POST['userId'])) {
                            $water->getWaterRecords($_POST['userId'], $_POST['page']);
                        } else {
                            $water->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "listLastOne":
                        if (!empty($_POST['userId']) && is_string($_POST['userId'])) {
                            $water->getLastWaterRecord($_POST['userId']);
                        } else {
                            $water->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "update":
                        if (
                            !empty($_POST['userId']) && !empty($_POST['water']) && !empty($_POST['date']) 
                            && is_string($_POST['userId']) && is_string($_POST['water']) && is_string($_POST['date'])
                        ) {
                            $water->updateWaterRecord($_POST['userId'], $_POST['water'], $_POST['date']);
                        } else {
                            $water->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "delete":
                        if (
                            !empty($_POST['userId']) && !empty($_POST['date']) 
                            && is_string($_POST['userId']) && is_string($_POST['date'])
                        ) {
                            $water->deleteWaterRecord($_POST['userId'], $_POST['date']);
                        } else {
                            $water->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    case "getPageQuantity":
                        if (!empty($_POST['userId']) && is_string($_POST['userId'])) {
                            $water->getPageQty($_POST['userId']);
                        } else {
                            $water->returnError(400, false, "Invalid API parameters!");
                        }
                        break;
                    default:
                        $water->returnError(400, false, "Invalid API parameters : wrong method.");
                }
    
                break;

            case 'advice':
                $advice = new Advice();
                if ($_POST['method'] == "list") {
                    $advice->getRandomAdvice();
                }
                break;

            default:
                http_response_code(400);
                $server_response_error = array(
                    "code" => 400,
                    "status" => false,
                    "message" => "Invalid API parameters : wrong type."
                );
                echo json_encode($server_response_error);
        }

    }  else {
        http_response_code(400);
        $server_response_error = array(
            "code" => 400,
            "status" => false,
            "message" => "Missing API parameters : no type or method."
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