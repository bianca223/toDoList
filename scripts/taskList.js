// this is used for fetching data for the table
function fetchData(){
  url = `/API/Controllers/TodoController.php`;
  fetch(url).then(r=> r.json().then(response=> createTable(response['records'])));
}

// this is the post function that send the information about the task to the backend
function createTask(){
  let title = document.getElementById('tile_id').value;
  let detalii = document.getElementById('detalii_id').value;
  const data = {
    'title' : title,
    'detalii' : detalii
  };
  url = `/API/Controllers/TodoController.php`;
  const params = {
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data),
    method:"POST",
    dataType: "JSON"
  };
  fetch(url,params).then(r=> r.json().then(response=> window.location.reload()));
}

// this is the delete function that communicates with the backend and sends the id of the element that has to be deleted
function deleteTask(element){
  id = element.getAttribute('crt');
  url = `/API/Controllers/TodoController.php?id=${id}`;
  const params = {
    method:"DELETE",
  };
  fetch(url,params).then(r=> r.json().then(response=> window.location.reload()));
  console.log(id);
}

function updateTask(element){
  id = element.getAttribute('crt');
  redirectToPage(`update.php?id=${id}`);
}
// here is the function that i use to create the table
function createTable(response){ 
  if(response && Object.keys(response).length){ 
    const body = document.getElementById('table_id'); 
    const serializer = serializeTable(response, { 
      "id" : "ID", 
      "title" : "Titlu", 
      "detalii" : "Detalii",
      "update" : "Update",
      "delete" : "Delete"
    }) 
    // the serializeTable and createElementFromHtml are found in utils.js
    if(body){ 
      body.innerHTML = " "; 
      body.appendChild(createElementFromHTML(` 
        <div> 
          <table class="table"> 
            <thead> 
              <tr> 
                ${serializer[0]} 
              </tr> 
            </thead> 
            <tbody> 
            ${eachRow(serializer[1], response, function(row){ 
              return `class='rows'`; 
            })} 
            </tbody> 
        </div> 
      `)) 
    } 
  } else { 
    const body = document.getElementById("table_id"); 
    if(body){ 
      body.innerHTML = ` 
      <div class='msg-warning'>Nu este niciun task. Felicitari!</div> 
      ` 
    } 
    return; 
  } 
}

fetchData();