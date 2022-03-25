//With utils, I create the table that shows to tasks on the to do list 
function serializeTable(input, acceptedParams) { 
  if(input && !input.length) { 
    return [] 
  } 
  if(input.constructor === Object) { 
    return [getKeysAsTableNames(input, acceptedParams), getValuesAsTableValues(input, acceptedParams)]; 
  } 
  const multiRecords = (input, acceptedParams) => { 
    let response = []; 
    input.forEach(element => { 
      response.push(getValuesAsTableValues(element, acceptedParams)); 
    }); 
    return response; 
  } 
  return [getKeysAsTableNames(input[0], acceptedParams), multiRecords(input, acceptedParams)]; 
} 
function eachRow(rows, rowsData, functionClass) { 
  let response = ""; 
  let index = 0; 
  if(functionClass === null) { 
    functionClass = function(row) { 
      return ""; 
    } 
  } 
  if(rowsData && !rowsData.length) { 
    return ""; 
  }
  if(rows instanceof Array) { 
    rows.forEach(element => { 
      response +=  
      `
        <tr ${functionClass(rowsData[index])}> 
          ${element} 
        </tr> 
      `; 
      index++; 
    })   
  } 
  else { 
    response =  
    ` 
    <tr ${classToAdd(rows)} id=${randomID}> 
      ${rows} 
    </tr> 
    `;
  } 
  return response 
} 
function getKeysAsTableNames(records, acceptedParams) { 
  let response = []; 
  for (const [key, _] of Object.entries(records)) { 
    if(acceptedParams[key] !== undefined) { 
      response.push(acceptedParams[key]); 
    } 
  } 
  return addNamesToTable(response); 
} 
function getValuesAsTableValues(records, acceptedParams) { 
  let responseFields = []; 
  for (const [key, value] of Object.entries(records)) { 
    if(acceptedParams[key] !== undefined) { 
      if(value) { 
        responseFields.push([value, key]); 
      } 
      else { 
        responseFields.push(["", key]); 
      } 
    } 
  } 
  return addFieldsToTable(responseFields); 
} 
function addNamesToTable(names) { 
  let innerFields = ""; 
  names.forEach(name => { 
    innerFields += `<th data-field="${name}"data-sortable="true" class="bg-delonghi" onclick="sortTableBy(this)">${name}</th>`; 
  }); 
  return innerFields; 
} 
function addFieldsToTable(currentFields) { 
  let currentColumn = document.getElementById("filter") 
  if(currentColumn) { 
    currentColumn = currentColumn.value; 
  } 
  let innerFields = ""; 
  const element = document.getElementById("valueID"); 
  let specialString = ""; 
  if(element) { 
    specialString = element.value; 
  } 
  currentFields.forEach(cell => { 
    let cellString = cell[0].toString(); 
    if(currentColumn && cell[1] === currentColumn && specialString.length) { 
      let cll = JSON.parse(JSON.stringify(cellString)); 
      let specialStringCopy = JSON.parse(JSON.stringify(specialString)); 
      let index = cll.toLowerCase().indexOf(specialStringCopy.toLowerCase()); 
      if(index !== -1) { 
        cellString = cellString.slice(0, index) + `<span style="background-color:blue; color: white; font-size: 16px;">${cellString.slice(index, index + specialString.length)}</span>` + cellString.slice(index + specialString.length, cellString.length) 
      } 
    } 
    innerFields += `<td>${cellString}</td>`
  }); 
  return innerFields; 
} 
function createElementFromHTML(htmlString) { 
  var div = document.createElement('div'); 
  div.innerHTML = htmlString.trim(); 
  return div.firstChild;  
} 
function redirectToPage(path){
  window.location.href = path;
}
function getParamBy(url, field){
  var url_string = url;
  var url = new URL(url_string);
  return url.searchParams.get(field);
}