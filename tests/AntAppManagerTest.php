
<?php

use PHPUnit\Framework\TestCase;

class AppManagerTest extends TestCase
{
    function testGenerateManifest() {
        $AM = new \PHPAnt\Core\AppManager();
        $options = getDefaultOptions();
        $options['disableApps'] = true;
        //$options['appRoot']     = 'tests/PHPAnt/Core/resources/Apps/';
        $AE = getMyAppEngine($options);

        //Make sure we ENABLE the app manager!
        $name = "+Core App Manager";
        $AE->enableApp($name,$AE->availableApps[$name]);
        $AE->activateApps();

        $name = "Test Ant App";
        $command = new \PHPAnt\Core\Command("apps manifest generate $name");

        $args            = [];
        $args['AE']      = $AE;
        $args['command'] = $command;

        $return = $AE->runActions('cli-command',$args);   
        $this->assertTrue($return['success']);        
    }

    function testGetActionList() {
        $AM = new \PHPAnt\Core\AppManager();
        $options = getDefaultOptions();
        $options['disableApps'] = true;
        $AE = getMyAppEngine($options);

        //Make sure we ENABLE our app!
        $name = "+Core App Manager";
        $AE->enableApp($name,$AE->availableApps[$name]);
        $AE->activateApps();

        //We have to have at least one app...
        $this->assertGreaterThan(0, count($AE->apps),"App count was zero? Not one loaded app? Seems odd.");
    }

    /* Testing commands */
    function testAppsList() {
        $AM = new \PHPAnt\Core\AppManager();
        $options = getDefaultOptions();
        $options['disableApps'] = true;
        $AE = getMyAppEngine($options);

        //Make sure we ENABLE our app!
        $name = "+Core App Manager";
        $AE->enableApp($name,$AE->availableApps[$name]);
        $AE->activateApps();

        $args = [];
        $args['AE'] = $AE;
        $command = new \PHPAnt\Core\Command('apps list');
        $args['command'] = $command;

        $AE->runActions('cli-command',$args);
        $expectedOutput = PHP_EOL. "List what? apps list [available | enabled]" . PHP_EOL . PHP_EOL;
        $this->expectOutputString($expectedOutput);
    }
    function testAppsListAvailable() {
        $AM = new \PHPAnt\Core\AppManager();
        $options = getDefaultOptions();
        $options['disableApps'] = true;
        $AE = getMyAppEngine($options);

        //Make sure we ENABLE our app!
        $name = "+Core App Manager";
        $AE->enableApp($name,$AE->availableApps[$name]);
        $AE->activateApps();

        $args = [];
        $args['AE'] = $AE;
        $command = new \PHPAnt\Core\Command('apps list available');
        $args['command'] = $command;
        $regex = '/Available apps\n\n-{0,}\nApp *Path *\n-{0,}/';
        //$regex = '/Available apps.*/';
        $this->expectOutputRegex($regex);

        $AE->runActions('cli-command',$args);
    }

    function testAppsListEnabled() {
        $AM = new \PHPAnt\Core\AppManager();
        $options = getDefaultOptions();
        $options['disableApps'] = true;
        $AE = getMyAppEngine($options);

        //Make sure we ENABLE our app!
        $name = "+Core App Manager";
        $AE->enableApp($name,$AE->availableApps[$name]);
        $AE->activateApps();

        $args = [];
        $args['AE'] = $AE;
        $command = new \PHPAnt\Core\Command('apps list enabled');
        $args['command'] = $command;
        $regex = '/Enabled apps\n\n-{0,}\nApp *Path *\n-{0,}/';
        //$regex = '/Available apps.*/';
        $this->expectOutputRegex($regex);

        $AE->runActions('cli-command',$args);
    }

