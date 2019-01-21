<?php
namespace Deployer;

require 'recipe/common.php';

set('bin/php', '/usr/bin/php');
set('bin/composer', '/usr/bin/php /usr/local/bin/composer');

set('shared_files', [
    'app/etc/env.php',
    'var/.maintenance.ip',
]);

set('shared_dirs', [
    'var/log',
    'var/backups',
    'pub/media',
]);

set('writable_dirs', [
    'var',
    'pub/static',
    'pub/media',
]);

set('clear_paths', [
    'generation/*',
    'var/generation/*',
    'var/di/*',
    'var/cache/*',
]);

set('repository', 'git@foobar.com:foo/bar.git');
set('git_tty', true);

// Hosts
host('production')
    ->stage('production')
    ->set('deploy_path', '/var/www/html');    
    
// Tasks
desc('Preparing deployment');
task('magento:prepare', function () {
    run("chmod 755 {{release_path}}/bin/magento");
});

desc('Compile magento di');
task('magento:compile', function () {
    run("{{bin/php}} -d memory_limit=1024M {{release_path}}/bin/magento setup:di:compile");
});

desc('Deploy assets');
task('magento:deploy:assets', function () {
    run("{{bin/php}} -d memory_limit=1024M {{release_path}}/bin/magento setup:static-content:deploy -s quick");
});

desc('Enable maintenance mode');
task('magento:maintenance:enable', function () {
    run("if [ -d $(echo {{deploy_path}}/current) ]; then {{bin/php}} {{deploy_path}}/current/bin/magento maintenance:enable; fi");
});

desc('Disable maintenance mode');
task('magento:maintenance:disable', function () {
    run("if [ -d $(echo {{deploy_path}}/current) ]; then {{bin/php}} {{deploy_path}}/current/bin/magento maintenance:disable; fi");
});

desc('Upgrade magento database');
task('magento:upgrade:db', function () {
    run("{{bin/php}} {{release_path}}/bin/magento setup:upgrade --keep-generated");
});

desc('Upgrade magento database if needed');
task('magento:upgrade:db-ifneeded', function () {
    $mage = '{{bin/php}} {{release_path}}/bin/magento';
    run("$mage setup:db:status -q || $mage maintenance:enable && $mage setup:upgrade --keep-generated && $mage maintenance:disable");
});

desc('Flush Magento Cache');
task('magento:cache:flush', function () {
    run("{{bin/php}} {{release_path}}/bin/magento cache:clean");
});

desc('Magento2 deployment operations');
task('deploy:magento', [
    'magento:prepare',
    'magento:compile',
    'magento:deploy:assets',
    'magento:upgrade:db-ifneeded',
    'magento:cache:flush',
]);

desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:magento',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

after('deploy:failed', 'magento:maintenance:disable');
