
<?php

//ActiveRecords is an extension to ToDoModel
  class ModelArray {
    private $evaluation;
    private $conn;
    private $table;
    private $point;
    private $isAll;
    private $valTypes;
    private $parameters;

    function __construct($sql, $conn, $table, $point, $isAll, $valTypes, $parameters) {
      $this->evaluation = $sql;
      $this->conn = $conn;
      $this->table = $table;
      $this->point = $point;
      $this->isAll = $isAll;
      $this->valTypes = $valTypes;
      if(!$this->isAll) {
        $this->parameters = $parameters;
      }
      else {
        $this->parameters = array();
      }
    }

    public function order($by, $attr) {
      $this->evaluation .= " order by $by $attr ";
      return $this;
    }

    public function paginate($page, $perPage) {
      $offset = ($page - 1) * $perPage;
      $this->evaluation .= " LIMIT $perPage OFFSET $offset ";
      return $this;
    }

    public function execute($sql) {
      $this->evaluation .= $sql;
      return $this;
    }

    public function like($key, $regex) {
      if($this->isAll) {
        $this->evaluation .= " WHERE ($key LIKE ?) ";
      }
      else {
        $this->evaluation .= " AND ($key LIKE ?) ";
      }
      array_push($this->parameters, "%$regex%");
      $this->isAll = 0;
      return $this;
    }

    public function where($params) {
      $inffered = getKeyValuePairAND($params);
      $sql = $inffered[0];
      $this->parameters = array_merge($this->parameters, $inffered[1]);
      if($this->isAll) {
        $this->evaluation .= " WHERE ($sql) ";
      }
      else {
        $this->evaluation .= " AND ($sql) ";
      }
      $this->isAll = 0;
      return $this;
    }

    public function whereRaw($sql, $params) {
      if($this->isAll) {
        $this->evaluation .= " WHERE ($sql) ";
      }
      else {
        $this->evaluation .= " AND ($sql) ";
      }
      $this->parameters = array_merge($this->parameters, $params);
      $this->isAll = 0;
      return $this;
    }

    public function count() {
      $cEvaluation = $this->evaluation;
      $cEvaluation = str_replace("*", "COUNT(*)", $cEvaluation);
      if($this->isAll) {
        $result = mysqli_query($this->conn, $cEvaluation);
        $response = getQueryResponseAsArray($result);
        return intval($response[0]["COUNT(*)"]);
      }
      $types = ModelArray::getParams($this->parameters);
      $stmt = $this->conn->prepare($cEvaluation);
      $stmt->bind_param($types, ...$this->parameters);
      if(!$stmt->execute()) {
        return -1;
      }
      $result = $stmt->get_result();
      $response = getQueryResponseAsArray($result);
      if(count($response)) {
        return intval($response[0]["COUNT(*)"]);
      }
      return 0;
    }

    public static function getParams($fields) {
      $response = "";
      foreach($fields as $field) {
        if(is_numeric($field)) {
          $response .= 'i';
        }
        else {
          $response .= 's';
        }
      }
      return $response;
    }

    public function fetch() {
      $cEvaluation = $this->evaluation;
      if($this->isAll) {
        $result = mysqli_query($this->conn, $this->evaluation);
        $response = getQueryResponseAsArray($result);
        return $this->point->tranfer($response);
      }
      $types = ModelArray::getParams($this->parameters);
      $stmt = $this->conn->prepare($cEvaluation);
      if(!$stmt) {
        echo mysqli_error($this->conn);
        return NULL;
      }
      $stmt->bind_param($types, ...$this->parameters);
      if(!$stmt->execute()) {
        return -1;
      }
      $result = $stmt->get_result();
      $response = getQueryResponseAsArray($result);
      return $this->point->tranfer($response);
    }
  }

  function checkAcceptedParams($accepted_params, $cparams) {
    $accepted_map = array();
    foreach ($accepted_params as &$iteration) {
      $accepted_map[$iteration] = 1;
    }
    $unknown_parameters = "Unknown paramters ";
    $correct = 1;
    foreach ($cparams as $key => $value) {
      if(!array_key_exists($key, $accepted_map)) {
        $unknown_parameters .= "'" . strval($key) . "'";
        $correct = 0;
      }
    }
    if($correct) {
      return "";
    }
    return $unknown_parameters;
  }
  function createFieldData($fields) {
    $response = "";
    $index = 0;
    foreach ($fields as $key => $value) {
      if($index) {
        $response .= " , ";
      }
      if($value) 
        $response .= "$key = ? ";
      else
        $response .= "$key = NULL ";
      $index++;
    }
    return $response;
  }
  function updateById($conn, $db, $table, $key, $value, $fields, $val_types) { // not working properly
    $fieldResponse = "";
    $params = getParams($fields);
    mysqli_select_db($conn, $db);
    $fetched_params = getParamsTypes($val_types, $fields);
    $stringQuery = $fetched_params[3];
    if(!$fields) {
      return array(
        1
      );
    }
    $sql = "UPDATE $table SET $stringQuery WHERE $key = $value;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($fetched_params[0], ...$fetched_params[1]);
    if($stmt->execute()) {
      $last_id = $conn->insert_id;
      return array(
        1,
        getRecordBy($conn, "id", $last_id, $db, $table, NULL)
      );
    } 
    return array(
      0,
      mysqli_error($conn)
    );
  }

  function allTrunked($conn, $db, $table, $funtionPointer, $valTypes) { 
    mysqli_select_db($conn, $db);
    $sql = "SELECT * FROM $table";
    return new ModelArray($sql, $conn, $table, $funtionPointer, 1, $valTypes, array());
  }

  function all($conn, $db, $table) { 
    mysqli_select_db($conn, $db);
    $sql = "SELECT * FROM $table";
    $result = mysqli_query($conn, $sql);
    if(!$result) {
      return null;
    }
    $response = getQueryResponseAsArray($result);
    return $response;
  }
  function allPagi($conn, $db, $table, $page, $perPage) { 
    mysqli_select_db($conn, $db);
    $total = getCount($conn, $db, $table, NULL);
    $offset = ($page - 1) * $perPage;
    $sql = "SELECT * FROM $table LIMIT $perPage OFFSET $offset;";
    $result = mysqli_query($conn, $sql);
    if(($page - 1) * $perPage > $total) {
      return array();
    }
    if(!$result) {
      return null;
    }
    $response = getQueryResponseAsArray($result);
    return $response;
  }
  function getCurrentUrlValue($variable) {
    $url_components = parse_url($_SERVER['REQUEST_URI']);
    if (!array_key_exists('query', $url_components)) {
      return NULL; 
    }
    parse_str($url_components['query'], $params);
    if (array_key_exists($variable, $params)) {
      return $params[$variable]; 
    }
    return NULL;
  }
  function checkRequiredParams($required, $cparams) {
    $required_map = array();
    foreach ($required as &$iteration) {
      $required_map[$iteration] = 1;
    }
    $required_parameters = "Not enough parameters!";
    $count_params = 0;
    $expected = "";
    foreach ($cparams as $key => $value) {
      if(array_key_exists($key, $required_map)) {
        $count_params++;
        $expected .= $key . " ";
      }
    }
    $counted = count($required);
    if($count_params == count($required)) {
      return "";
    }
    return $required_parameters . " expected $counted but got $count_params instead! ($expected)";
  }
  function multiInsert($conn, $db, $table, $cparams, $val_types) {
    mysqli_select_db($conn, $db);
    $api_response = array();
    $questionMarks = getQuestionMarksCountMulti($cparams);
    $fetched_params = getParamsTypesMulti($val_types, $cparams);
    $keys = join(", ", $fetched_params[2]);
    $questionPairs = getPairsQuestionMark(count($cparams), $questionMarks);
    $sql = "INSERT INTO $table ($keys) VALUES $questionPairs";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($fetched_params[0], ...$fetched_params[1]);
    mysqli_select_db($conn, $db);
    if($stmt->execute()){
      $last_id = $conn->insert_id;
      return array(
        1,
        $last_id
      );
    } 
    return array(
      0,
      mysqli_error($conn)
    );
  }
  function getQuestionMarksCountMulti($params) {
    $questions = array();
    $response = "";
    foreach($params as $element) {
      for($i = 0; $i < count($element); $i++) {
        array_push($questions, "?");
      }
      return join(",", $questions);
    }
    return $response;
  }
  function getParamsTypesMulti($val_types, $params) {
    $list = array();
    $values = array();
    $keys = array();
    $keys_update = array();
    $init_params = 0;
    foreach($params as $cparam) {
      foreach($val_types as $key => $value) {
        if(array_key_exists($key, $cparam)) {
          if($value == "int") {
            array_push($list, 'i');
          }
          else {
            array_push($list, 's');
          }
          array_push($values, $cparam[$key]);
          if(!$init_params) {
            array_push($keys, $key);
          }
          array_push($keys_update, "$key = ?");
        }
      }
      $init_params = 1;
    }
    return array(
      join("", $list),
      $values,
      $keys,
      join(", ", $keys_update)
    );
  }
  function getPairsQuestionMark($total, $questions) {
    $arr = array();
    for($i = 0; $i < $total; $i++) {
      $arm = $questions;
      array_push($arr, "($arm)");
    }
    return join(",", $arr);
  }
  function updateOnDuplicate($fields) {
    $query = array();
    foreach($fields[0] as $key => $value) {
      if($key != 'id') {
        array_push($query, " $key = VALUES($key) ");
      }
    }
    $query = join(", ", $query);
    return "ON DUPLICATE KEY UPDATE $query";
  }
  function updateByIdMulti($conn, $db, $table, $cparams, $val_types) { 
    mysqli_select_db($conn, $db);
    $api_response = array();
    if(!$cparams || !count($cparams)) {
      return array(
        0
      );
    }
    $questionMarks = getQuestionMarksCountMulti($cparams);
    $fetched_params = getParamsTypesMulti($val_types, $cparams);
    $keys = join(", ", $fetched_params[2]);
    $questionPairs = getPairsQuestionMark(count($cparams), $questionMarks);
    $insertUpdate = updateOnDuplicate($cparams);
    $sql = "INSERT INTO $table ($keys) VALUES $questionPairs $insertUpdate;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($fetched_params[0], ...$fetched_params[1]);
    mysqli_select_db($conn, $db);
    if($stmt->execute()){
      return array(
        1,
      );
    } 
    return array(
      0,
      mysqli_error($conn)
    );
  }

  function imap($records, $by) {
    $recordsMap = array();
    foreach($records as $record) {
      $recordsMap[$record->to_array()[$by]] = $record;
    }
    return $recordsMap;
  }

  function isAssoc(array $arr)
  {
    if (array() === $arr) {
      return false;
    }
    return array_keys($arr) !== range(0, count($arr) - 1);
  }
  function getParams($cparams) {
    $index = 0;
    $response_keys = "";
    $response_values = "";
    foreach ($cparams as $key => $value) {
      $response_keys .= $key;
      $response_values .= "'" . $value . "'";
      if($index < count($cparams) - 1) {
        $response_keys .= ', '; 
        $response_values .= ', ';
      }
      $index++;
    }
    return array(
      "keys_string" => $response_keys,
      "values_string" => $response_values
    );
  }
  function getRecordBy($conn, $key, $id, $db, $table, $query) {
    $sql = "SELECT * FROM $table WHERE $key = $id ";
    if($query) {
      $sql .= $query;
    }
    mysqli_select_db($conn, $db);
    $result = mysqli_query($conn, $sql);
    if(!$result) {
      return null;
    }
    $response = getQueryResponseAsArray($result);
    if(isAssoc($response)) {
      return $response;
    }
    if(!count($response)) {
      return null;
    }
    return $response[0];
  }
  function getKeyValuePairAND($map)  {
    $response = array();
    $parameters = array();
    foreach($map as $key => $value)  {
      if(is_array($value)) {
        $ors = [];
        for($i = 0; $i < count($value); $i++) {
          $c_value = $value[$i];
          array_push($parameters, $c_value);
          array_push($ors, "$key = ?");
        }
        $internal = join(" OR ", $ors);
        $value = "($internal)";
        array_push($response, $value);
      }
      else {
        array_push($parameters, $value);
        array_push($response, "$key = ?");
      }
    }
    return array(
      join(" AND ", $response),
      $parameters
    );
  }
  function whereRecordByMultiTrunk($conn, $params, $db, $table, $point, $valTypes) {
    $inffered = getKeyValuePairAND($params);
    $sql = "SELECT * FROM $table WHERE ($inffered[0])";
    return new ModelArray($sql, $conn, $table, $point, 0, $valTypes, $inffered[1]);
  }
  function getRecordByMulti($conn, $params, $db, $table, $point) {
    mysqli_select_db($conn, $db);
    $record = whereRecordByMultiTrunk($conn, $params, $db, $table, $point, NULL)->fetch();
    if(!$record || !count($record)) {
      return NULL;
    }
    return $record[0]->to_array();
  }
  function getQuestionMarksCount($params) {
    $questions = array();
    for($i = 0; $i < count($params); $i++) {
      array_push($questions, "?");
    }
    return join(",", $questions);
  }
  function getParamsTypes($val_types, $params) {
    $list = array();
    $values = array();
    $keys = array();
    $keys_update = array();
    foreach($val_types as $key => $value) {
      if(array_key_exists($key, $params)) {
        if($value == "int") {
          array_push($list, 'i');
        }
        else {
          array_push($list, 's');
        }
        array_push($values, $params[$key]);
        array_push($keys, $key);
        array_push($keys_update, "$key = ?");
      }
    }
    return array(
      join("", $list),
      $values,
      $keys,
      join(", ", $keys_update)
    );
  }
  function insertGeneral($conn, $db, $table, $cparams, $val_types) {
    $respons = getParams($cparams);
    $api_response = array();
    $values = "(" . $respons["values_string"]. ")";
    $questionMarks = getQuestionMarksCount($cparams);
    $fetched_params = getParamsTypes($val_types, $cparams);
    $keys = join(", ", $fetched_params[2]);
    $sql = "INSERT INTO $table ($keys) VALUES ($questionMarks)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($fetched_params[0], ...$fetched_params[1]);
    mysqli_select_db($conn, $db);
    if($stmt->execute()){
      $last_id = $conn->insert_id;
      return array(
        1,
        $last_id
      );
    } 
    return array(
      0,
      mysqli_error($conn)
    );
  }
  function deleteRecordBy($conn, $db, $table, $key, $value) {
    $sql = "DELETE FROM $table WHERE $key = $value";
    $record = getRecordBy($conn, $key, $value, $db, $table, NULL);
    if(!$record) {
      return array(
        0,
        "Nu exista recordul cu campul $key si valoarea $value"
      );
    }
    $result = mysqli_query($conn, $sql);
    if(!$result) {
      return array(
        0,
        mysqli_error($conn)
      );
    }
    return array(
      1,
      $record
    );
  }
  function getQueryResponseAsArray($result) {
    $response = array();
    while($row = mysqli_fetch_assoc($result)) {
      array_push($response, $row);
    }
    return $response;
  }
?>
  
  