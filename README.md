#Intraway TEST

Intraway TEST is a RESTful API for MySQL database.

##Requirements

- PHP 5.3+ & PDO
- MySQL 

##Installation

DATABASE

- Create database intraway
- import file intraway.sql to databse intraway
- Edit `MessagesDB.php` and change the `const` : (LOCALHOST,USER,PASSWORD,DATABASE) located at the top, here are some examples:



##API Design

The actual API design is very straightforward and follows the design patterns of the majority of APIs.

	Create > POST   /action
	Read   > GET    /action
	Read   > GET    /action[/id]
	Read   > GET    /action[/column/content]
	Delete > DELETE /action/id

	Test of how you would use the Intraway TEST

	# Get all rows from the "messages" table
	GET http://localhost:82/intraway/v1/messages

	# Get a single row from the "messages" table (where "1" is the ID)
	GET http://localhost/intraway/v1/messages/1
	
	# Get a single row from the "messages" table (where "1" is the ID)
	GET http://localhost/intraway/v1/messages?id=1

	# Get all rows from the "messages" table where the "status" field matches "aa" (`LIKE`)
	GET http://localhost/intraway/v1/messages?q=aa&p=1&r=5

	# Get 3 rows from the "messages" table where the "status" field matches "aaa b" (`LIKE`)
	GET http://localhost/intraway/v1/messages?q=aaa b&r=3
	
	# Get 3 rows with page 2 from the "messages" table where the "status" field matches "aa" (`LIKE`)
	GET http://localhost/intraway/v1/messages?q=aa&p=2&r=3

	# Create a new row in the "messages" table where the POST data corresponds to the database fields
	POST http://localhost/intraway/v1/publishMessage
	- JSON ARRAY : {
					"email" : "test3@gmail.com",
					"status" : "Message3.... aaaa bbbb"
				   }

	# Delete message "1" from the "messages" table
	DELETE http://localhost/intraway/v1/messages/1

##Responses

All responses are in the JSON format. A `GET` response from the `customers` table might look like this:

json
[
	{
		"id": "2",
		"email": "test2@gmail.com",
		"status": "Message2.... aaaa bbbb",
		"created_at": "2016-11-27 14:03:39"
	},
	{
		"id": "1",
		"email": "test@gmail.com",
		"status": "Message.... aaaa bbbb",
		"created_at": "2016-11-27 14:03:29"
	}
]

Successful `POST` responses will look like:

json
[
	{
		"status": "success",
		"message": "new record added"
	}
]

Successful `PUT` and `DELETE` responses will look like:

json
[
	{
		"status": "success",
		"message": "Record 1 delete"
	}
]

Errors are expressed in the format:

json
[
	{
		"status": 400,
		"message": "Bad Request"
	}
]

The following codes and message are avaiable:

* `200` OK
* `201` Created
* `204` No Content
* `400` Bad Request
* `403` Forbidden
* `404` Not Found
* `409` Conflict
* `503` Service Unavailable

Also, if the `callback` query string is set *and* is valid, the returned result will be a [JSON-P response](http://en.wikipedia.org/wiki/JSONP):

javascript
callback(JSON);

Ajax-like requests will be minified, whereas normal browser requests will be human-readable.

##License

Copyright (c) 2016 Ariel Diaz (arieldiaz23@gmail.com).
