<?php
namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'reviewers.exchange');

// Project repository
set('repository', 'https://github.com/sirking1991/reviewers.exchange.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);


// Hosts

host('18.140.4.50')
    ->stage('production') 
    ->user('ubuntu')   
    ->set('deploy_path', '/home/ubuntu/Sites/reviewers.exchange');   
    
// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'artisan:migrate');