    function testAppsEnable() {
        $AM = new \PHPAnt\Core\AppManager();
        $options = getDefaultOptions();
        $options['disableApps'] = true;
        $AE = getMyAppEngine($options);

        //Make sure we ENABLE our app!
        $name = "+Core App Manager";
        $AE->enableApp($name,$AE->availableApps[$name]);
        $AE->activateApps();

        //Make now, we have to enable the default app!
        $name = "Default Grammar";
        $command = new \PHPAnt\Core\Command("apps enable $name");

        $args            = [];
        $args['AE']      = $AE;
        $args['command'] = $command;

        $return = $AE->runActions('cli-command',$args);
        $this->expectOutputString('App successfully enabled. Use apps reload to activate it' . PHP_EOL);

        $this->assertTrue($return['success']);

        $AE->activateApps();

        //We have to have at least one app...
        $this->assertGreaterThan(0, count($AE->apps),"App count was zero? Not one loaded app? Seems odd.");
        $this->assertEquals(2, count($AE->apps));

        //var_dump($AE->enabledApps);

        $actionList = $AM->getActionList($AE);
        $this->assertGreaterThan(3,count($actionList),"Seems the App Engine does not have any apps or those apps don't have any hooks?");

    }    

    function testAppsDisable() {
        $AM = new \PHPAnt\Core\AppManager();
        $options = getDefaultOptions();
        $options['disableApps'] = true;
        $AE = getMyAppEngine($options);

        //Make sure we ENABLE our app!
        $name = "+Core App Manager";
        $AE->enableApp($name,$AE->availableApps[$name]);
        $name = "Default Grammar";
        $AE->enableApp($name,$AE->availableApps[$name]);
        $AE->activateApps();

        //We have to have our apps enabled
        $this->assertGreaterThan(0, count($AE->apps),"App count was zero? Not one loaded app? Seems odd.");
        $this->assertEquals(2, count($AE->apps));

        $this->assertEquals(2, count($AE->enabledApps));
        
        $actionList = $AM->getActionList($AE);
        $this->assertGreaterThan(3,count($actionList),"Seems the App Engine does not have any apps or those apps don't have any hooks?");
        //Make now, we have to enable the default app!

        //Now, let's disable the Default Grammar app.
        
        $name = "Default Grammar";
        $command = new \PHPAnt\Core\Command("apps disable $name");

        $args            = [];
        $args['AE']      = $AE;
        $args['command'] = $command;

        $return = $AE->runActions('cli-command',$args);
        $this->expectOutputString("Default Grammar successfully disabled." . PHP_EOL);

        $this->assertTrue($return['success']);

        $this->assertGreaterThan(0, count($AE->apps),"App count was zero? Not one loaded app? Seems odd.");
        $this->assertEquals(2, count($AE->apps));        
        $this->assertEquals(1, count($AE->enabledApps));
    }

    /**
     * @dataProvider providerTestDumpExport
     **/

    function testDumpExport($expected) {
        $AM = new \PHPAnt\Core\AppManager();
        $options = getDefaultOptions();
        $options['disableApps'] = true;
        $AE = getMyAppEngine($options);

        //Make sure we ENABLE our app!
        $name = "+Core App Manager";
        $AE->enableApp($name,$AE->availableApps[$name]);
        $name = "Default Grammar";
        $AE->enableApp($name,$AE->availableApps[$name]);
        $AE->activateApps();

        //Get the app manager itself.
        foreach($AE->apps as $app) {
            if($app->appName == 'App Manager') break;
        }

        $args['AE'] = $AE;

        $dump = $app->getGitDump($args);
        $fh = fopen('/tmp/dump.json','w');
        fwrite($fh,$dump);
        fclose($fh);

        $this->assertJson($dump);

        $actualObject   = json_decode($dump);

        $expectedObject = json_decode($expected);

        foreach($expectedObject as $expectedApp) {
            $path = $expectedApp->path;

            $this->assertObjectHasAttribute($path, $actualObject);
            $actualApp = $actualObject->$path;

            $this->assertSame($expectedApp->path  , $actualApp->path   );
            $this->assertSame($expectedApp->name  , $actualApp->name   );
            //These will change. Only enable this when you're debugging.
            //$this->assertSame($expectedApp->hash  , $actualApp->hash   );
            //$this->assertSame($expectedApp->remote, $actualApp->remote );
        }
        
    }

