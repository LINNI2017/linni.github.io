# Food API Documentation
The Food API provides food information, includes abstract and calories info, and user information, includes username and password.

## Abstract of Usage
**Mode:**
- `log` for user to log in with valid account
- `reg` for user to register with a new valid account
- `search` for log-in user to search food
- `update` for log-in user to update food database

**Possible GET Parameter Set:**
- `mode=log` & `name={username}` & `pass={password}`  

- `mode=search` & `type=single` & `food={food-name}`  

- `mode=search` & `type=multi`  
    & `table={table-name}` & `show={display number}`  
    & `macro={macro-nutrient}` & `maorder={macro-nutrient order}`  
    & `micro={micro-nutrient}` & `miorder={micro-nutrient order}`   
    & `min={lower bound of calories}` & `max={upper bound of calories}`   
    & `key={keyword of food}`  

**Possible POST Parameter Set:**
- `mode=reg`, `name={username}`, `pass={password}`
- `mode=update`, `food={food-name}`, `abs={food-abstract}`, `cal={food-calories}`

## Search Single Food in Local Table
**Request Format:** food.php?mode=search&type=single&food={food-name}

**Request Type:** GET

**Returned Data Format**: JSON

**Description:** Given a valid food name, it returns a JSON of the food information. A valid food name does not contain any spaces, and it is required to be English letters and space, case of letter does not matter.

**Example Request:** food.php?mode=search&food=apple

**Example Response:**

```json
{
    "name": "apple",
    "abstract": "fruit & perfect daily desert",
    "calories": "52"
}
```

**Error Handling:**
- If missing a parameter, it will 400 error with: `Error: Missing parameters`
- If passed in an unknown food name, it will 400 error with: `Error: Unknown food.`

## Search Food with Multiple Filters in Professional Table
**Request Format:**food.php?mode=search&type=multi  
&table={table-name}&show={display number}      
&macro={macro-nutrient}&maorder={macro-nutrient order}  
&micro={micro-nutrient}&miorder={micro-nutrient order}     
&min={lower bound of calories}&max={upper bound of calories}        
&key={keyword of food}

**Request Type:** GET

**Returned Data Format**: JSON

**Description:** Given a valid food name, it returns a JSON of the food information. A valid food name does not contain any spaces, and it is required to be English letters and space, case of letter does not matter.

**Example Request:** food.php?mode=search&type=multi&table=Grocery&show=2&macro=nf_protein  
&maorder=asc&micro=nf_sugars&miorder=asc&min=10&max=1000&key=beef

**Example Response:**

```json
{
    {
        "Brand": "HMR Entree",
        "Food": "Bean and Beef Enchiladas",
        "Calories": "210",
        "Fat": "3",
        "Carbohydrate": "35",
        "Protein": "11",
        "Sugars": "6"
    },
    {
        "Brand": "Smithfield",
        "Food": "Sausage, Smoked",
        "Calories": "280",
        "Fat": "23",
        "Carbohydrate": "4",
        "Protein": "12",
        "Sugars": "1"
    }
}
```

**Error Handling:**
- If missing a parameter, it will 400 error with: `Error: Missing parameters`
- If passed in an unknown food name, it will 400 error with: `Error: No Matched Food in the Database.`

## Add New Food into Local Table
**Request Format:** food.php endpoint with POST parameters of `mode`, `food`, `abs` and `cal`

**Request Type**: POST

**Returned Data Format**: Plain Text

**Description:** Given a mode `update`, a valid food name `food`, abstract `abs`, calories number `cal`, cover boolean `cover` to send, the food will reply with a plain text message response.

**Example Request:** food.php with POST parameters of `mode=update`, `food=apple pie`, `abs=sweet&fat explosion` and `cal=500`, `cover=no`

**Example Response:**

```
Success: Add New Food 'apple pie' to Database.
```

## Update Existed Food in Local Table
**Request Format:** food.php endpoint with POST parameters of `mode`, `food`, `abs` and `cal`

**Request Type**: POST

**Returned Data Format**: Plain Text

**Description:** Given a mode `update`, a valid food name `food`, abstract `abs`, calories number `cal` to send, the food will reply with a plain text message response.

**Example Request:** food.php with POST parameters of `mode=update`, `food=pineapple`, `abs=fruit&sour&sweet&great resources of Vitamin C`, `cal=50`, `cover=yes`

**Example Response:**
- If passed in an existed food, `cover=yes`, means that user allows to override previous food record, update food database.

```
Success: Update database.
```

**Example Request:** food.php with POST parameters of `mode=update`, `food=pineapple`, `abs=fruit&sour&sweet&great resources of Vitamin C`, `cal=50`, `cover=no`

**Example Response:**
- If passed in an existed food, `cover=no`, it will print out existed food record.

```
{
    "name": "apple piee",
    "abs": "sweet&crispy",
    "cal": "52"
}
```

**Error Handling:**
- If missing some parameter, it will 400 error with: `Error: Missing parameters.`
- If passed in an invalid calories number, it will 400 error with:  `Error: Invalid calories number.` 

## User Login Account in Local Table
**Request Format:** food.php?mode=log&name={username}&pass={password}

**Request Type:** GET

**Returned Data Format**: JSON

**Description:** Given a valid food name, it returns a JSON of the food information. A valid food name does not contain any spaces, and it is required to be English letters and space, case of letter does not matter.

**Example Request:** food.php?mode=log&name=apple&pass=aaa

**Example Response:**

```
Success: Log in.
```

**Error Handling:**
- If passed in an unknown username, it will 400 error with: `Error: Unknown username.`
- If passed in a known username but invalid password, it will 400 error with: `Error: Existed username and Invalid password.`

## User Register Account in Local Table
**Request Format:** food.php endpoint with POST parameters of `mode`, `name` and `pass`

**Request Type**: POST

**Returned Data Format**: Plain Text

**Description:** Given a mode `reg`, a valid username `name`and a valid password `pass` to send, the food will reply with a plain text message response.

**Example Request:** food.php with POST parameters of `mode=reg`, `name=tester1`, `pass=aaa`

**Example Response:**

```
Success: New account.
```

**Error Handling:**
- If passed in an existed username, it will 400 error with: `Error: Existed username.`
- If passed in an invalid and unexisted username, it will 400 error with: `Error: Invalid Username.`
- If passed in a valid username but an invalid password, it will 400 error with: `Error: Invalid Password.`
- If passed in a valid username but an invalid password, it will 400 error with:  `Error: Existed Username but Invalid Password.`
