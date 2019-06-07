# Food API Documentation
The Food API provides food information, includes abstract and calories info, and user information, includes username and password.

## Abstract of Usage
**Base:**
- `food` for food tables
- `user` for user table

**Mode for `food`:**
- `add` for user to add food in local food table
- `update` for user to update food in local food table
- `delete` for user to delete food in local food table
- `ssearch` for user to search food in local food table by name
- `msearch` for user to search food in professional food table by filters

**Mode for `user`:**
- `log` for user to log in with valid account
- `reg` for user to register with a new valid account
- `delete` for user to delete existed valid account
- `search` for user to serach existed valid account
- `update` for user to update existed valid account

**Possible POST Parameter Set for `food`:**
- `base=food`, `mode=add`, `name={food-name}`, `abs={food-abstract}`, `cal={food-calories}`

- `base=food`, `mode=update`, `name={food-name}`, `abs={food-abstract}`, `cal={food-calories}`

- `base=food`, `mode=delete`, `name={food-name}`     

- `base=food`, `mode=ssearch`, `name={food-name}`  

- `base=food`, `mode=msearch`, `table={table-name}`,   
  `show={display number}`, `key={keyword of food}`,   
  `macro={macro-nutrient}`, `maorder={macro-nutrient order}`,   
  `micro={micro-nutrient}`, `miorder={micro-nutrient order}`,     
  `min={lower bound of calories}`, `max={upper bound of calories}`  

**Possible POST Parameter Set for `user`:**
- `base=user`, `mode=log`, `name={username}`, `pass={password}`  

- `base=user`, `mode=reg`, `name={username}`, `pass={password}`,   
  `ques={security question}`, `ans={security question answer}`  

- `base=user`, `mode=update`, `name={username}`, `pass={password}`,   
  `ques={security question}`, `ans={security question answer}`  

- `base=user`, `mode=search`, `name={username}`, `ques={security question}`,   
  `ans={security question answer}`  

- `base=user`, `mode=delete`, `name={username}`, `pass={password}` 


## Search Single Food in Local Table
**Request Format:** food.php endpoint with POST parameters of `base`, `mode`, `name`, `abs` and `cal`

**Request Type**: POST

**Returned Data Format**: JSON

**Description:** Given a base `food`, a mode `ssearch`, a valid food name `name`, it returns a JSON of the food information. A valid food name only contains letters and space, case of letter does not matter.

**Example Request:** food.php with POST parameters of `base=food`, `mode=update`, `food=apple`

**Example Response:**

```json
{
    "name": "Apple",
    "abs": "Sweet & Crispy & Daily Fruit Choice",
    "cal": "52"
}
```

**Error Handling:**
- If missing base, it will 400 error with: `Error: [PHP] Missing POST parameters. Missing base.`
- If missing mode, it will 400 error with: `Error: [PHP] Missing POST parameters. Missing mode.`
- If missing name, it will 400 error with: `Error: [PHP] Missing POST parameters. Missing name.`
- If passed in an unknown food name, it will 400 error with: `Error: Unknown Food '{food-name}' in Food Table.`

## Search Food with Multiple Filters in Professional Table
**Request Format:** food.php endpoint with POST parameters of `base`, `mode`, `table`, `show`, `macro`, `maorder`, `micro`, `miorder`, `min`, `max`,`key`

**Request Type:** POST

**Returned Data Format**: JSON

**Description:** Given a base `food`, a mode `msearch`, a valid food name `name`, a valid table name `table`, a valid display number `show`, a valid macro nutrient name `macro`, a valid macro nutrient display order `maorder`, a valid mirco nutrient name `micro`, a valid micro nutrient display order `miorder`, a valid food calories lower bound `min`, a valid food calories upper bound `max`, a valid food key word `key`, it returns a JSON of the food information.

**Example Request:** food.php with POST parameters of `base=food`, `mode=msearch`, `table=Grocery`, `show=2`, `key=beef`, `macro=nf_total_carbohydrate`, `maorder=asc`, `micro=nf_iron_dv`, `miorder=asc`, `min=10`, `max=1000`

**Example Response:**

```json
[
    {
        "Brand": "Great Value",
        "Food": "Beef Burgers",
        "Calories": "290.00",
        "Fat": "23.00",
        "Carbohydrate": "0.00",
        "Protein": "19.00",
        "Iron": "10.00"
    },
    {
        "Brand": "Smithfield",
        "Food": "Sausage, Smoked",
        "Calories": "280.00",
        "Fat": "23.00",
        "Carbohydrate": "4.00",
        "Protein": "12.00",
        "Iron": "4.00"
    }
]
```

**Error Handling:**
- If missing a parameter, it will 400 error with: `Error: [PHP] Missing POST parameters. Missing {parameter-name}.`
- If passed in an unknown food name, it will 400 error with: `Error: No Matched Food with '{food-name}' in {table-name} Table.`

## Add New Food into Local Table
**Request Format:** food.php endpoint with POST parameters of `base`, `mode`, `food`, `abs` and `cal`

**Request Type**: POST

**Returned Data Format**: Plain Text

**Description:** Given a base `food`, a mode `add`, a valid food name `food`, a valid abstract `abs`, a valid calories number `cal` to send, the food will reply with a plain text message response.