    function providerTestDumpExport() {
        return  [ [ '{"/home/michael/php/php-ant/includes/apps/ant-theme-material-design/app.php":{"name":"PHP Ant Material Design Theme","path":"/home/michael/php/php-ant/includes/apps/ant-theme-material-design/app.php","hash":"f51b9fe","remote":"git@github.com:mjmunger/ant-theme-material-design.git"},"/home/michael/php/php-ant/includes/apps/ant-app-default/app.php":{"name":"Default Grammar","path":"/home/michael/php/php-ant/includes/apps/ant-app-default/app.php","hash":"739037f","remote":"https://github.com/mjmunger/ant-app-default.git"},"/home/michael/php/php-ant/includes/apps/ant-app-test-app/app.php":{"name":"Test Ant App","path":"/home/michael/php/php-ant/includes/apps/ant-app-test-app/app.php","hash":"d774321","remote":"git@github.com:mjmunger/ant-app-test-app.git"},"/home/michael/php/php-ant/includes/apps/ant-app-material-design-overview/app.php":{"name":"Overview Microservice","path":"/home/michael/php/php-ant/includes/apps/ant-app-material-design-overview/app.php","hash":"5d59b6c","remote":"git@github.com:mjmunger/ant-app-material-design-overview.git"},"/home/michael/php/php-ant/includes/apps/phpant-app-manager/app.php":{"name":"+Core App Manager","path":"/home/michael/php/php-ant/includes/apps/phpant-app-manager/app.php","hash":"5a8e46c","remote":"https://github.com/mjmunger/phpant-app-manager.git"},"/home/michael/php/php-ant/includes/apps/ant-app-login-form/app.php":{"name":"PHPAnt Login Form","path":"/home/michael/php/php-ant/includes/apps/ant-app-login-form/app.php","hash":"4b6e2c3","remote":"git@github.com:mjmunger/ant-app-login-form.git"},"/home/michael/php/php-ant/includes/apps/ant-app-authenticator/app.php":{"name":"PHPAnt Authenticator","path":"/home/michael/php/php-ant/includes/apps/ant-app-authenticator/app.php","hash":"8404359","remote":"git@github.com:mjmunger/ant-app-authenticator.git"},"/home/michael/php/php-ant/includes/apps/ant-app-configs/app.php":{"name":"Config Management","path":"/home/michael/php/php-ant/includes/apps/ant-app-configs/app.php","hash":"3f5f3f9","remote":"git@github.com:mjmunger/ant-app-configs.git"},"/home/michael/php/php-ant/includes/apps/ant-app-test-theme/app.php":{"name":"Test Theme","path":"/home/michael/php/php-ant/includes/apps/ant-app-test-theme/app.php","hash":"4657b74","remote":"git@github.com:mjmunger/ant-app-test-theme.git"}}']
                ];
    }

    /**
     * @dataProvider providerTestDiff
     **/

     function testDiff($jsonDump, $expectedStatusJSON) {
        $AM = new \PHPAnt\Core\AppManager();
        $options = getDefaultOptions();
        $options['disableApps'] = true;
        $AE = getMyAppEngine($options);

        //Make sure we ENABLE our app!
        $name = "+Core App Manager";
        $AE->enableApp($name,$AE->availableApps[$name]);
        $name = "Default Grammar";
        $AE->enableApp($name,$AE->availableApps[$name]);
        $AE->activateApps();

        //Get the app manager itself.
        foreach($AE->apps as $app) {
            if($app->appName == 'App Manager') break;
        }

        $args['AE'] = $AE;

        $actualDiffStatus = $app->getGitDiff($args, $jsonDump);

        //Re-encode it because that should give us the same string.
        $actualDiffJSON   = json_encode(json_decode($actualDiffStatus));
        $expectedDiffJSON = json_encode(json_decode($expectedStatusJSON));

        //These will vary from time to time. Only enable when you're testing / debugging this app / feature.
        // $this->assertSame($expectedDiffJSON, $actualDiffJSON);

        //This is added to avoid breaks / warning.
        $this->assertTrue(true);
    }

