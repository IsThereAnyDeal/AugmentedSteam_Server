<?php
namespace Deployer;

require "recipe/common.php";

import(__DIR__."/deployer.yaml");

set("auto_ssh_keygen", false);

add("shared_files", [
    ".config.json"
]);
add("shared_dirs", [
    "logs", "temp", "bin"
]);
add("writable_dirs", []);

add("clear_paths", [
    ".deployer.template.yaml",
    ".gitignore",
    "composer.json",
    "composer.lock",
    "deploy.php",
    "install.sql",
    "phpstan.neon"
]);

task("push", [
    "deploy:prepare",
    "deploy:vendors",
    "deploy:clear_paths"
]);

task("publish", [
    "deploy:publish"
]);

task("deploy", [
    "push",
    "publish"
]);

after('deploy:failed', 'deploy:unlock');
