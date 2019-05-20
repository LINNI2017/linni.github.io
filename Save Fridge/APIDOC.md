# Food API Documentation
The Food API provides food information, includes abstract and calories info, and user information, includes username and password.

## Lookup Food Information
**Request Format:** food.php?mode=food&food={food-name}

**Request Type:** GET

**Returned Data Format**: JSON

**Description:** Given a valid food name, it returns a JSON of the food information. A valid food name does not contain any spaces, and it is required to be English letters and space, case of letter does not matter.

**Example Request:** food.php?mode=food&food=apple

**Example Response:**
*Fill in example response in the {}*

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

## Update New Food Information
**Request Format:** food.php endpoint with POST parameters of `mode`, `food`, `abs` and `cal`

**Request Type**: POST

**Returned Data Format**: Plain Text

**Description:** Given a mode `food`, a valid food name `food`, abstract `abs`, calories number `cal` to send, the food will reply with a plain text message response.

**Example Request:** food.php with POST parameters of `mode=food`, `food=pineapple`, `abs=fruit&sour&sweet&great resources of Vitamin C` and `cal=50`

**Example Response:**

```
Success: Update database.
```

**Error Handling:**
- If missing some parameter, it will 400 error with: `Error: Missing parameters.`
- If passed in an invalid calories number, it will 400 error with:  `Error: Invalid calories number.`
- If passed in an existed food, it will 400 error with: `Error: Existed food.` 

## User Login Account
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

## User Register Account
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
- If passed in a valid username but an invalid password, it will 400 error with:  `Error: Invalid Password.`
