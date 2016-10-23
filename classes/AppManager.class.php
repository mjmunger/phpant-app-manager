<?php
namespace PHPAnt\Core;

/**
 * App Name: +Core App Manager
 * Provides App management from the CLI to bootstrap your application.
 */

 /**
 * This App allows you to list, enable, and disable Apps from the CLI
 * in order to boostrap or debug your software.
 *
 * @package      PHPAnt
 * @subpackage   Apps
 * @category     Bootstrap manager
 * @author       Michael Munger <michael@highpoweredhelp.com>
 */ 


class AppManager extends \PHPAnt\Core\AntApp implements \PHPAnt\Core\AppInterface  {

    /**
     * Instantiates an instance of the PluginManager class.
     * Example:
     *
     * <code>
     * $appAppManager = new PluginManager();
     * </code>
     *
     * @return void
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function __construct() {
        $this->pluginName = 'App Manager';
        $this->canReload = false;
        $this->path = __DIR__;
    }

    function getActionList($Engine) {
        $actionList = array();

        foreach($Engine->apps as $app) {
            foreach($app->hooks as $hook) {
                if(!in_array($hook['hook'], $actionList)) {
                    $actionList[$hook['hook']] = NULL;
                }
            }
        }

        //Sort the list so we can display it nicely.
        ksort($actionList);        

        return $actionList;        
    }    

    /**
     * Callback for the cli-load-grammar action, which adds commands specific to this plugin to the CLI grammar.
     * Example:
     *
     * <code>
     * $appAppManager->addHook('cli-load-grammar','loadAppManager');
     * </code>
     *
     * @return array An array of CLI grammar that will be merged with the rest of the grammar. 
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function loadAppManager($args) {
        $AE = $args['AE'];
        
        $appList = array();

        foreach($AE->availableApps as $name => $path) {
            $appList[$name] = NULL;
        }

        $actionList = $this->getActionList($AE);

        /* Add "all" as an option for the plugin list. */

        $appList['all'] = NULL;

        $grammar['apps'] = [ 'disable' => NULL
                              , 'enable'  => NULL
                              , 'get'     => NULL
                              , 'list'    => [ 'available' => NULL
                                             , 'enabled'   => NULL
                                             ]
                              , 'new'     => NULL
                              , 'reload'  => NULL
                              , 'svn'     => [ 'add'    => $appList
                                             ,'check'   => NULL
                                             , 'commit' => $appList
                                             , 'update' => $appList
                                             ]
                              ];

        $grammar['libs']    = ['git' => NULL];

        $grammar['actions'] = ['show' => [ 'all'        => NULL 
                                         , 'priorities' => $actionList
                                         ]
                              ];
        
