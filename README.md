# QFileEncryptionBundle
The QFileEncryption Bundle is a symfony bundle that attempts to ease files encryption

  * GnuPG is used to encrypt/decrypt files
  * Automatically rename and associate encrypted files
  * Can be used to generate a download link and immediately delete the decrypted file

The main goal is to provide a simple PHP interface between the well known GnuPG and your application. For a brief 
description on how the bundle handle the procedures:
  * It can generate a key pair for a given user (with a passphrase of course) and store them in the specified gnupg home 
  (in the `config.yml`). The point is that it will not store the keypair in the main GPG database but will create a local 
  trust database for each user and will not perturb the main functionning of the host with these keys.
  * For the encryption part, the bundle take the actual path of the file we want to encrypt, store the original
  name and generate a new one for this file, encrypt and remove the original file. The association is then stored in 
  database
  * For the decryption part, the bundle takes a stored encrypted file (`QFile`), a user associated key (`QKey`) and a 
  passphrase (which is checked before trying decryption). It then try to decrypt the file, store it the `/tmp` directory
  generate a `BinaryFileResponse` (a response you can use to generate a download link in your application) and then remove 
  the temporary decrypted file.
  
# Documentation
For usage documentation, see:  
[Resources/doc/index.rst](https://github.com/Querdos/QFileEncryptionBundle/blob/master/Resources/doc/index.rst)
