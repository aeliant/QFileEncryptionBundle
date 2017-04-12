Installation and usage
============

Step 1: Prerequesites
---------------------

The only important thing you have to install before enabling this bundle is 
[GnuPG](https://www.gnupg.org/index.html).

Step 2: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

    $ composer require querdos/qfile-file-encryption "~1"

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

Step 3: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the ``app/AppKernel.php`` file of your project:

    <?php
    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...

                new Querdos\QFileEncryptionBundle\QFileEncryptionBundle(),

                // ...
            );

            // ...
        }

        // ...
    }

Step 4: Configuration
---------------------

For now, only one engine is supported:  
  * [ORM](http://www.doctrine-project.org/projects/orm.html)

More support will come as soon as possible.  
You can noww configure your configuration file ans specify these following options:

    # app/config/config.yml
    {...}
    q_file_encryption:
        # Default GPG directory (default: ~/.gnupg)
        gnupg_home: /path/to/.gnupg

The `gnupg_home` directory will be used to store key pairs for each users and use them later to encrypt/decrypt files for the given user.

Update your database schema by running the following  command:
    $ bin/console doctrine:schema:update --force
Or if you have the `DoctrineMigrationBundle` enabled:
    $ bin/console doctrine:migration:diff && bin/console doctrine:migration:migrate

Finally, make sure that your application has at least validations enabled (you can add annotation validation if you want)

    # app/config/config.yml
    {...}
    framework:
        validation:    { enabled: true }
        # to enable validation with annotation:
        # validation:   { enabled: true, enable_annotations: true }

    

Step 5: Usage
-------------

Please refer to the [usage] documentation page for more details on how to use this bundle.

