# Exceptions description
If you want to handle efficiently exception for your application, there are the main exceptions that can be thrown 
with the key pair generation.

If you want informations about Import, Encryption and Decryption errors, please refer to your `qfe_error.log` file, in
in the logs folder (specified in the `config.yml` file in the installation procedure).

  * A `KeyOptionException` is thrown when a specified option don't pass the tests for GnuPG. For example a malformed 
  recipient, no username specified and so on. You can refer to the validation file 
  [Resources/config/validation.yml](https://github.com/Querdos/QFileEncryptionBundle/blob/master/Resources/config/validation.yml) 
  to see how they are managed.
  * A `KeyGenerationException` is thrown when the key generation has failed for other problem than the specified options.
  * A `KeyImportExcemtion` is thrown when the trust database creation has failed (when trying to import the public and private
  key).
  * An `EncryptionException` is thrown when GnuPG fails to encrypt data. 
  * A `DecryptionException` is thrown when GnuPG fails to decrypt the given encrypted data.
