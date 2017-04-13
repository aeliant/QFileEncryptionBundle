# Key Generation Procedure
Author: 
## Using the console
If you want to generate a key pair manually in the console, a command is available:  
```bash
$ bin/console qfe:gen-key [options]
```

There are three mandatory options :
  * `-u`, `--username`:  Option used to specify the username for the key pair
  * `-r`, `--recipient`: Option used to specify the recipient for the key pair
  * `-p`, `--passphrase`: Option used to specify the passphrase for the key pair
 
Example:  
```bash
$ bin/console qfe:gen-key -u querdos -r querdos@gmail.com -p dumbPassphrase
```

Suposing you haven't changed the default `gnupg_home`, it will create a directory `username` and store the generated key
pair in it. Then it will create a local trust database by importing the public and private key.
```
-- gnupg_home
    |-- username_1
        |-- username_1.pub
        |-- username_1.sec
        |-- trustdb.gpg
    |-- username_2
        |-- username_2.pub
        |-- username_2.sec
        |-- trustdb.gpg
    ..
 ```          
Finally, the command will persist these informations in database with the `QKey` entity composed as followed:
  * The username value for the key pair
  * The recipient value for the key pair
  * The passphrase hashed (using `BCRYPT`) value for the key pair
  
## Using a controller

In a controller, it's not much difficult to generate a key pair. A service is available to create the key pair:
`qfe.util.asymetric`. You can use the `generate_key` method with the following parameter
  * `$user_interface`: an instance of the `UserInterface` class, which will be used to retrieve the username 
  and eventually the email to set it as the recipient
  * `$passphrase`: a plain passphrase that will be used for the key pair generation (will be hashed in database)
  * `$recipient`: a valid email used for the key generation. If specified, it will have a higher priority than the user 
  interface and will be used instead. Otherwise, if not set, the method will check the existance of a `getEmail()` method
  in the `UserInterface` specified by your application. If both are null, a `KeyOptionException` exception will be thrown.

Suppose you have a form that submit the generation demand: 
```php
    <?php
    //...
    public function generateKeyAction(Request $request)
    {
        //...
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $formData = $form->getData();
            
            $this
                ->get('qfe.util.asymetric')
                
                // with a recipient different than the user
                ->generate_key($user, $passphrase, $recipient)
                
                // no recipient specified, using the user email
                ->generate_key($user, $passphrase)
            ;
        }
    }
``` 
## Exceptions
If you want to handle efficiently exception for your application, there are the main exceptions that can be thrown 
with the key pair generation:
  * `KeyOptionException` is thrown when a specified option don't pass the tests for GnuPG. For example a malformed 
  recipient, no username specified, etc...
  * `KeyGenerationException` is thrown when the key generation has failed for other problem than the specified options.
  To know why the generation has failed, you can refer to the generated `qfe_error.log` in the logs folder (specified in 
  the `config.yml` file in the installation procedure)
  * `KeyImportExcemtion` is thrown when the trust database creation has failed (when trying to import the public and private
  key). Again, for more details about the error, please refer to your `qfe_error.log` file.