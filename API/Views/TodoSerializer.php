
<?php
  require_once('../Models/TodoModel.php');
  
  $general_params = array('id', 'title', 'detalii');
  
  // Using the Seralizer, I define the arrays that will be sent to the frontend 
  class TodoSerializer {
    static function each($conn, $objects) {
      $response = array();
      foreach($objects as $obj) {
        array_push($response, array(
          'id' => $obj->id,
          'title' => $obj->title,
          'detalii' => $obj->detalii,
          'update' => "<button class='edit_btn' crt='$obj->id' onclick='updateTask(this)'>Update</button>",
          'delete' => "<button class='del_btn' crt='$obj->id' onclick='deleteTask(this)'>Delete</button>"
        ));
      }
      return $response;
    }
    static function once($conn, $obj) {
      return array(
        'id' => $obj->id,
          'title' => $obj->title,
          'detalii' => $obj->detalii,
      );
    }
  }
  
?>
  