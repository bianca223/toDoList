import pymysql.cursors
import json

# here i get the data from the file config.json for authentification in MySql so please change there
fObj = open('../../config.json',)
data = json.load(fObj)
fObj.close()

# Connect to the database
connection = pymysql.connect(host=data['host'],
                             user=data['userMySql'],
                             password=data['passwordMySql'],
                             database='toDo',
                             cursorclass=pymysql.cursors.DictCursor)
# here i create the tables I need
def executeQuery(query, table):
  with connection.cursor() as cursor:
    sql = query
    cursor.execute(f"SHOW TABLES LIKE '{table}';")
    result = cursor.fetchone()
    if result != None:
      print(f"Warning Table '{table}' already exists!")
      return
    cursor.execute(sql)
    result = cursor.fetchone()
    print(f"Table '{table}' has been created succesfully!")

# delete all the previous table
def dropEverything():
  with connection.cursor() as cursor:
    sql = f"SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' AND TABLE_SCHEMA = 'toDo'"
    cursor.execute(sql)
    for table in cursor.fetchall():
      sql = f"DROP TABLE {table['TABLE_NAME']}"
      cursor.execute(sql)
    connection.commit()
    print("Tables dropped!")

dropEverything() 
# I use just one table(toDo) which has 3 fields-> id, title, detalii
executeQuery("""CREATE TABLE toDo (
              id INT(7) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              title varchar(100),
              detalii varchar(120)
              )
  """, "toDo")