# Encryption procedure
Author: Hamza ESSAYEGH <querdos>
## Using the console
If you want to encrypt a file manually with the console, a command is available:  
```bash
bin/console qfe:encrypt [options] [--] file
```
There is one mandatory argument:
  * The `file` to encrypt

There is one mandatory option:
  * `-u`, `--username`:  Option used to retrieve the user's directory for the encryption
 
Finally, there is one optional option:
  * `-k`, `--keep-original`: Option used to specify if you want to delete the original file after encryption or not
  
Example:  
```bash
# This command will encrypt the given file, store it and delete the original
bin/console qfe:encrypt --username querdos --recipient querdos@gmail.com /path/to/file.txt

# This command will also encrypt the given file and store it, but will keep the original file
bin/console qfe:encrypt -u querdos -r querdos@gmail.com -k /path/to/file.txt
```
Finally, a random filename will be generated for the encrypted file (with a `.enc` extension) and an entity (`QFile`) will
be created and persisted to the database. This entity has the following informations:
  * The original filename given by the user
  * The new random filename generated, used to retrieve an encrypted file
  * The path where the file has been stored

## Using a controller
A service is available to encrypt a file: `qfe.util.asymetric`. You can use the `encrypt_file` method to encrypt a file.
You will need the following parameters:
  * The `file_path` of the plain file to encrypt
  * An associated `QKey` for the current user.
  
Another service is available to retrieve the corresponding keypair: `qfe.manager.qkey`. You can use the `findByUsername`
method to retrieve the `QKey` object by the username:
```php
<?php
// Supposing you are inside a controller
$qkey = $this->get('qfe.manager.qkey')->findByUsername('querdos');
```

Let's suppose we have a file uploaded by the user using a form, here is an example of how you can encrypt his file:
```php
<?php
//...
public function encryptFileAction(Request $request)
{
    //...
    
    $form->handleRequest($request);
    if ($form->isSubmitted()) {
        // retrieving form data
        $formData = $form->getData();
        
        // retrieving the file
        $file = $formData['file'];
        $file->move('/path/to/upload/directory', $file->getClientOriginalName());
        
        // retrieving the associated key pair for the current user
        $qkey = $this
            ->get('qfe.manager.qkey')
            ->findByUsername($this->getUser()->getUsername())
        ;
        
        // encrypting the given file
        $this
            ->get('q_fe.util.asymetric')
            ->encrypt_file("/path/to/uploaded/file", $qkey)
        ;
    }
}
```

Finally, if you want to retrieve every encrypted file for the current user, you can use the `QKeyManager` again with the 
`allForUsername($username)` method:
```php
<?php
$allEncrypted = $this
    ->get('q_fe.manager.qfile')
    ->allForUsername(
        $this->getUser()->getUsername()
    )
;
```
