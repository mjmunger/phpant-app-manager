
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
        $this->assertEquals(1, count($AE->apps));

        $actionList = $AM->getActionList($AE);
        $this->assertEquals(3,count($actionList),"Seems the App Engine does not have any apps or those apps don't have any hooks?");
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
        $this->assertEquals(3,count($actionList),"Seems the App Engine does not have any apps or those apps don't have any hooks?");

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
        $this->assertEquals(3,count($actionList),"Seems the App Engine does not have any apps or those apps don't have any hooks?");
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
}