------------------------------------------------------
Respondr OpenCart
------------------------------------------------------
Author:				Respondr.io
Version:			1.0
Release Date:		2015-10-13
License:			GNU General Public License (GPL) version 3
------------------------------------------------------



DESCRIPTION
-----------
Implements Respondr Ecommerce tracking for OpenCart;
> Tracks regular page views
> Tracks Ecommerce product views (category views not yet implemented)
> Tracks Ecommerce cart add/update/delete
> Tracks Ecommerce orders
> Tracks Site Searches



IMPORTANT
---------
1. Requires VQmod installed (Highly recommended - https://github.com/vqmod/vqmod/ ).

2. The default install assumes that Respondr is installed to the '/respondr/' folder at the root of your OpenCart site.
If you have used a custom install path then please place the 'RespondrTracker.php' file from '/upload/respondr/RespondrTracker.php' to your custom Respondr folder.

3. The default install assumes that your OpenCart Admin directory is in the '/admin/' folder at the root of your OpenCart site.
If you have used a custom Admin path then please place all files from '/upload/admin/' to your custom OpenCart Admin folder.



INSTALL
-------
1) Upload the contents of the 'Upload' directory to the root of your OpenCart site.
2) Login to your OpenCart admin, go to the Extensions -> Modules page, and click 'Install' next to 'Respondr OpenCart Ecommerce mod'.
3) After install, click 'Edit' next to 'Respondr OpenCart Ecommerce mod', and on the settings page enter the details about your site and the Respondr installation;

a) "Respondr Tracking" - Global 'Enabled' / 'Disabled' setting for the Respondr OpenCart mod.
b) "Respondr Site ID" - This is the ID used in your Respondr install for the site you want to track, usually this is '1' but can vary if you have multiple sites or a custom setup. Consult the 'Website Management' page on your Respondr admin panel for this setting (under Settings -> Websites).



UPGRADE
-------
To upgrade from a previous version simply upload the files from the new version as described in step 1) of the installation.
There should be no need to re-install anything else or change/restore any settings as I've tried to pay attention to backwards compatibility during development.
Please get in touch if you experience any issues.



UNINSTALL
---------
In OpenCart admin, go to the Extensions -> Modules page, and simply click 'Uninstall' next to 'Respondr OpenCart Ecommerce mod'.
This will ensure the configuration settings are deleted and that none of the main functions of the mod will run.
Some files will still remain - however these should be perfectly safe and not affect anything but to fully remove please delete all files which you uploaded during the install.



VERSION HISTORY
---------------

v1.0 - 2015/10/13
First version released