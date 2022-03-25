function fetchData(){
  id = getParamBy(window.location.href, "id");
  url = `/API/Controllers/TodoController.php?id=${id}`;
  fetch(url).then(r=> r.json().then(response=> {
    loadTagInput('tile_id',response['records'][0]['title']);
    loadTagInput('detalii_id',response['records'][0]['detalii']);
  }));
}
function updateTask(){
  id = getParamBy(window.location.href, "id");
  let title = document.getElementById('tile_id').value;
  let detalii = document.getElementById('detalii_id').value;
  const data = {
    'id' : id,
    'title' : title,
    'detalii' : detalii
  };
  url = `/API/Controllers/TodoController.php?patch=true`;
  const params = {
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data),
    method:"POST",
    dataType: "JSON"
  };
  fetch(url,params).then(r=> r.json().then(response=> redirectToPage(`index.php`)));
}
function loadTagInput(id, value){
  const element = document.getElementById(id);
  if(element && value){
    element.value = value;
  }
}
fetchData();