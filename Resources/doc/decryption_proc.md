# Decryption procedure
Author: Hamza ESSAYEGH <querdos>
## Using the console
If you want to decrypt an encrypted file manually using the console, a command is available:
```bash
bin/console qfe:decrypt [options] [--] file
```

There is one mandatory argument:
  * The `file` to decrypt

There are two mandatory options:
  * `-u`, `--username`: Option used to retrieve an associated `QKey` 
  * `-p`, `--passphrase`: Option used to unlock the private key for decryption.
  
Finally, there is one optionnal option:
  * `-o`, `--output`: Option used to specify where the decrypted file will be stored (only for debugging purpose).
  If not specified, the default value is in the temporary directory.
  
Example:
```bash
bin/console qfe:decrypt -u querdos -p dumbPassphrase /path/to/file.txt
```

The decrypted file will be placed in the `/tmp` directory by default. If the service `AsymetricUtil` service
is used, the decryption method will generate a `BinaryResponseData` and then remove the decrypted file for security.

## Using a controller
A service is available to decrypt a file: `qfe.util.asymetric`. You can use the `decrypt_file` method to decrypt a file.
You will need the following parameters:
  * A `QFile` object to retrieve and make the association between the randomly named file and its original name
  * A `QKey` object to allow the passphrase checking and unlock the private key
  * A `$passphrase` string unlock the private key
  
Just as a reminder, you can use these two following managers to read the database:
  * `qfe.manager.qfile`
  * `qfe.manager.qkey`

Supposing you have a controller and an associated view, and the user want to decrypt a given file, you can handle the
process by doing the following (and yes, it is as simple as it is shown !):
```php
<?php
//...
/**
 * @route("/download/{$qfile_id}", name="decrypt_and_download")
 */
public function downloadDecryptedAction($qfile_id) {
    // retrieve the associated qfile
    $qfile = $this->get('qfe.manager.qfile')->readById($qfile_id);
    
    // retrieve the associated qkey
    $qkey = $qfile->getQkey();
    
    // retrieve the binary response data
    $data = $this->get('q_fe.util.asymetric')->decrypt_file($qfile, $qkey, 'dumbPassphrase');
    
    // return the download link
    return $data;
}
```
