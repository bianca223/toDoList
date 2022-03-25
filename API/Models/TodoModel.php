
<?php
  require_once('ActiveRecords/ActiveRecords.php');
  require_once('Extensions/TodoExtension.php');
  
  //Here I define the models that are used in Controllers 
  //These models are the ones that make the connection with the database 
  $accepted_params_post = array('id', 'title', 'detalii');
  
  
  $required_params_post = array('title', 'detalii');
  
  
  $accepted_params_update = array('id', 'title', 'detalii');
  
  
  $required_params_update = array('id');
  
  
  $accepted_params_delete = array('id');
  
  
  $required_params_delete = array('id');
  
  
  class Todo extends TodoExtension  {
    public $id;
    public $title;
    public $detalii;
    static $valTypes = array(
      'id' => 'int','title' => 'varchar','detalii' => 'varchar'
    );
  
    public static function get($conn, $params) {
      $self = new Todo;
      $response = getRecordByMulti($conn, $params, "toDo", "toDo", new Todo);
      if(!$response) {
        return NULL;
      } 
      
      if(array_key_exists("id", $response)) {
        $self->id = $response['id'];
      }
      if(array_key_exists("title", $response)) {
        $self->title = $response['title'];
      }
      if(array_key_exists("detalii", $response)) {
        $self->detalii = $response['detalii'];
      }
      return $self;
    }  

    public static function all($conn) {
      $result = array();
      $response = allTrunked($conn, "toDo", "toDo", new Todo, self::$valTypes);
      return $response;
    }

    public static function where($conn, $params) {
      $result = array();
      $response = whereRecordByMultiTrunk($conn, $params, "toDo", "toDo", new Todo, self::$valTypes);
      return $response;
    }

    public function tranfer($records) {
      $result = array();
      for($i = 0; $i < count($records); $i++) {
        $self = new Todo;
        
      if(array_key_exists("id", $records[$i])) {
        $self->id = $records[$i]['id'];
      }
      if(array_key_exists("title", $records[$i])) {
        $self->title = $records[$i]['title'];
      }
      if(array_key_exists("detalii", $records[$i])) {
        $self->detalii = $records[$i]['detalii'];
      }
        array_push($result, $self);
      }
      return $result;
    }

    public static function insertMulti($conn, $records) {
      $response = multiInsert($conn, "toDo", "toDo", $records, self::$valTypes);
      if(!$response[0]) {
        return 0;
      }
      return 1;
    }

    public static function updateByIdMulti($conn, $records) {
      $response = updateByIdMulti($conn, "toDo", "toDo", $records, self::$valTypes);
      if(!$response[0]) {
        return 0;
      }
      $ids = array();
      foreach($records as $record) {
        array_push($ids, $record['id']);
      }
      return Todo::where($conn, array(
        "id" => $ids
      ));
    } 

    public static function insert($conn, $params) {
      $response = insertGeneral($conn, "toDo", "toDo", $params, self::$valTypes);
      if(!$response[0]) {
        return 0;
      }
      
      return Todo::get($conn, array(
        'id' => $response[1]
      ));
    }

    public static function objectCount($conn, $clause) {
      return getCount($conn, "toDo", "toDo", $clause);
    }

    public function update($conn, $params) {
      $new = Todo::get($conn, array(
        "id" => $this->id
      ));
      if(!$new) {
        return 0;
      }
      $response = updateById($conn, "toDo", "toDo", "id", $new->id, $params, self::$valTypes);
      if(!$response[0]) {
        return 0;
      }
      $new = Todo::get($conn, array(
        "id" => $this->id
      ))->to_array();
      
      if(array_key_exists("id", $new)) {
        $this->id = $new['id'];
      }
      if(array_key_exists("title", $new)) {
        $this->title = $new['title'];
      }
      if(array_key_exists("detalii", $new)) {
        $this->detalii = $new['detalii'];
      }
      return 1;
    } 

    public static function pluck($conn, $param, $array) {
      $response = array();
      for($index = 0; $index < count($array); $index++) {
        $current_object = $array[$index]->to_array();
        if(array_key_exists($param, $current_object)) {
          array_push($response, $current_object[$param]);
        }
      }
      return $response;
    } 

    public function delete($conn) {
      $current = Todo::get($conn, array(
        "id" => $this->id
      ));
      if(!$current) {
        return 0;
      }
      $current = $current->to_array();
      
      if(array_key_exists("id", $current)) {
        $this->id = $current['id'];
      }
      if(array_key_exists("title", $current)) {
        $this->title = $current['title'];
      }
      if(array_key_exists("detalii", $current)) {
        $this->detalii = $current['detalii'];
      }
      $response = deleteRecordBy($conn, "toDo", "toDo", "id", $current["id"]);
      return $response[0];
    } 

    public static function execute($conn, $params) {
    } 

    public static function map_to_object($conn, $params) {
      $self = new Todo;
      
      if(array_key_exists("id", $params)) {
        $self->id = $params['id'];
      }
      if(array_key_exists("title", $params)) {
        $self->title = $params['title'];
      }
      if(array_key_exists("detalii", $params)) {
        $self->detalii = $params['detalii'];
      }
      return $self;
    }

    public function save($conn) {

    }

    public function to_array() {
      return get_object_vars($this);
    } 
  }
  
?>
  