import requests
import json
import os

cookie = None
os.environ['NO_PROXY'] = '127.0.0.1:5001'
IP = '127.0.0.1:5001'

# test for Post
def testTaskPost(): 
  formular = {
    "title": "programare",
    "detalii": "app for management money",
  }
  response = requests.post(f"http://{IP}/API/Controllers/TodoController.php",
                            headers={"Cookie":cookie}, data=json.dumps(formular))
  jsonData = json.loads(response.text)
  assert "Error" not in jsonData or print(jsonData)
  print("Test testTaskPost passed!")

# test for Get
def testTaskGet():
  response = requests.get(f"http://{IP}/API/Controllers/TodoController.php",
                            headers={"Cookie":cookie})
  jsonData = json.loads(response.text)
  assert "Error" not in jsonData and jsonData['count'] > 0 or print(jsonData)
  print("Test testTaskGet passed!")
 
# test for Update  
def testTaskUpdate():
  response = requests.get(f"http://{IP}/API/Controllers/TodoController.php",
                            headers={"Cookie":cookie})
  objects = json.loads(response.text)
  assert "Error" not in objects or print(objects)
  formular = {
    "id": objects['records'][0]['id'],
    "detalii": "app for work",
  }
  response = requests.post(f"http://{IP}/API/Controllers/TodoController.php?patch=true",
                            headers={"Cookie":cookie}, data=json.dumps(formular))
  jsonData = json.loads(response.text)
  assert "Error" not in jsonData or print(jsonData)
  print("Test testTaskUpdate passed!")

# test for Delete  
def testTaskDelete():
  response = requests.get(f"http://{IP}/API/Controllers/TodoController.php",
                            headers={"Cookie":cookie})
  objects = json.loads(response.text)
  assert "Error" not in objects or print(objects)
  response = requests.delete(f"http://{IP}/API/Controllers/TodoController.php?id={objects['records'][0]['id']}",
                            headers={"Cookie":cookie})
  jsonData = json.loads(response.text)
  assert "Error" not in jsonData or print(jsonData)
  print("Test testTaskDelete passed!")
  
import testDB
def testAll():
  testTaskPost()
  testTaskGet()
  testTaskUpdate()
  testTaskDelete()
  print("Congrats!Tests passed!")  
  
testAll()