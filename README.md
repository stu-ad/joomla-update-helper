# Joomla Update Helper
A CLI utility to automate the extension updating process on both single and multi site (mightysites) Joomla installations

## The problem
Your workflow involves updating joomla plugins locally and testing before pushing to live/staging environments.  
You end up updating each extension locally and then repeat the work on each environment to ensure the database migrations and manifest is updated successfully.

If you use Mighty Sites to power a multisite setup, the problem is compounded.

## The solution
This CLI utility will perform the process automatically.  

* Add the update_plugins.php and MultiSiteUpdates folder to the cli directory on your existing installation
* Unzip each package to the cli/MultiSiteUpdates/Extensions folder, i.e cli/MultiSiteUpdates/Extensions/pkg_jce_pro_2711
* Run the following from your cli folder

```
path/to/your/php update_plugins.php
```

If you have a mighty sites setup, set $multisite_mode to true.  The utility will attempt to find all mighty sites configurations and peform the process on each of them.

## Disclaimer
Please do not attempt to use this tool unless you fully understand how it works and know how to rollback your site if something goes wrong.
I would recommend thorough testing in a development environment before attempting to run this on a live site.  You may need to modify it to meet the needs of your own workflow.  Again, if you don't understand how to do this then this tool is probably not for you.

I'm not interested in helping with broken joomla sites so please, please read the above again!

I can't guarantee that it will work with every extension.

I've personally used it to update the following across multiple sites:

* JCE Pro
* SH404SEF
* JSitemap Pro
* Mighty Sites



