.. _idx_config:

=============
Configuration
=============

All Idephix configurations are defined within the ``idxrc.php`` file.
By default Idephix will look for a file named ``idxrc.php`` in the root
directory of your project, but you can store it wherever you want and
name it whatever you want. If you want to use a custom configuration file
you need to specify id by using ``-c`` option with Idephix CLI.

The file **must** return an array of configurations. Idephix uses 3 main configuration elements:

- environments
- ssh_client
- extensions

None of them are mandatory, you'll need environments (at least one) and ssh_client only to execute remote
tasks and extensions only if you want to register some extension.

This example of ``idxrc.php`` file will give you and idea of how define environments, ssh clients
and extensions:

::

    <?php

    $environments = array(
        'prod' => array(
            'hosts' => array('127.0.0.1', '33.33.33.10'),
            'ssh_params' => array(
                'user' => 'ideato'
            ),
        ),
        'stage' => array(
            'hosts' => array('192.168.169.170'),
            'ssh_params' => array(
                'user' => 'ideato'
            ),
        ),
        'test' => array(
            'hosts' => array('127.0.0.1'),
            'ssh_params' => array('user' => 'kea'),
        ),
    );

    return array(
        'envs' => $environments,
        'ssh_client' => new \Idephix\SSH\SshClient(),
        'extensions' => array(),
    );

Idephix use ssh-agent to authenticate to remote computers without password.
Otherwise you can specify the password in your script or use ``CLISshProxy``
(instead of the default ``PeclSsh2Proxy``) that ask you the password.

Once you have defined several environments you can specify which one you want to run
your remote task against, using ``--env`` CLI option.