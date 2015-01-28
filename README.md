### Create a PHP class for use with the mysqli driver.

Classy will create PHP classes that provide an abstraction layer between the application and the database. This is not your typical database abstraction layer; it does NOT present a set of "safe" functions that your application can use to manipulate the database. Instead, it creates a class that presents a set of safe CRUD type functions to manipulate your DATA - regardless of how those data are managed.

I try to follow two general rules regarding my apps and how they access data:

  - The app should never use SQL statements in the business logic.
  - The app should never know, or care, what database engine it's talking to.

The generated classes present the db table in a classic OPP fashion, which also satisfies the above rules. Accessing your data in an OOP fashion has numerous benefits, one being to ensure the integrity of your data. For example, these classes are immune to sql injection attacks. No prior filtering or checking needed. 

### Classy will:
- Creates variable definitions, constructor, load(), delete(), insert() and update() functions. The ctor uses the default values from the table, or sane defaults if none are specified.
- Detects data types from table description and applies type casts where appropriate.
- Static access functions are also created.
- Standard db access, or prepared statements. If 'prepared' and 'standard' are selected, both are generated, but the standard code is commented out.
- Handles tables with AUTO_INCREMENT key structure. _**For tables using more complex key structure it creates place-holders for that logic.**_
<br>

### Example class
[Click to view a sample generated class](https://github.com/chriscurran/classy/wiki/Example-generated-class)

### Expanding
The functions generated are the basic CRUD type functions. It is expected that you will add functions to the class to perform additional tasks. For example, lets say you had a customer table and wanted everyone in the 33511 zip code. Instead of:
```
    $result = $DB->query("SELECT * FROM customer WHERE zip='$zip'");
```
create a new function in the customerT class:
```
    public function getCustomersByZip($zip) {
        //
        // do the SQL query in here and return a normailzed result
        //
        return $data_array;
    }
```
then refer to it as:
```
    $customers = $customer->getCustomersByZip("33511");
```



### Setup/config
There is no "config" file - control variables and defines are set at the top of the generator script (classy.php). Open classy.php in your editor.
```
        //
        // Choose which access method to use when creating class variables.
        //     - If you use "private", getters and setters will be generated.
        //     - If you use "public", getters and setters will not be generated.
        //
        $ACCESS_METHOD = 'private';
        //$ACCESS_METHOD = 'public';

        //
        // what kind of mysql code to generate
        //
        define('DO_STANDARD', true);
        define('DO_PREPARED', true);
        define('DO_STATIC', true);
```

After setting these values, you will need to setup access to your mysql server. Edit as needed for your mysql connection.
```
	// 
	// define db settings based on what machine we're on
	// 
	//
	// local dev setup
	//
	$db_host = "localhost";
	$db_user = "db_user_name";
	$db_pw 	 = "db_user_password";
	$db_name = "db_name";
```


### Running the tool

At a command line prompt enter:
```
    > php classy.php <table_name> <class_name>
```
where \<table_name\> is the name of the mysql table, and \<class_name\> is the class name to generate.
```
    > php classy.php libchat_depart_online libChatDepartOnlineT>libChatDepartOnlineT.php
```


### Using the generated class
```
	//
	// load customer id "123"
	//
	$c = new customerT($DB);	//creates an empty customer record using sql default values
	$record = $c->load("123");	//$record is a reference to $c. See 'update' below for alternate usage.
	if ($record == null) {
		die($c->get_last_error());
	}
	// $record and $c now point to the same object
```

```
	//
	// create and insert a new record into 'customer'
	//
	$c = new customerT($DB);	//creates an empty customer record using sql default values
	$c->set_name("Joe Smith");
	$c->set_addr("123 Main Street");
	$c->set_city("Brandon");
	$c->set_state("FL");
	$c->set_zip("33511");
	if ($c->insert() == -1) {
		die($c->get_last_error());	
	}

	//
	// create and insert a new record into 'customer' using a JSON object as the source
	//
	$c = new customerT($DB, $jsonObj);	//creates an empty customer record using $jsonObj
	if ($c->insert() == -1) {
		die($c->get_last_error());	
	}
```

```
	//
	// update an existing record in 'customer'
	//
	$c = new customerT($DB);	//creates an empty customer record using sql default values
	if ($c->load("123") == null) {
		die($c->get_last_error());
	}
	$c->set_zip("33511");
	if ($c->update() == -1) {
		die($c->get_last_error());	
	}

	//
	// update an existing record in 'customer' with the contents of a json object
	//
	$c = new customerT($DB);	//creates an empty customer record using sql default values
	if ($c->load("123") == null) {
		die($c->get_last_error());
	}
	if ($c->update($jsonObj) == -1) {
		die($c->get_last_error());	
	}
```
