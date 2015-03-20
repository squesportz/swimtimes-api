# SwimTimes API
API access to the SwimTimes system.

## Requirements
- PHP 5.4+
- cURL or fsock
- An activated account on SwimTimes, with API access (<http://www.swimtimes.nl>)
- For API access send a request on <http://www.squesportz.nl/contact/>

## Installation
The file under examples can be used for easy installation. Make sure you add the required Username and Password (the one you requested in the links above)!

```php
define("USERNAME", "<<<Your username>>>");		// Username for API
define("PASSWORD", "<<<Your password>>>");		// Password for API
define("TEAM", "<<<Your team>>>");	            // Nation.clubcode or Unique ID
define("ACTIVE", "true");		                // Select only active swimmers
```

## License
MIT <http://noodlehaus.mit-license.org>