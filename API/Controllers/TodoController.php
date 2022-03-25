
<?php
  require_once('../Models/TodoModel.php');
  require_once('../Views/TodoSerializer.php');
  
  
  $accepted_params_post = array('title', 'detalii');
  
  $required_params_post = array('title', 'detalii');
  
  $accepted_params_update = array('id', 'title', 'detalii');
  
  $required_params_update = array('id');
  
  $accepted_params_delete = array('id');
  
  $required_params_delete = array('id');



  class TodoController {
    public static function get() {

      // conn is connection to the database
      $data = json_decode(file_get_contents('../../config.json'),true);
      $conn = mysqli_connect($data["servername"], $data["userMySql"], $data["passwordMySql"], "toDo");

      // for function all,count and fetch navigate to ToDoModel.php(in Models)
      $id = getCurrentUrlValue('id');
      if($id){
        $allRecords = Todo::where($conn, array(
          "id" => $id
        ))->fetch();
      } else {
         $allRecords = Todo::all($conn)->fetch();
      }
      $response = array(
        "records" => TodoSerializer::each($conn, $allRecords),
      );
      return $response;
      mysqli_close($conn);
    }

    public static function post($params) {

      $data = json_decode(file_get_contents('../../config.json'),true);
      $conn = mysqli_connect($data["servername"], $data["userMySql"], $data["passwordMySql"], "toDo");
      $conn->autocommit(FALSE);
      // here i make the insertion into the table and return the field
      $obj = Todo::insert($conn, $params);
      if(!$obj) {
        $conn->rollback();
        return array(
          "Error" => "Nu s-a putut inregistra recordul Todo!"
        );
      }
      $conn->commit();
      mysqli_close($conn);
      return TodoSerializer::once($conn, $obj);
    }

    public static function update($params) {

      $data = json_decode(file_get_contents('../../config.json'),true);
      $conn = mysqli_connect($data["servername"], $data["userMySql"], $data["passwordMySql"], "toDo");
      $conn->autocommit(FALSE);
      // here i check if the record exists
      $obj = Todo::get($conn, array(
        'id' => $params['id']
      ));
      if(!$obj) {
        $conn->rollback();
        $id = $params['id'];
        return array(
          "Error" => "Nu s-a putut gasi recordul obiectului Todo cu id $id!"
        );
      }
      unset($params['id']);
      // here i make the the update
      if(!$obj->update($conn, $params)) {
        $conn->rollback();
        return array(
          "Error" => "Nu s-a putut face update la recordul Todo!"
        );
      }
      $conn->commit();
      mysqli_close($conn);
      return TodoSerializer::once($conn, $obj);
    }
    public static function delete($params) {
      
      $data = json_decode(file_get_contents('../../config.json'),true);
      $conn = mysqli_connect($data["servername"], $data["userMySql"], $data["passwordMySql"], "toDo");
      $conn->autocommit(FALSE);
      // here i check if the record exists
      $obj = Todo::get($conn, array(
        'id' => $params['id']
      ));
      if(!$obj) {
        $conn->rollback();
        $id = $params['id'];
        return array(
          "Error" => "Nu s-a putut gasi recordul obiectului Todo cu id $id!"
        );
      }
      // here i make the the delete by id
      if(!$obj->delete($conn, $params)) {
        $conn->rollback();
        return array(
          "Error" => "Nu s-a putut sterge recordul Todo!"
        );
      }
      $conn->commit();
      mysqli_close($conn);
      return TodoSerializer::once($conn, $obj);
    }
  }

  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // when the requets is get than i use the function get from the class TodoController
    echo json_encode(TodoController::get());
    return ;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $params = json_decode(file_get_contents('php://input'),true);
    if(getCurrentUrlValue('patch') && getCurrentUrlValue('patch') == true) {
      // when the requets is update(the link has a parameters patch) than i use the function update from the class TodoController
      // after I verify whetever the params are the same with the ones defined at the beginning
      $errs = checkAcceptedParams($accepted_params_update, $params);
      if(strlen($errs)) {
        http_response_code(400);
        echo json_encode(array(
          "Error" => $errs
        ));
        return ;
      }
      $errs = checkRequiredParams($required_params_update, $params);
      if(strlen($errs)) {
        http_response_code(400);
        echo json_encode(array(
          "Error" => $errs
        ));
        return ;
      }
      $response = TodoController::update($params);
      if(array_key_exists("Error", $response)) {
        http_response_code(400);
        echo json_encode($response);
        return ;
      }
      echo json_encode($response);
      return ;
    }
    $errs = checkAcceptedParams($accepted_params_post, $params);
    if(strlen($errs)) {
      http_response_code(400);
      echo json_encode(array(
        "Error" => $errs
      ));
      return ;
    }
    $errs = checkRequiredParams($required_params_post, $params);
    if(strlen($errs)) {
      http_response_code(400);
      echo json_encode(array(
        "Error" => $errs
      ));
      return ;
    }
    $response = TodoController::post($params);
    if(array_key_exists("Error", $response)) {
      http_response_code(400);
      echo json_encode($response);
      return ;
    }
    echo json_encode($response);
    return ;
  }
  
  if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $params = $_GET;
    $errs = checkAcceptedParams($accepted_params_delete, $params);
    if(strlen($errs)) {
      http_response_code(400);
      echo json_encode(array(
        "Error" => $errs
      ));
      return ;
    }
    $errs = checkRequiredParams($required_params_delete, $params);
    if(strlen($errs)) {
      http_response_code(400);
      echo json_encode(array(
        "Error" => $errs
      ));
      return ;
    }
    $response = TodoController::delete($params);
    if(array_key_exists("Error", $response)) {
      http_response_code(400);
      echo json_encode($response);
      return ;
    }
    echo json_encode($response);
    return ;
  }
  http_response_code(401);
  echo json_encode($error_code);
  
?>
  