**Example Request:** food.php with POST parameters of `base=food`, `mode=add`, `food=apple pie`, `abs=sweet&fat explosion` and `cal=500`

**Example Response:**

```
Success: Add New Food 'apple pie' to Database.
```

**Error Handling:**
- If missing a parameter, it will 400 error with: `Error: [PHP] Missing POST parameters. Missing {parameter-name}.`
- If passed in an existed food name, it will 400 error with: `Error: Existed Food '{food-name}' in Food Table.`

## Update Existed Food in Local Table
**Request Format:** food.php endpoint with POST parameters of `base`, `mode`, `food`, `abs` and `cal`

**Request Type**: POST

**Returned Data Format**: Plain Text

**Description:** Given a base `food`, mode `update`, a valid food name `food`, a valid abstract `abs`, a valid calories number `cal` to send, it will reply with a plain text message response.

**Example Request:** food.php with POST parameters of `base=food`, `mode=update`, `food=apple pie`, `abs=sweet&fat explosion&cinnamon lover` and `cal=500`

**Example Response:**

```
Success: Update Existed 'apple pie' Info in Food Database.
```

**Error Handling:**
- If missing a parameter, it will 400 error with: `Error: [PHP] Missing POST parameters. Missing {parameter-name}.`
- If passed in an unknown food name, it will 400 error with: `Error: Unknown Food '{food-name}' in {table-name} Table.`
- If passed in an invalid calories number, it will 400 error with:  `Error: Invalid Food Calories. Food Calories must use number.` 

## User Login Account in Local Table
**Request Format:** food.php endpoint with POST parameters of `base`, `mode`, `name` and `pass`

**Request Type:** POST

**Returned Data Format**: JSON

**Description:** Given a base `user`, a mode `log`, a valid user name, a valid user password to send, it will reply with a plain text message response.


**Example Request:** food.php with POST parameters of `base=user`, `mode=log`, `name=aaa` and `pass=aaa`

**Example Response:**

```
Success: Log in.
```

**Error Handling:**
- If missing a parameter, it will 400 error with: `Error: [PHP] Missing POST parameters. Missing {parameter-name}.`
- If passed in an unknown username, it will 400 error with: `Error: Unknown User '{user-name}' in User Table.`
- If passed in a known username but invalid password, it will 400 error with: `Error: Existed Username '{user-name}' but Invalid Password '{user-pass}'.`
- If passed in an invalid username, it will 400 error with: `Error: Invalid Username. Length must greater than 2.`
- If passed in a valid username but invalid password, it will 400 error with: `Error: Invalid Password. Length must greater than 2.`

## User Register Account in Local Table
**Request Format:** food.php endpoint with POST parameters of `base`, `mode`, `name`, `pass`, `ques`, `ans`

**Request Type**: POST

**Returned Data Format**: Plain Text

**Description:** Given a base `user`, a mode `reg`, a valid username `name`, a valid password `pass` to send, a valid security question `ques`, and a valid security question answer `ans` to send, it reply with a plain text message response.

**Example Request:** food.php with POST parameters of `base=user`, `mode=reg`, `name=test`, `pass=aaa`, `ques=favor_city`, `ans=Ningbo`

**Example Response:**

```
Success: Registered.
```

**Error Handling:**
- If missing a parameter, it will 400 error with: `Error: [PHP] Missing POST parameters. Missing {parameter-name}.`
- If passed in an existed username, it will 400 error with: `Error: Existed Username '{user-name}'.`
- If passed in an invalid username, it will 400 error with: `Error: Invalid Username. Length must greater than 2.`
- If passed in a valid username but an invalid password, it will 400 error with: `Error: Invalid Password. Length must greater than 2.`

## User Update Account in Local Table
**Request Format:** food.php endpoint with POST parameters of `base`, `mode`, `name`, `pass`, `ques`, `ans`

**Request Type**: POST

**Returned Data Format**: Plain Text

**Description:** Given a base `user`, a mode `update`, a valid username `name`, a valid password `pass` to send, a valid security question `ques`, and a valid security question answer `ans` to send, it reply with a plain text message response.

**Example Request:** food.php with POST parameters of `base=user`, `mode=update`, `name=test`, `pass=aaaa`, `ques=favor_city`, `ans=Hangzhou`

**Example Response:**

```
Success: Update Existed 'test' Info in User Table.
```

**Error Handling:**
- If missing a parameter, it will 400 error with: `Error: [PHP] Missing POST parameters. Missing {parameter-name}.`
- If passed in an unknown username, it will 400 error with: `Error: Unknown User '{user-name}' in User Table.`
- If passed in an invalid username, it will 400 error with: `Error: Invalid Username. Length must greater than 2.`
- If passed in a valid username, but an invalid password, it will 400 error with: `Error: Invalid Password. Length must greater than 2.`
- If passed in a valid username, a valid password, but an invalid security question, it will 400 error with: `Error: Invalid Security Question. Length must greater than 2.`
- If passed in a valid username a valid password, a valid security question, but an invalid security question answer, it will 400 error with: `Error: Invalid Security Question Answer. Length must greater than 2.`
