# Magento 2 deployer configuration
Follow the documentation of [deployer](https://deployer.org/) to installed things. Then copy this
`deploy.php` to your project. Next, modify the following:

- Modify the variable `repository` to point to your git repository.

- Modify the hosts and `deploy_path` and optionally add hosts to it.

- Optionally, modify the `bin/php` variable. For instance, if the system has multiple PHP versions installed, use the binary that fits best.
