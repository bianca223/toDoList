<?php
?>

<html>
  <head>
    <link rel="stylesheet" href="styles/styles.css"></link>
    <script src="scripts/taskList.js"></script>
    <script src="scripts/utils.js"></script>
  </head>
  <body>
    <div class="container">
      <div class="content-box-parent">
        <div class="title-part">
          <h1 class="title">TO DO LIST</h1>
        </div>
        <div class="form-group">
          <div class="input-parts-childs">
            <label>Titlu</label>
            <input type="text" class="form-field" id='tile_id'></input>
          </div>
          <div class="input-parts-childs">
            <label>Detalii</label>
            <input type="text" class="form-field" id='detalii_id'></input>
          </div>
          <button class="submit-button" type="submit" value='SUBMIT' onclick="createTask()">Adauga Task Nou</button>
        </div>
      </div>
      <div class="table" id="table_id"></div>
    </div>
  </body>
</html>