### Create a PHP class for use with the mysqli driver.

Classy will create PHP classes that provide an abstraction layer between the application and the database. This is not your typical database abstraction layer; it does NOT present a set of "safe" functions that your application can use to manipulate the database. Instead, it creates a class that presents a set of safe CRUD type functions to manipulate your DATA - regardless of how those data are managed.

I have two general rules regarding my apps and how they access data:

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

An OOP interface also allows your data to be "smart". For example, the classic `order` and `order line items` paradigm: when an order is deleted, you want to make sure that all line items associated with that order are also deleted.
```
	/** 
	 * delete an order
	 * 
	 * @param integer $key	the 'oid' to delete.
	 * 
	 * @return integer	-1 on error, 0 otherwise
	 */
	public function delete($oid) {
		$this->DB->autocommit(FALSE);		// do transactions for multiple table updates/deteles
		
		// 
		// delete all line items for 'oid' from the line items table
		// 
		if (orderLineItemT::delete($oid) != 0) {
			$this->DB->autocommit(TRUE);	// turn it back on
			return -1;
		}

		// 
		// delete 'oid' from the orders table
		// 
		$_key = intval($oid);
		if (!$this->DB->query("DELETE FROM orders WHERE oid='$_key' LIMIT 1")) {
			$this->_last_error = $DB->error; 
			$this->DB->rollback();
			$this->DB->autocommit(TRUE);	// turn it back on
			return -1;			
		}

		$this->DB->commit();			// commit the changes
		$this->DB->autocommit(TRUE);		// turn it back on
		return 0;
	}
```
Yes, you can build SQL rules on the server that can approximate this activity, but, it can be a vendor and version specific nightmare of a task to pull off in a production environment.



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
```

After setting these values, you will need to setup access to your mysql server. Edit as needed for your mysql connection.
```
	// 
	// define db settings based on what machine we're on
	// 
	define('AWS', (strpos(gethostname(),"springyaws")!==FALSE) );	
	if (!AWS) {
		//
		// local dev setup
		//
		set_include_path(get_include_path() . 
			PATH_SEPARATOR . "answers/include/common");

		$db_host = "localhost";
		$db_user = "db_user_name";
		$db_pw 	 = "db_user_password";
		$db_name = "db_name";
	}
	else {
		//
		// AWS setup
		//
		set_include_path(get_include_path() . 
			PATH_SEPARATOR . "libanswers/include/common");

		// load Registry class. Also loads RedisServer class.
		require_once "Registry.php";

		// connect to the Registry using 'la.config' as the context
		Registry::connect('la.config');

		// load our system config object into the Registry
		//	once loaded, can be accessed as 'Registry::$config' or 'Registry::get_config()'
		Registry::load_config('sysConfig');

		$db_host = Registry::$config->la_db->host;
		$db_user = Registry::$config->la_db->user;
		$db_pw   = Registry::$config->la_db->pw;
		$db_name = Registry::$config->la_db->name;

		date_default_timezone_set ("UTC");
	}
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
<br>
[An example of the code generated from the above command](https://github.com/springshare/tools/wiki/Classy-sample-output)


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