        $results['grammar'] = $grammar;
        $results['success'] = true;
        return $results;
    }
    
    /**
     * Callback function that prints to the CLI during cli-init to show this plugin has loaded.
     * Example:
     *
     * <code>
     * $appAppManager->addHook('cli-init','declareMySelf');
     * </code>
     *
     * @return array An associative array declaring the status / success of the operation.
     * @author Michael Munger <michael@highpoweredhelp.com>
     **/

    function declareMySelf() {
        if($this->verbosity > 4 && $this->loaded ){
            print("Default Grammar Plugin loaded.\n");
        }
        return array('success' => true);
    }

    function listapps(AppEngine $AE, $which = 'all') {
        $listingArray = NULL;

        print PHP_EOL;
        switch ($which) {
            case 'available':
                $listingArray = $AE->availableapps;
                print "Available apps" . PHP_EOL;
                break;
            
            case 'enabled':
                print "Enabled apps" . PHP_EOL;
                $listingArray = $AE->enabledapps;
                break;

            case 'all':
                print "All apps" . PHP_EOL;
                $listingArray = $AE->availableapps;
                break;

            default:
                print "List what? apps list [available | enabled]" . PHP_EOL . PHP_EOL;
                return ['success' => true];
                break;
        }

        print str_pad('', 80, '-') . PHP_EOL;
        print str_pad('Name', 30);
        print str_pad('Version', 8);
        print "Path" . PHP_EOL;
        print str_pad('', 80, '-') . PHP_EOL;

        foreach($listingArray as $plugin) {
            $name = $AE->getPluginName($plugin);

            $version = ($AE->getPluginVersion($plugin)?$AE->getPluginVersion($plugin):"?");
            $version = $AE->getPluginVersion($plugin);

            print str_pad($name,30);
            print str_pad($version,8);
            print $plugin;
            print PHP_EOL;
        }

        return ['success' => true];
    }

    function disablePlugin(AppEngine $AE) {
        $enabled   = $AE->enabledapps;

        $choices = array();
        $counter = 0;

        print "0. Cancel" . PHP_EOL;

        foreach($enabled as $plugin) {
            $counter++;
            printf('%s. %s' . PHP_EOL,$counter,$AE->getPluginName($plugin));
            $choices[$counter] = $plugin;
        }

        $selection = trim(fgets(STDIN));
        if($selection == 0) {
            return ['success' => true];
        }

        try {
            $plugin = $choices[$selection];
            $name = $AE->getPluginName($plugin);
            $AE->disablePlugin($name,$plugin);
            printf("%s has been enabled. Reload apps / restart CLI to activate" . PHP_EOL,$name);
        } catch (Exception $e) {
            print "Invalid choice or plugin could not be enabled." . PHP_EOL;
            return ['success' => false];
        }

        return ['success' => true];
    }

    function enablePlugin(AppEngine $AE) {
        $available = $AE->availableapps;
        $enabled   = $AE->enabledapps;

        $candidates = array_diff($available, $enabled);

        $choices = array();
        $counter = 0;

        print "0. Cancel" . PHP_EOL;

        foreach($candidates as $plugin) {
            $counter++;
            printf('%s. %s' . PHP_EOL,$counter,$AE->getPluginName($plugin));
            $choices[$counter] = $plugin;
        }

        $selection = trim(fgets(STDIN));

        if($selection == 0) {
            return ['success' => true];
        }

        try {
            $plugin = $choices[$selection];
            $name = $AE->getPluginName($plugin);
            $AE->enablePlugin($name,$plugin);
            printf("%s has been enabled. Reload apps / restart CLI to activate" . PHP_EOL,$name);
        } catch (Exception $e) {
            print "Invalid choice or plugin could not be enabled." . PHP_EOL;
            return ['success' => false];
        }

        return ['success' => true];
    }

    function checkSVNStatus($AE) {
        // Open a known directory, and proceed to read its contents
        foreach($AE->availableapps as $name => $path) {

            $pluginDir = dirname($path);
            chdir($pluginDir);

            $cmd = "svn status";
            $result = shell_exec($cmd);
            if(strlen($result) > 0) {
                echo $name               . PHP_EOL;
                echo $result             . PHP_EOL;
                echo str_pad('', 40,'=') . PHP_EOL;
            }
        }
    }

    function addSVNFiles($AE,$cmd) {
        // Open a known directory, and proceed to read its contents
        foreach($AE->availableapps as $name => $path) {

            $pluginDir = dirname($path);
            chdir($pluginDir);

            $cmd = "svn status | grep '^?'";
            $result = shell_exec($cmd);

            /* create the svn add command for each of these. */
            $buffer = split("\n", $result);

            $hasUpdate = false;
            $commands = array();

            foreach($buffer as $line) {
                $line = trim($line);
                $hasUpdate = true;
                $line = preg_replace('#^\? *#', 'svn add ', $line);
                array_push($commands, $line);
            }


            if($hasUpdate) {
                echo $name               . PHP_EOL;
                foreach($commands as $c) {
                    $result = shell_exec($c);
                    echo $result . PHP_EOL;
                }
            } else {
                echo $name . "OK";
            }
            echo str_pad('', 40,'=') . PHP_EOL;
        }        
    }

    function updatePlugin($AE,$cmd) {

        /* Remove the 'apps svn commit' from the command string */
        $repo = trim(str_replace('apps svn update', '', $cmd->raw_command));

        if(!$repo) {
            throw new Exception("You must specify which plugin to update or 'all'.", 1);
            
        }
        
        /* Our list of apps to commit */
        $commitList = [];

        switch ($repo) {
            case 'all':
                foreach($AE->availableapps as $name => $path) {
                    array_push($commitList, $name);
                }
                break;
            
            default:
                array_push($commitList, $pluginDir);
                break;
        }

        foreach($commitList as $repo) {
            $pluginDir = trim(dirname($AE->availableapps[$repo]));

            chdir($pluginDir);
    
            echo "Updating: " . $repo . PHP_EOL;
            
            $cmd = sprintf("svn update");
            $result = shell_exec($cmd);
    
            echo $result;
    
        }            

        return ['success' => true];

    }

    function commitPlugin($AE,$cmd) {

        /* Remove the 'apps svn commit' from the command string */
        $repo = trim(str_replace('apps svn commit', '', $cmd->raw_command));
        if(!$repo) {
            throw new Exception("You must specify the plugin to commit or 'all'.", 1);
            return false;
        }

        /* Our list of apps to commit */
        $commitList = [];

        switch ($repo) {
            case 'all':
                foreach($AE->availableapps as $name => $path) {
                    array_push($commitList, $name);
                }
                break;
            
            default:
                array_push($commitList, $pluginDir);
                break;
        }

        foreach($commitList as $repo) {
            $pluginDir = trim(dirname($AE->availableapps[$repo]));

            chdir($pluginDir);
    
            $commitSummary = tempnam('/tmp/', 'svn_');
            $fh=fopen($commitSummary, 'w');
            fwrite($fh,'Interim commit from CLI');
            fclose($fh);
            printf("Commiting path: " . $pluginDir . PHP_EOL);
    
            $cmd = sprintf("svn commit -F %s",$commitSummary);
            $result = shell_exec($cmd);
    
            echo $result;
    
            unlink($commitSummary);
        }            

        return ['success' => true];

    }

    private function UCArrayWord($word) {
        return ucwords($word);
    }

    function createNewPlugin(AppEngine $AE, $cmd) {
        $repo = $cmd->getLastToken();

        if($repo == 'new') {
            printf("You must supply a name for your plugin. Example: bfw-foo". PHP_EOL);
            return ['success' => false];
        }

        /*1. Create the repo on svn.highpoweredhelp.com */

        // create a new cURL resource
        printf("Registering new plugin repo with svn.highpoweredhelp.com...");
        $ch = curl_init();
        
        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, "http://svn.highpoweredhelp.com/create/?repo=".urlencode($repo));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        // grab URL and pass it to the browser
        $result = curl_exec($ch);
        
        // close cURL resource, and free up system resources
        curl_close($ch);

        if($result != "OK") {
            echo $result;
            return ['success' => true];
        } else {
            printf("Registration succesful!" . PHP_EOL);
        }

        /*2. Check it out to the apps directory. */

        printf("Checking out repo to " . $AE->Configs->getappsDir());

        $cmd = sprintf("svn co svn://svn.highpoweredhelp.com/%s %s/%s",$repo,$AE->Configs->getappsDir(),$repo);
        $result = exec($cmd);

        printf("Done".PHP_EOL);

        /*4. load the accompanying default plugin template, and change the names, etc...*/
        // Create a Friendly name from the repo name.
        $fnBuffer = explode('-', $repo);
        $fnBuffer = array_map(['PluginManager','UCArrayWord'], $fnBuffer);
        $friendlyName = implode(' ', $fnBuffer);

        $buffer = file_get_contents(__DIR__.'/resources/plugin-template.php');
        $buffer = str_replace('%FRIENDLYNAME%', $friendlyName, $buffer);
        $systemName = str_replace(' ', '', $friendlyName);
        $buffer = str_replace('%SYSTEMNAME%', $systemName, $buffer);

        //copy($AE->Configs->getappsDir().'bfw-plugin-default/plugin.php',$AE->Configs->getappsDir(). '/' . $repo . '/plugin.php' );
        $fp = fopen($AE->Configs->getappsDir(). '/' . $repo . '/plugin.php','w');
        fwrite($fp,$buffer);
        fclose($fp);

        printf("Default content for plugin template setup as this plugin's plugin.php file. Be sure to customize it!" . PHP_EOL);

        printf("Adding the plugin.php file to the repo with svn add...");

        chdir($AE->Configs->getappsDir(). '/'. $repo);
        $cmd = "svn add plugin.php";
        $result = exec($cmd);

        printf("Done." . PHP_EOL);

        /*5. Done. */

        printf("The repo has been created and checked out into your apps/ directory. The default plugin has been copied over your new plugin as a starting point. Be sure to customize it BEFORE activating it!" . PHP_EOL);
    }

    function getNewPlugin(AppEngine $AE, Command $cmd) {
        chdir($AE->Configs->getappsDir());
        if(strtolower($cmd->getLastToken()) == 'get') {
            printf("You must specify a plugin to get!" . PHP_EOL);
            return false;
        }
        $cmd = "svn co svn://svn.highpoweredhelp.com/" . $cmd->getLastToken();
        $result = exec($cmd);
        printf($result . PHP_EOL);
        printf("Done". PHP_EOL);
    }

    function gitLibraries($args) {
        $cmd = $args['command'];
        $AE  = $args['PE'];

        chdir($AE->Configs->getLibsDir());
        $cmd = "git clone " . $cmd->getLastToken();
        $result = exec($cmd);
        echo $result . PHP_EOL;
        printf("Done" . PHP_EOL);
    }

    function showPriorities($AE,$cmd) {
        $action = $cmd->getLastToken();

        $appsWithRequestedHook = $AE->getappsWithRequestedHook($action);
        print PHP_EOL;
        printf("For hook: %s, plugin firing order is:" . PHP_EOL, $action);
        print str_pad("Name", 50);
        print str_pad("Priority", 50);
        print PHP_EOL;

        foreach($appsWithRequestedHook as $plugin) {

            $hash = $AE->getHookKey($plugin,$action);

            print str_pad($plugin->pluginName, 50);
            print str_pad($plugin->hooks[$hash]['priority'], 50);
            print PHP_EOL;                
        }
    }

    function showAllActions($AE) {
        print "Actions that have been registered in this application:" . PHP_EOL;
        $actionList = $this->getActionList($AE);
        foreach($actionList as $action => $buffer) {
            print $action . PHP_EOL;
        }
    }

    function processCommand($args) {
        $cmd = $args['command'];
        $AE  = $args['PE'];

        /* deal with actions */

        if($cmd->startswith('actions show priorities')) {
            $this->showPriorities($AE,$cmd);
        }

        if($cmd->is('actions show all')) {
            $this->showAllActions($AE,$cmd);
        }        

        /* git for libraries */

        if($cmd->startswith('libs git')) {
            $this->gitLibraries($args);
        }

        /* list apps */ 
        if($cmd->startswith('apps list')) {
            $which = $cmd->getLastToken();
            $this->listapps($AE,$which);
        }

        if($cmd->startswith('apps enable')) {
            $this->enablePlugin($AE);
        }

        if($cmd->startswith('apps disable')) {
            $this->disablePlugin($AE);
        }

        if($cmd->startswith('apps new')) {
            $this->createNewPlugin($AE,$cmd);
        }

        if($cmd->startswith('apps get')) {
            $this->getNewPlugin($AE,$cmd);
        }

        if($cmd->startswith('apps svn')) {
            $choice = $cmd->getToken(2);
            switch ($choice) {
                case 'check':
                    $this->checkSVNStatus($AE);
                    break;
                case 'commit':
                    try {
                        $this->commitPlugin($AE,$cmd);
                    } catch (Exception $e) {
                        $AE->Configs->divAlert($e->getMessage(),'danger');
                    }
                    break;
                case 'update':
                    try {
                        $this->updatePlugin($AE,$cmd);
                    } catch (Exception $e) {
                        $AE->Configs->divAlert($e->getMessage(),'danger');
                    }
                    break;
                case 'add':
                    $this->addSVNFiles($AE,$cmd);
                    break;
                default:
                    # code...
                    break;
            }

            return ['success' => true];

        }

        return ['success' => true];
    }
}