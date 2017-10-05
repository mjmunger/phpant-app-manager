<?php
namespace PHPAnt\Core;

use \Exception;

/**
 * App Name: +Core App Manager
 * App Description: Provides App management from the CLI to bootstrap your application.
 * App Version: 1.0
 * App Action: cli-load-grammar -> loadAppManager       @ 90
 * App Action: cli-init         -> declareMySelf        @ 50
 * App Action: cli-command      -> processCommand       @ 50
 * App Action: load_loaders     -> AppManagerAutoLoader @ 50
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
        $this->appName = 'App Manager';
        $this->canReload = false;
        $this->path = __DIR__;
    }

    //Uncomment this function and the following function to enable the autoloader for this plugin.
    function AppManagerAutoLoader() {
        //REGISTER THE AUTOLOADER! This has to be done first thing! 
        spl_autoload_register(array($this,'loadAppManagerClasses'));
        return ['success' => true];

    }

    public function loadAppManagerClasses($class) {

        //Deal with namespaces
        if(stripos($class, '\\')) {
            $buffer = explode('\\', $class);
            $class = end($buffer);
        }
        
        $baseDir = $this->path;

        $candidate_files = array();

        //Try to grab it from the classes directory.
        $candidate_path = sprintf($baseDir.'/classes/%s.class.php',$class);
        array_push($candidate_files, $candidate_path);

        //Loop through all candidate files, and attempt to load them all in the correct order (FIFO)
        foreach($candidate_files as $dependency) {
            if($this->verbosity > 14) printf("Looking to load %s",$dependency) . PHP_EOL;

            if(file_exists($dependency)) {
                if(is_readable($dependency)) {

                    //Print debug info if verbosity is greater than 9
                    if($this->verbosity > 9) print "Including: " . $dependency . PHP_EOL;

                    //Include the file!
                    include($dependency);
                }
            }
        }
        return ['success' => true];
    }

    function getActionList($Engine) {
        $actionList = array();

        foreach($Engine->apps as $app) {
            foreach($app->hooks as $hook) {
                if(!in_array($hook['hook'], $actionList)) $actionList[$hook['hook']] = NULL;
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

        $grammar['apps'] = [ 'blacklist' => [ 'clear'   => NULL
                                            , 'disable' => NULL
                                            , 'enable'  => NULL
                                            , 'show'    => NULL
                                            , 'unban'   => NULL
                                            ]
                           , 'codepath'  => [ 'analyze' => NULL ]
                           , 'disable'   => $appList
                           , 'enable'    => $appList
                           , 'git'       => [ 'autocommit' => $appList
                                            , 'diff'       => NULL
                                            , 'dump'       => NULL
                                            , 'export'     => [ 'snapshot' => [ 'relaxed' => NULL] ] 
                                            , 'import'     => [ 'snapshot' => NULL ] 
                                            , 'status'     => $appList 
                                            ]
                           , 'key'       => [ 'remove' => NULL
                                            , 'set'    => NULL
                                            , 'show'   => NULL
                                            ]
                           , 'list'      => [ 'available' => NULL
                                            , 'enabled'   => NULL
                                            ]
                           , 'manifest'  => ['generate' => $appList]
                           , 'new'       => NULL
                           , 'publish'   => $appList
                           , 'reload'    => NULL
                           , 'sign'      => NULL
                           , 'snapshot'  => [ 'restore' => NULL
                                            , 'save'    => NULL
                                            ]
                            ,'verify'    => $appList
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
                $listingArray = array_diff($AE->availableApps,$AE->enabledApps);
                print "Available apps" . PHP_EOL;
                break;
            
            case 'enabled':
                print "Enabled apps" . PHP_EOL;
                $listingArray = $AE->enabledApps;
                break;

            case 'all':
                print "All apps" . PHP_EOL;
                $listingArray = $AE->availableApps;
                break;

            default:
                print "List what? apps list [available | enabled]" . PHP_EOL . PHP_EOL;
                return ['success' => true];
                break;
        }

        $TL = new TableLog();
        $TL->setHeader(['App','Path']);

        foreach($listingArray as $app => $path) {
            $TL->addRow([$app,$path]);
        }

        $TL->showTable();

        return ['success' => true];
    }

    function disableApp($args) {
        $AE      = $args['AE'];
        $command = $args['command'];
        $appName = $command->leftStrip('apps disable',true);
        if(!array_key_exists($appName, $AE->availableApps)) {
            echo "$appName could not be found in the list of available apps." . PHP_EOL;
            return ['success' => false];
        }

        $result = $AE->disableApp($appName,$AE->availableApps[$appName]);

        echo ($result?"$appName successfully disabled.":"$appName could not be enabled.");
        echo PHP_EOL;
        return ['success' => $result];
    }

    function enableAllApps($args) {
        $Engine = $args['AE'];

        foreach($Engine->availableApps as $name => $path) {
            printf("Enabling: %s" . PHP_EOL, $name);

            $result = $Engine->enableApp($name,$path);
        }

        return $result;
    }

    function enableApp($args) {
        $AE      = $args['AE'];
        $command = $args['command'];
        $appName = $command->leftStrip('apps enable',true);

        if($appName == 'all') return $this->enableAllApps($args);

        if(!array_key_exists($appName, $AE->availableApps)) {
            echo "$appName could not be found in the list of available apps." . PHP_EOL;
            return ['success' => false];
        }

        $result = $AE->enableApp($appName,$AE->availableApps[$appName]);

        return $result;
    }

    private function UCArrayWord($word) {
        return ucwords($word);
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

            print str_pad($plugin->appName, 50);
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

    function generateAppManifest($args) {
        $cmd = $args['command'];
        $AE  = $args['AE'];

        $appName = $cmd->leftStrip('apps manifest generate',true);

        //Get the app dir, because that's what PHPAntSigner needs.
        $path = dirname($AE->availableApps[$appName]);
        $buffer = explode('/', $path);
        $appDir = end($buffer);
        $options['AE'] = $AE;

        $Signer  = new PHPAntSigner($options);
        $Signer->setApp($appDir);
        $manifestPath = $Signer->generateManifestFile();

        return ['success' => file_exists($manifestPath)];
    }

    function determineAppNameOnDisk(AppEngine $AE, $requestedApp, $mode) {
        switch ($mode) {
            case 'byName':
                if(!isset($AE->availableApps[$requestedApp])) {
                    print "The requested app ($requestedApp) is not available. Remember: app names are CaSe SeNsiTivE" . PHP_EOL;
                    return ['success' => false];
                }
                $buffer = explode('/',dirname($AE->availableApps[$requestedApp]));
                $appFolder = end($buffer);
                return $appFolder;
                break;
            case 'byPath':
                $buffer = explode('/',dirname($requestedApp));
                $appFolder = end($buffer);
                return $appFolder;
                break;            
            default:
                // code...
                break;
        }
    }

    function verifySingleApp(AppEngine $AE,$requestedApp,$mode) {

        $appFolder = $this->determineAppNameOnDisk($AE, $requestedApp, $mode);

        $options['AE'] = $AE;
        $Signer = new \PHPAnt\Core\PHPAntSigner($options);
        try {
            $Signer->setApp($appFolder);
        } catch (Exception $e) {
            print "Seems you just tried to set an app that doesn't exist?" . PHP_EOL;
        }
        return $Signer->verifyApp();
    }

    function createNewApp($AE,$cmd) {

        $privateKey = $AE->Configs->getConfigs(['signing-key']);
        if(!$privateKey) {

            print "You do not have a signing key, and therefore cannot create" . PHP_EOL;
            print "a new app. Either generate a new key with `apps key generate`, or" . PHP_EOL;
            print "use settings set signing-key [/path/to/private.key] to setup your" . PHP_EOL;
            print "key." . PHP_EOL;
            print PHP_EOL;

            return ['success' => false];
        }

        //Copy the template app to this directory.
        $templatePath = __DIR__ . '/resources/app-template.php';
        $template     = file_get_contents($templatePath);

        $autoloaderTemplatePath = __DIR__ . '/resources/auto-loader-template.php';
        $autoloaderTemplate     = file_get_contents($autoloaderTemplatePath);

        //We are only supporting git for now.
        printf("Enter the git URL for this project:\n");
        
        //this really needs to be sanitized, but if you're an admin and you
        //want to inject malcious code here, go for it. You're only destroying
        //your own system. We are not going to try to protect you from
        //yourself.

        $gitURL = trim(fgets(STDIN));

        //If we use an autoloader, that will be included later.
        printf("Will this project use an autoloader? [Y/n]\n");
        $choice = trim(fgets(STDIN));

        $autoloader = ($choice == "" || $choice = "Y");
        printf("Use autoloader: %s\n",($autoloader?"Yes":"No"));

        //Parse the project name from the URL
        $buffer = explode('/', $gitURL);
        $buffer = end($buffer);
        $buffer = explode('.', $buffer);

        $gitProjectName = $buffer[0];

        printf("Creating app directory: %s\n",$gitProjectName);

        //change to the apps directory.
        chdir($AE->Configs->getAppsDir());
        $command = sprintf('git clone %s',$gitURL);
        passthru($command);

        $appDir = $AE->Configs->getAppsDir() . $gitProjectName;

        chdir($appDir);

        //Add in the autoloader FIRST before we do a find / replace on the other template fields so they will be included!

        $autoloaderTemplate = ($autoloader ? $autoloaderTemplate: NULL);
        $template = str_replace('%AUTOLOADER%',$autoloaderTemplate, $template);

        //Replace all the placeholders with the info we need.

        $questions = [ "What's the project namespace?"                                              => '%PROJECT%'
                     , "What's the subspace for this app?"                                          => '%SUBSPACE%'
                     , "What's the category of this app?"                                           => '%CATEGORY%'
                     , "What's the app's name? (Friendly name. Spaces allowed.)"                    => '%FRIENDLYNAME%'
                     , "What's this app's system name? (Usually, the friendly name without spaces)" => '%SYSTEMNAME%'
                     , "Please give a short description of this app."                               => '%APPDESCRIPTION%'
                     , "What's your name? (For authorship details)"                                 => '%AUTHORNAME%'
                     , "What's your email address? (For authorship details)"                        => '%AUTHOREMAIL%'
                     ];

        foreach($questions as $q => $field) {
            printf("%s\n",$q);
            $answer   = trim(fgets(STDIN));
            $template = str_replace($field, $answer, $template);

            //Save these to setup git later.
            if($field == '%AUTHORNAME%')  $authorname  = $answer;
            if($field == '%AUTHOREMAIL%') $authoreamil = $answer;
        }

        $fh = fopen($appDir . '/app.php','w');
        fwrite($fh,$template);
        fclose($fh);

        $request = ['signing-key'];
        $settings = $AE->Configs->getConfigs($request);

        //Next, let's publish the app.
        $options['AE']             = $AE;
        $options['privateKeyPath'] = $settings['signing-key'];
        $options['appName']        = $gitProjectName;

        $Signer  = new PHPAntSigner($options);
        $results = $Signer->publish($options);

        $TL = new TableLog();
        $TL->setHeader(["Task","Result"]);
        foreach($results as $key => $value) {
            switch($key) {
                case 'appActions':
                    $key = 'Action List for this App (JSON)';
                    break;

                case 'cleanAppCredentials':
                    $key = 'App Credentials Cleaned';
                    break;

                case 'generateManifestFile':
                    $key = 'Generate Manifest File';
                    break;

                case 'saveDerivePublicKey':
                    $key = 'Derived Public Key';
                    break;

                case 'setApp':
                    $key = 'Setting Application for Signing';
                    break;

                case 'signApp':
                    $key = 'Signing App';
                    break;

                case 'verifyApp':
                    $key = 'Verifying Signature';
                    $value = $value['integrityOK'];
                    break;

            }

            if(is_bool($value)) $value = ($value?"OK":"FAILED");

            $TL->addRow([$key,$value]);
        }

        $TL->showTable();        

        //Now, let's tell git about it.
        chdir($appDir);
        mkdir('tests');

        //Create the readme so that this will get included in the git folder.
        $fh = fopen($appDir . '/tests/readme.md','w');
        fwrite($fh,'Unit tests go in this directory');
        fclose($fh);

        if($autoloader) {
            mkdir('classes');
            $fh = fopen($appDir . '/classes/readme.md','w');
            fwrite($fh,'Autoloaded classes go in this directory');
            fclose($fh);            
        }

        $commands = [ sprintf('git config user.email %s',$authoreamil)
                    , sprintf('git config user.name "%s"',$authorname)
                    , 'git add *'
                    , 'git commit -m "Initial commit. Added signed base app.php"'
                    , 'git push'
                    ];

        foreach($commands as $command) {
            passthru($command);
        }
        
        echo "App skeleton created, committed to git, and pushed to the repo. Remember to";
        echo PHP_EOL;
        echo "regenerate your manifest file when you add new hooks, and to re-sign your app";
        echo PHP_EOL;
        echo "once you've got it ready for distribution.";
        echo PHP_EOL;
    }

    function showGitStatus($args) {
        $AE  = $args['AE'];
        $counter = 0;
        $TL = new TableLog();
        $TL->setHeader(['#','Name','Path', 'Status', 'Hash']);
        $counter = 0;
        foreach($AE->availableApps as $name => $path) {
            $counter++;
            $appDir = dirname($path);
            chdir($appDir);

            $GitParser = new GitParser();

            $cmd = "git status";
            $status = shell_exec($cmd);
            $state = $GitParser->analyzeStatus($status);

            $cmd = 'git rev-parse --short HEAD';
            $hash = trim(shell_exec($cmd));

            $TL->addRow([$counter,$name,$path,$state,$hash]);
        }

        $TL->showTable();
    }

    function showAppGitStatus($args,$appName) {
        $AE  = $args['AE'];
        foreach($AE->availableApps as $name => $path) {
            if($name == $appName) {
                $appDir = dirname($path);
                $GitParser         = new GitParser();
                $GitParser->appDir = $appDir;
                echo $GitParser->getGitStatus();
            }
        }
    }

    function autocommit($args,$appName) {
        $AE  = $args['AE'];
        foreach($AE->availableApps as $name => $path) {
            if($name == $appName) {
                $path = dirname($path);
                chdir($path);
                
                echo "Performing autocommit for $name ($path)..." . PHP_EOL;

                $cmd = 'git commit -a -m"Autocommit via PHP-Ant App Manager"';
                $result = shell_exec($cmd);
                echo $result;

                //Try to push it if the word "pull" does not appear in the result.
                if(stripos($result, "pull")) return false;

                echo "Attempting to git push..." . PHP_EOL;
                $cmd = 'git push';
                $result = shell_exec($cmd);
                echo $result;
            }
        }
    }

    function perfectExportStatus($AE) {
        $OK = true;

        foreach($AE->availableApps as $name => $path) {
            $counter++;
            $appDir = dirname($path);
            chdir($appDir);

            $GitParser = new GitParser();

            $cmd = "git status";
            $status = shell_exec($cmd);
            $state = $GitParser->analyzeStatus($status);

            if($state != 'up-to-date') {
                $OK = false;
                break;
            }
        }            
        return $OK;
    }

    function findAppByRemote($args,$remote) {
        $buffer   = explode('/', $remote);
        $repoName = end($buffer);
        $buffer   = explode('.', $repoName);
        $appDirName = $buffer[0];

        $AE = $args['AE'];

        $appPath = $AE->Configs->getAppsDir() . $appDirName;

        if(!file_exists($appPath)) return false;

        //Don't delete myself!
        if($appPath == __DIR__ ) return false;

        $GitParser = new GitParser();
        $GitParser->appDir = $appPath;
        $GitParser->getGitHash();
        $GitParser->parseOrigin();
        $GitParser->getGitStatus();

        if($GitParser->remotes == $remote) {
            print "Found: $remote. Proceding with update!" . PHP_EOL;
            return dirname($path);
        } else {
            printf("Remote does not agree with snapshot. (%s != %s) Removing this app so it can be re-cloned." . PHP_EOL,$GitParser->remotes, $remote);
            //remove this directory so we can re-clone it.
            print "Remote does not match directory. Removing this app so it can be recloned: $appPath" . PHP_EOL;
            $this->rrmdir($appPath);
        }

        //did not find the app!
        print "Could not find $remote" . PHP_EOL;
        return false;
    }

    function rrmdir($dir) { 
        print "Recursively deleting: $dir" . PHP_EOL;
       if (is_dir($dir)) { 
         $objects = scandir($dir); 
         foreach ($objects as $object) { 
           if ($object != "." && $object != "..") { 
             if (is_dir($dir."/".$object))
               $this->rrmdir($dir."/".$object);
             else
               unlink($dir."/".$object); 
           } 
         }
         rmdir($dir); 
       } 
     }

    function importGitSnapshot($args,$cmd) {

        $snapshotPath = __DIR__ . '/git-snapshot.json';

        if(!file_exists($snapshotPath)) {
            print "File does not exist: $snapshotPath." . PHP_EOL;
            print "Create this file by running:"  . PHP_EOL;
            print PHP_EOL;
            print PHP_EOL;
            print "  git apps export snapshot"  . PHP_EOL;
            print PHP_EOL;
            print PHP_EOL;
            print "...and then copy it to the PHP-Ant App Manager app directory."  . PHP_EOL;
            return false;
        }

        $buffer = file_get_contents($snapshotPath);
        $json = json_decode($buffer);

        foreach($json as $node) {

            $fetch = $node->remotes;
            //Never update the app manager this way.
            if(strstr($fetch, 'phpant-app-manager.git') !== FALSE) {
                print "You cannot update the app manager this way. Skipping." . PHP_EOL;
                continue;
            }

            print "Updating $fetch..." . PHP_EOL;

            $appPath = $this->findAppByRemote($args,$fetch);


            if($appPath === false) {
                //We need to checkout this app.
                chdir($args['AE']->Configs->getAppsDir());
                $cmd = "git clone $fetch";
                $result = shell_exec($cmd);
                // echo $result . PHP_EOL;
                //Try again.
                $appPath = $this->findAppByRemote($args,$fetch);
            }

            if($appPath === false) {
                //Tried twice. Something went wrong.
                print "Could not checkout $fetch to revision $node->hash. Something went wrong. Please correcct any errors and try again." . PHP_EOL;
                return false;
            }

            print "AppPath = $appPath" . PHP_EOL;

            chdir($appPath);

            print "Current directory: " . getcwd() . PHP_EOL;

            print "Cleaning..." . PHP_EOL;
            $cmd = "git clean -f";
            $result = shell_exec($cmd);
            // echo $result . PHP_EOL;

            print "Resetting..." . PHP_EOL;
            $cmd = "git reset --hard HEAD";
            $result = shell_exec($cmd);
            // echo $result . PHP_EOL;

            print "Checking out master..." . PHP_EOL;
            $cmd = 'git checkout master';
            $result = shell_exec($cmd);

            print "Pulling for master..." . PHP_EOL;
            $cmd = 'git pull';
            $result = shell_exec($cmd);
            // echo $result . PHP_EOL;
            
/*            $Directory = new \RecursiveDirectoryIterator($appPath,\FilesystemIterator::SKIP_DOTS);
            $Iterator = new \RecursiveIteratorIterator($Directory);

            foreach($Iterator as $file) {
                //Don't look at anything in the .git directory.
                if(stripos($file->getPathname(),'.git') !== FALSE ) continue;

                //echo "Checking: $file" . PHP_EOL;

                $cmd = "git checkout $file";
                //$result = shell_exec($cmd);
                //echo $result . PHP_EOL;
            }*/
            
            
            print "Checking out: " . $node->hash . PHP_EOL;
            $cmd = "git checkout $node->hash";
            $result = shell_exec($cmd);
            //echo $result . PHP_EOL;
        }
    }

    function exportGitSnapshot($args,$cmd) {
        $AE = $args['AE'];

        $strict = ($cmd->getLastToken() == 'relaxed' ? false : true);

        if($strict && !$this->perfectExportStatus($AE)) {
            print "All your apps must be in the 'up-to-date' status before you can created a snapshot export. This means all changes are either stashed or committed, and they have been pushed upstream so the resulting hash is available on other systems via git pull. Run 'apps git status' to see the current status of all yours apps. If you tried to do this on purpose, and do not want a full export, you can use the command 'git apps export snapshot relaxed' to export ONLY the apps that are 'up-to-date'";
            return true;
        }

        $export = [];

        foreach($AE->availableApps as $name => $path) {
            $appDir = dirname($path);
            $GitParser = new GitParser();
            $GitParser->appDir = $appDir;
            $GitParser->getGitHash();
            $GitParser->parseOrigin();
            $GitParser->getGitStatus();

            if($GitParser->analyzeStatus($GitParser->fullStatus) != 'up-to-date') continue;

            $node = [];
            $node['remotes'] = $GitParser->remotes;
            $node['hash'] = $GitParser->hash;

            array_push($export, $node);
        }

        $jsonPath = __DIR__ . '/git-snapshot.json';
        $fh = fopen($jsonPath,'w');
        fwrite($fh,json_encode($export));
        fclose($fh);

        print PHP_EOL;
        print "Apps snapshot written to:";
        print PHP_EOL;
        print PHP_EOL;
        print "  $jsonPath.";
        print PHP_EOL;
        print PHP_EOL;
        print "You can use this to import apps to another installation and ensure they are " . PHP_EOL;
        print "checked out to the exact same versions you have here on this server." . PHP_EOL;
        print PHP_EOL;
        print "Simply copy this file to the same location on the target server, then run this command:" . PHP_EOL;
        print PHP_EOL;
        print "  apps git import snapshot";
        print PHP_EOL;
        print PHP_EOL;

    }

    function getGitDump($args) {
        $AE = $args['AE'];

        $buffer =  [];

        foreach($AE->availableApps as $name => $path) {
            $appDir = dirname($path);
            $GitParser = new GitParser();
            $GitParser->appDir = $appDir;
            $GitParser->getGitHash();
            $GitParser->parseOrigin();
            $GitParser->getGitStatus();

            $node = [];
            $node['name'] = $name;
            $node['path'] = $path;
            $node['hash'] = $GitParser->hash;
            $node['remote'] = $GitParser->remotes;

            $buffer[$path] = $node;
        }

        return json_encode($buffer);
    }

    function packageApp($remoteApp) {
        $buffer = [];
        $buffer['app']    = $remoteApp->path;
        $buffer['remote'] = $remoteApp->remote;
        $buffer['hash']   = $remoteApp->hash;
        return $buffer;
    }

    function getGitDiff($args, $remoteJsonDump) {

        //Organize the two dump files.
        $remoteDump = json_decode($remoteJsonDump);
        $myDump = json_decode($this->getGitDump($args));

        $results = [];
        $results['missing'] = []; //Holds apps the remote has installed, that we do not.
        $results['hash']    = []; //Holds apps where they exist in both places, but the hash is different.
        $results['extra']   = []; //Holds apps we have that the remote does not.
        //Loop through the remote dump
        foreach($remoteDump as $remoteApp) {

            //Check to see if that app exists locally
            $path = $remoteApp->path;
            $exists = isset($myDump->$path);
            $buffer = $this->packageApp($remoteApp);

            if($exists) {
                $localApp = $myDump->$path;
                //Check the hash
                $hashSame = (strcmp($localApp->hash, $remoteApp->hash) == 0);

                if($hashSame) continue; //Exists in both places, and hash is the same. We're no longer interested in it.


                //Exists in both places, but there is a hash mismatch. Add to the hash element.
                array_push($results['hash'], $buffer);
                continue;
            }

            //Doesn't exist, so add to missing
            array_push($results['missing'], $buffer);
        }

        //figure out what we have that they don't.

        foreach($myDump as $localApp) {

            $path = $localApp->path;
            $exists = isset($remoteDump->$path);
            $buffer = $this->packageApp($localApp);

            // printf("[ %s ] $path" . PHP_EOL,($exists ? "OK" : "EXTRA"), $path);

            if($exists) continue;

            array_push($results['extra'], $buffer);
        }

        $return = json_encode($results);
        return $return;
    }

    function generatenewKeys($args) {
        $Signer = new \PHPAnt\Core\PHPAntSigner($args);
        $Signer->genKeys(true);
    }

    function printDiffTable($appListObject, $title) {
        echo str_repeat("=", 20) . PHP_EOL;
        print strtoupper($title) . PHP_EOL;
        echo str_repeat("=", 20) . PHP_EOL;

        $TL = new TableLog();
        $TL->setHeader(['App','Remote Hash','Repo']);

        foreach($appListObject as $App) {
            $TL->addRow([$App->app, $App->hash, $App->remote]);
        }

        return $TL->makeTable();
    }

    function resetApps($Engine) {

        $Engine->Configs->setConfig('enabledAppsList',[]);
        $Engine->loadApps();

        print "Apps have been reset. Re-enable apps now, and reload as necessary." . PHP_EOL;
    }

    function processCommand($args) {
        $cmd = $args['command'];
        $AE  = $args['AE'];

        /* deal with actions */

        if($cmd->startswith('apps git import snapshot')) {

            if(!($cmd->getLastToken() == 'delete-stuff')) {

                print PHP_EOL;
                print "This is a DESTRUCTIVE action. It will" . PHP_EOL;
                print "remove unversioned files from the target directory and revert" . PHP_EOL;
                print "any changes found to existing, versioned files. Before you can" . PHP_EOL;
                print "execute this, you must confirm that you are willing to lose" . PHP_EOL;
                print "files that are not in the remote snapshot by appending" . PHP_EOL;
                print "'delete-stuff' to the end of the command, like so:" . PHP_EOL;
                print PHP_EOL;
                print PHP_EOL;
                print "apps git import snapshot delete-stuff" . PHP_EOL;
                print PHP_EOL;
                print "You may consider 'git stash' as a way to save those changes or" . PHP_EOL;
                print "make a backup first, which is the better idea." . PHP_EOL;
                print PHP_EOL;
                print PHP_EOL;
            } else {
                $this->importGitSnapshot($args,$cmd);
            }
        }

        if($cmd->startswith('apps git dump')) {
            $outfile = $cmd->getLastToken();
            $outpath = dirname($outfile);

            if(!file_exists($outpath)) mkdir($outpath, 0777, true);

            $json = $this->getGitDump($args);

            $fh = fopen($outfile,'w');
            fwrite($fh,$json);
            fclose($fh);

            echo "Git status dumpped to: $outfile" . PHP_EOL;
            echo "";

        }

        if($cmd->startswith('apps git diff')) {
            $infile = $cmd->getLastToken();
            if(!file_exists($infile)) {
                echo "Cannot find file: $infile" . PHP_EOL;
                return ['success' => false];
            }

            $remoteJsonDump = trim(file_get_contents($infile));
            $resultObject = json_decode($this->getGitDiff($args, $remoteJsonDump));

            if(count($resultObject->missing) > 0 ) echo $this->printDiffTable($resultObject->missing , 'missing' );
            if(count($resultObject->hash)    > 0 ) echo $this->printDiffTable($resultObject->hash    , 'hash'    );
            if(count($resultObject->extra)   > 0 ) echo $this->printDiffTable($resultObject->extra   , 'extra'   );

            if(count($resultObject->missing) + count($resultObject->hash) + count($resultObject->extra) == 0) echo "Good news! Remote system is identical to this system." . PHP_EOL;

        }

        if($cmd->startswith('apps git export snapshot')) {
            $this->exportGitSnapshot($args,$cmd);
        }

        if($cmd->startswith('apps git autocommit')) {
            $this->autocommit($args,$cmd->leftStrip('apps git autocommit', true));
        }

        if($cmd->startswith('apps git status')) {
            if($cmd->is('apps git status')) {
                $this->showGitStatus($args);
            } else {
                $this->showAppGitStatus($args,$cmd->leftStrip('apps git status',true));
            }
        }

        if($cmd->is('apps new')) $this->createNewApp($AE,$cmd);

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
        
        if($cmd->startsWith('apps codepath analyze')) {
            $AE->showRoutedCodePath($cmd->leftStrip('apps codepath analyze'));
        }

        if($cmd->startswith('apps blacklist')) {
            switch ($cmd->getToken(2)) {

                case 'disable':
                    $AE->AppBlacklist->disabled = true;
                    $AE->Configs->setConfig('BlacklistDisabled',"1");
                    $success = true;
                    $message = "Blacklist disabled." . PHP_EOL;
                    break;
                case 'enable':
                    $AE->AppBlacklist->disabled = false;
                    $AE->Configs->setConfig('BlacklistDisabled',"0");
                    $success = true;
                    $message = "Blacklist enabled." . PHP_EOL;
                    break;                    
                case 'clear':
                    $AE->AppBlacklist->clear();
                    $message = "Blacklist cleared." . PHP_EOL;
                    $success = true;
                    break;

                case 'show':
                    $message = '';
                    $counter = 0;
                    $TL = new TableLog();
                    $TL->setHeader(['#','Name','Path']);
                    foreach($AE->AppBlacklist->blacklist as $path) {
                        $TL->addRow([$counter,$AE->getAppMeta($path,'name'),$path]);
                    }
                    $message = $TL->makeTable();
                    $success = true;
                    break;

                case 'unban':
                    $item = $cmd->getLastToken();

                    if($item >= count($item)) {
                        $message = "Item not found. Use apps blacklist show to see the currently blacklisted apps," . PHP_EOL . "then use the number listed next to the app you want to unban." . PHP_EOL;
                        $success = false;
                        break;
                    }

                    $appName = $AE->getAppMeta($AE->AppBlacklist->blacklist[$item],'name');
                    $AE->AppBlacklist->unban($item);
                    $message = "%s removed from blacklist. If this app is still malfunctioning, it will be re-added nearly instantly." . PHP_EOL;
                    $message = sprintf($message,$appName);
                    $success = true;
                    break;

                default:
                    // code...
                    break;
            }

            printf($message);
            $return = [];
            $return['success'] = $success;
            $return['message'] = $message;
            return $return;
        }

        if($cmd->startswith('apps key')) {
            switch ($cmd->getToken(2)) {
                case 'generate':
                    $this->generateNewKeys($args);
                    break;
                case 'remove':
                    $result = $args['AE']->Configs->setConfig('signing-key','');
                    print "Signing key removed." . PHP_EOL;
                    break;
                case 'set':
                    $path = $cmd->getLastToken();
                    if(!file_exists($path)) {
                        print "The file $path does not exist. Key not set!" . PHP_EOL;
                        return ['success' => false];
                    }
                    
                    $result = $args['AE']->Configs->setConfig('signing-key',$path);
                    $keyPath = $args['AE']->Configs->getConfigs(['signing-key']);
                    print "Signing key set to: " . $keyPath['signing-key'] . PHP_EOL;
                    return ['success' => $result];
                    break;
                case 'show':
                    $keyPath = $args['AE']->Configs->getConfigs(['signing-key']);
                    print "Signing key set to: " . $keyPath['signing-key'] . PHP_EOL;
                    break;
                default:
                    print           "Command not understood.";
                    print PHP_EOL . "Try:";
                    print PHP_EOL . "apps key set /path/to/private.key";
                    print PHP_EOL . "or";
                    print PHP_EOL . "apps key remove";
                    print PHP_EOL;
                    break;
            }
        }

        /* Save enabled apps so that we know what the known-good state of the application is */

        if($cmd->is('apps snapshot save')) {
            $buffer = $AE->Configs->getConfigs(['enabledAppsList']);
            if($AE->Configs->setConfig('enabledAppsListSnapshot',$buffer['enabledAppsList'])) {
                print "Enabled apps list snapshot saved." . PHP_EOL;
            } else {
                print "I could not snapshot the enabled apps list. " . PHP_EOL;
            }
            return ['success' => true];
        }

        if($cmd->is('apps snapshot restore')) {
            $buffer = $AE->Configs->getConfigs(['enabledAppsListSnapshot']);
            if($AE->Configs->setConfig('enabledAppsList',$buffer['enabledAppsListSnapshot'])) {
                $AE->getenabledApps();
                print "Enabled apps list snapshot restored. Reload apps to activate." . PHP_EOL;
            } else {
                print "I could not restore the enabled apps list. " . PHP_EOL;
            }
            return ['success' => true];
        }

        if($cmd->startswith('apps reset')) {
            $this->resetApps($AE);
        }

        /* list apps */ 
        if($cmd->startswith('apps list')) {
            $which = $cmd->getLastToken();
            $this->listapps($AE,$which);
        }

        if($cmd->startswith('apps enable')) {
            $result = $this->enableApp($args);
            if(isset($result['message'])) {
                echo $result['message'] . PHP_EOL;
                $args['AE']->log("AppEngine",$result['message']);
            }

        }

        if($cmd->startswith('apps disable')) {
            $this->disableApp($args);
        }

        if($cmd->is('apps reload')) {
            $AE->reload();
            echo "Reload complete." . PHP_EOL;
            return ['success' => true,'cli-command' => 'reload-grammar'];
        }

        if($cmd->startswith('apps manifest generate')) {
            $return = $this->generateAppManifest($args);
            return $return;
        }

        if($cmd->startswith('apps verify')) {
            $requestedApp = $cmd->leftStrip('apps verify', true);

            if($requestedApp == 'all') {
                $appStatus = [];
                foreach($AE->availableApps as $app) {
                    $results = $this->verifySingleApp($AE,$app,'byPath');
                    $appStatus[$app] = ($results['integrityOK']?"OK":"FAILED");
                }
                $TL = New TableLog();
                $TL->setHeader(['App','Integrity Status']);
                foreach($appStatus as $app => $status) {
                    $TL->addRow([$app,$status]);
                }
                $TL->showTable();

            } else {
                $result = $this->verifySingleApp($AE,$requestedApp, 'byName');
                
                if($result['integrityOK']) {
                    print "App integrity OK." . PHP_EOL;
                } else {
                    print "APP INTEGRITY CANNOT BE VERIFIED." . PHP_EOL;
                    $TL = new TableLog();
                    $TL->setHeader(['File','Status']);
                    foreach($result as $file => $status) {
                        if($file == 'integrityOK') continue;
                        $TL->addRow([$file,$status]);
                    }
                    $TL->showTable();
                }
            }

        }

        if($cmd->startswith('apps publish')) {
            $privateKey = $args['AE']->Configs->getConfigs(['signing-key'])['signing-key'];

            if(!file_exists($privateKey)) {
                print "Private key ($privateKey) is missing or inaccessible. Cannot sign app." . PHP_EOL;
                return ['success' => false];
            }

            //Figure out the on-disk app name
            $requestedApp = $cmd->leftStrip('apps publish', true);

            $buffer = explode('/',dirname($AE->availableApps[$requestedApp]));
            $appFolder = end($buffer);

            $args['privateKeyPath'] = $privateKey;
            $args['appName']        = $appFolder;

            $options['AE'] = $args['AE'];

            $Signer = new \PHPAnt\Core\PHPAntSigner($options);
            $Signer->setApp($appFolder);
            $results = $Signer->publish($args);
            
            $TL = new TableLog();
            $TL->setHeader(["Task","Result"]);
            foreach($results as $key => $value) {
                switch($key) {
                    case 'appActions':
                        $key = 'Action List for this App (JSON)';
                        break;

                    case 'cleanAppCredentials':
                        $key = 'App Credentials Cleaned';
                        break;

                    case 'generateManifestFile':
                        $key = 'Generate Manifest File';
                        break;

                    case 'saveDerivePublicKey':
                        $key = 'Derived Public Key';
                        break;

                    case 'setApp':
                        $key = 'Setting Application for Signing';
                        break;

                    case 'signApp':
                        $key = 'Signing App';
                        break;

                    case 'verifyApp':
                        $key = 'Verifying Signature';
                        $value = $value['integrityOK'];
                        break;

                }

                if(is_bool($value)) $value = ($value?"OK":"FAILED");

                $TL->addRow([$key,$value]);
            }

            $TL->showTable();

        }

        return ['success' => true];
    }
}