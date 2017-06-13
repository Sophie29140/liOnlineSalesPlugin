To install this plugin, you have to :
1. download into your %SF_PLUGIN_DIR% directory (git submodule init && git submodule update)
2. download dependencies in the same way or check if they are present in the project
3. add its needed modules into the apps/*/config/extra-modules.php as it is proposed in the plugin config/ dir
4. build the model, filters and forms, and create the database
5. activate the modules in your targetted sf1 app as proposed in the config/extra-modules.php.template provided by the plugin
6. discover new submenus

Depedencies:
- sfDependencyInjectionPlugin
- liLibsApiPlugin

Adding an application:
- enable the osApplication module
- go to http[s]://[domain.tld]/[controler]/osApplication
- add a new Application with correct login/passwd
- try http[s]://[domain.tld]/[controler]/api/oauth/v2/token?client_id=[name]&client_secret=[passwd]&grant_type=password