    function providerTestDiff() {
                 //First one is "everything matches"
         return  [ [ '{"/home/michael/php/php-ant/includes/apps/ant-theme-material-design/app.php":{"name":"PHP Ant Material Design Theme","path":"/home/michael/php/php-ant/includes/apps/ant-theme-material-design/app.php","hash":"f51b9fe","remote":"git@github.com:mjmunger/ant-theme-material-design.git"},"/home/michael/php/php-ant/includes/apps/ant-app-default/app.php":{"name":"Default Grammar","path":"/home/michael/php/php-ant/includes/apps/ant-app-default/app.php","hash":"739037f","remote":"https://github.com/mjmunger/ant-app-default.git"},"/home/michael/php/php-ant/includes/apps/ant-app-test-app/app.php":{"name":"Test Ant App","path":"/home/michael/php/php-ant/includes/apps/ant-app-test-app/app.php","hash":"d774321","remote":"git@github.com:mjmunger/ant-app-test-app.git"},"/home/michael/php/php-ant/includes/apps/ant-app-material-design-overview/app.php":{"name":"Overview Microservice","path":"/home/michael/php/php-ant/includes/apps/ant-app-material-design-overview/app.php","hash":"5d59b6c","remote":"git@github.com:mjmunger/ant-app-material-design-overview.git"},"/home/michael/php/php-ant/includes/apps/phpant-app-manager/app.php":{"name":"+Core App Manager","path":"/home/michael/php/php-ant/includes/apps/phpant-app-manager/app.php","hash":"5a8e46c","remote":"https://github.com/mjmunger/phpant-app-manager.git"},"/home/michael/php/php-ant/includes/apps/ant-app-login-form/app.php":{"name":"PHPAnt Login Form","path":"/home/michael/php/php-ant/includes/apps/ant-app-login-form/app.php","hash":"4b6e2c3","remote":"git@github.com:mjmunger/ant-app-login-form.git"},"/home/michael/php/php-ant/includes/apps/ant-app-authenticator/app.php":{"name":"PHPAnt Authenticator","path":"/home/michael/php/php-ant/includes/apps/ant-app-authenticator/app.php","hash":"8404359","remote":"git@github.com:mjmunger/ant-app-authenticator.git"},"/home/michael/php/php-ant/includes/apps/ant-app-configs/app.php":{"name":"Config Management","path":"/home/michael/php/php-ant/includes/apps/ant-app-configs/app.php","hash":"3f5f3f9","remote":"git@github.com:mjmunger/ant-app-configs.git"},"/home/michael/php/php-ant/includes/apps/ant-app-test-theme/app.php":{"name":"Test Theme","path":"/home/michael/php/php-ant/includes/apps/ant-app-test-theme/app.php","hash":"4657b74","remote":"git@github.com:mjmunger/ant-app-test-theme.git"}}'  , '{"missing":[],"hash":[],"extra":[]}' ] //Everything is OK. 

                 //This one shows that the remote system is missing something we have here.
                 , [ '{"/home/michael/php/php-ant/includes/apps/ant-app-default/app.php":{"name":"Default Grammar","path":"/home/michael/php/php-ant/includes/apps/ant-app-default/app.php","hash":"739037f","remote":"https://github.com/mjmunger/ant-app-default.git"},"/home/michael/php/php-ant/includes/apps/ant-app-test-app/app.php":{"name":"Test Ant App","path":"/home/michael/php/php-ant/includes/apps/ant-app-test-app/app.php","hash":"d774321","remote":"git@github.com:mjmunger/ant-app-test-app.git"},"/home/michael/php/php-ant/includes/apps/ant-app-material-design-overview/app.php":{"name":"Overview Microservice","path":"/home/michael/php/php-ant/includes/apps/ant-app-material-design-overview/app.php","hash":"5d59b6c","remote":"git@github.com:mjmunger/ant-app-material-design-overview.git"},"/home/michael/php/php-ant/includes/apps/phpant-app-manager/app.php":{"name":"+Core App Manager","path":"/home/michael/php/php-ant/includes/apps/phpant-app-manager/app.php","hash":"5a8e46c","remote":"https://github.com/mjmunger/phpant-app-manager.git"},"/home/michael/php/php-ant/includes/apps/ant-app-login-form/app.php":{"name":"PHPAnt Login Form","path":"/home/michael/php/php-ant/includes/apps/ant-app-login-form/app.php","hash":"4b6e2c3","remote":"git@github.com:mjmunger/ant-app-login-form.git"},"/home/michael/php/php-ant/includes/apps/ant-app-authenticator/app.php":{"name":"PHPAnt Authenticator","path":"/home/michael/php/php-ant/includes/apps/ant-app-authenticator/app.php","hash":"8404359","remote":"git@github.com:mjmunger/ant-app-authenticator.git"},"/home/michael/php/php-ant/includes/apps/ant-app-configs/app.php":{"name":"Config Management","path":"/home/michael/php/php-ant/includes/apps/ant-app-configs/app.php","hash":"3f5f3f9","remote":"git@github.com:mjmunger/ant-app-configs.git"},"/home/michael/php/php-ant/includes/apps/ant-app-test-theme/app.php":{"name":"Test Theme","path":"/home/michael/php/php-ant/includes/apps/ant-app-test-theme/app.php","hash":"4657b74","remote":"git@github.com:mjmunger/ant-app-test-theme.git"}}'  , '{"missing":[],"hash":[],"extra":[{"app":"/home/michael/php/php-ant/includes/apps/ant-theme-material-design/app.php","remote":"git@github.com:mjmunger/ant-theme-material-design.git","hash":"f51b9fe"}]}' ] //Extra app (locally) that's not on the remote.

                 //This one shows that this system is missing something the remote system has.
                 , [ '{"/home/michael/php/php-ant/includes/apps/this-app-missing/app.php":{"name":"Missing Test App","path":"/home/michael/php/php-ant/includes/apps/this-app-missing/app.php","hash":"f51b9f9","remote":"git@github.com:mjmunger/missing-test-app.git"},"/home/michael/php/php-ant/includes/apps/ant-theme-material-design/app.php":{"name":"PHP Ant Material Design Theme","path":"/home/michael/php/php-ant/includes/apps/ant-theme-material-design/app.php","hash":"f51b9fe","remote":"git@github.com:mjmunger/ant-theme-material-design.git"},"/home/michael/php/php-ant/includes/apps/ant-app-default/app.php":{"name":"Default Grammar","path":"/home/michael/php/php-ant/includes/apps/ant-app-default/app.php","hash":"739037f","remote":"https://github.com/mjmunger/ant-app-default.git"},"/home/michael/php/php-ant/includes/apps/ant-app-test-app/app.php":{"name":"Test Ant App","path":"/home/michael/php/php-ant/includes/apps/ant-app-test-app/app.php","hash":"d774321","remote":"git@github.com:mjmunger/ant-app-test-app.git"},"/home/michael/php/php-ant/includes/apps/ant-app-material-design-overview/app.php":{"name":"Overview Microservice","path":"/home/michael/php/php-ant/includes/apps/ant-app-material-design-overview/app.php","hash":"5d59b6c","remote":"git@github.com:mjmunger/ant-app-material-design-overview.git"},"/home/michael/php/php-ant/includes/apps/phpant-app-manager/app.php":{"name":"+Core App Manager","path":"/home/michael/php/php-ant/includes/apps/phpant-app-manager/app.php","hash":"5a8e46c","remote":"https://github.com/mjmunger/phpant-app-manager.git"},"/home/michael/php/php-ant/includes/apps/ant-app-login-form/app.php":{"name":"PHPAnt Login Form","path":"/home/michael/php/php-ant/includes/apps/ant-app-login-form/app.php","hash":"4b6e2c3","remote":"git@github.com:mjmunger/ant-app-login-form.git"},"/home/michael/php/php-ant/includes/apps/ant-app-authenticator/app.php":{"name":"PHPAnt Authenticator","path":"/home/michael/php/php-ant/includes/apps/ant-app-authenticator/app.php","hash":"8404359","remote":"git@github.com:mjmunger/ant-app-authenticator.git"},"/home/michael/php/php-ant/includes/apps/ant-app-configs/app.php":{"name":"Config Management","path":"/home/michael/php/php-ant/includes/apps/ant-app-configs/app.php","hash":"3f5f3f9","remote":"git@github.com:mjmunger/ant-app-configs.git"},"/home/michael/php/php-ant/includes/apps/ant-app-test-theme/app.php":{"name":"Test Theme","path":"/home/michael/php/php-ant/includes/apps/ant-app-test-theme/app.php","hash":"4657b74","remote":"git@github.com:mjmunger/ant-app-test-theme.git"}}'  , '{"missing":[{"app":"/home/michael/php/php-ant/includes/apps/this-app-missing/app.php","remote":"git@github.com:mjmunger/missing-test-app.git","hash":"f51b9f9"}],"hash":[],"extra":[]}' ] //Missing app (locally) that's ont he remote.

                 //This one shows that a hash is mismatched, and one of them needs an update.
                 , [ '{"/home/michael/php/php-ant/includes/apps/ant-theme-material-design/app.php":{"name":"PHP Ant Material Design Theme","path":"/home/michael/php/php-ant/includes/apps/ant-theme-material-design/app.php","hash":"f51b9f9","remote":"git@github.com:mjmunger/ant-theme-material-design.git"},"/home/michael/php/php-ant/includes/apps/ant-app-default/app.php":{"name":"Default Grammar","path":"/home/michael/php/php-ant/includes/apps/ant-app-default/app.php","hash":"739037f","remote":"https://github.com/mjmunger/ant-app-default.git"},"/home/michael/php/php-ant/includes/apps/ant-app-test-app/app.php":{"name":"Test Ant App","path":"/home/michael/php/php-ant/includes/apps/ant-app-test-app/app.php","hash":"d774321","remote":"git@github.com:mjmunger/ant-app-test-app.git"},"/home/michael/php/php-ant/includes/apps/ant-app-material-design-overview/app.php":{"name":"Overview Microservice","path":"/home/michael/php/php-ant/includes/apps/ant-app-material-design-overview/app.php","hash":"5d59b6c","remote":"git@github.com:mjmunger/ant-app-material-design-overview.git"},"/home/michael/php/php-ant/includes/apps/phpant-app-manager/app.php":{"name":"+Core App Manager","path":"/home/michael/php/php-ant/includes/apps/phpant-app-manager/app.php","hash":"5a8e46c","remote":"https://github.com/mjmunger/phpant-app-manager.git"},"/home/michael/php/php-ant/includes/apps/ant-app-login-form/app.php":{"name":"PHPAnt Login Form","path":"/home/michael/php/php-ant/includes/apps/ant-app-login-form/app.php","hash":"4b6e2c3","remote":"git@github.com:mjmunger/ant-app-login-form.git"},"/home/michael/php/php-ant/includes/apps/ant-app-authenticator/app.php":{"name":"PHPAnt Authenticator","path":"/home/michael/php/php-ant/includes/apps/ant-app-authenticator/app.php","hash":"8404359","remote":"git@github.com:mjmunger/ant-app-authenticator.git"},"/home/michael/php/php-ant/includes/apps/ant-app-configs/app.php":{"name":"Config Management","path":"/home/michael/php/php-ant/includes/apps/ant-app-configs/app.php","hash":"3f5f3f9","remote":"git@github.com:mjmunger/ant-app-configs.git"},"/home/michael/php/php-ant/includes/apps/ant-app-test-theme/app.php":{"name":"Test Theme","path":"/home/michael/php/php-ant/includes/apps/ant-app-test-theme/app.php","hash":"4657b74","remote":"git@github.com:mjmunger/ant-app-test-theme.git"}}'  , '{"missing":[],"hash":[{"app":"/home/michael/php/php-ant/includes/apps/ant-theme-material-design/app.php","remote":"git@github.com:mjmunger/ant-theme-material-design.git","hash":"f51b9f9"}],"extra":[]}' ] //Hash is mismatched
                 ];
    }
}