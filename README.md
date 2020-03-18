# deploy_hook
proof of concept drupal module to address workflow issues needing https://www.drupal.org/project/drupal/issues/2901418

@todo: update readme!

### tldr:

Create `hook_deploy_NAME` update hooks in `mymodule.deploy.php` the same way you do with post_update hooks.
Then run `drush deploy-hook:run` to run all deploy hooks that have not been run yet.
ie the sequence becomes
```
drush updb -y
drush cim -y
drush deploy-hook:run -y # it currently doesn't ask for confirmation yet
```

All aspects of this including module name and drush command names are up for debate, feedback welcome!
