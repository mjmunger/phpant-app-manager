<?php

namespace %PROJECT%\%SUBSPACE%;

use PHPAnt\Core\AntApp;
use PHPAnt\Core\AppInterface;

use PHPAnt\Core\CommandInvoker;
use PHPAnt\Core\CommandList;

/**
 * App Name: %FRIENDLYNAME%
 * App Description: %APPDESCRIPTION%
 * App Version: 1.0
 * App Action: cli-load-grammar  -> load%SYSTEMNAME%       @ 90
 * App Action: cli-init          -> declareMySelf          @ 50
 * App Action: cli-command       -> processCommand         @ 50
 * App Action: load_loaders      -> %SYSTEMNAME%AutoLoader @ 50
 *
 * Actions may be remarked out by changing the ":" to a "#"
 * Example remarked action:
 * App Action# foo-action       -> doSomeWork             @ 50
 *
 * CSS and JS injections:
 * 
 * App Action: header-inject-css -> injectCSS              @ 50
 * App Action: footer-inject-js  -> injectFooterJS         @ 50
 * App Action: header-inject-js  -> injectHeaderJS         @ 50
 */

 /**
 * This app adds the %FRIENDLYNAME% and commands into the CLI by adding in
 * the grammar for commands into an array, and returning it up the chain.
 *
 * @package      %PROJECT%
 * @subpackage   %SUBSPACE%
 * @category     %CATEGORY%
 * @author       %AUTHORNAME% <michael@highpoweredhelp.com>
 */ 


class %SYSTEMNAME% extends AntApp  implements AppInterface  {

    /**
     * Instantiates an instance of the %SYSTEMNAME% class.
     * Example:
     *
     * <code>
     * $app%SYSTEMNAME% = new %SYSTEMNAME%();
     * </code>
     *
     * @return void
     * @author %AUTHORNAME% <%AUTHOREMAIL%>
     **/

    function __construct() {
        $this->appName = '%FRIENDLYNAME%';
        $this->canReload = true;
        $this->path = __DIR__;

        //requires to use the CommandList to get grammar... and to avoid crashes.
        $this->AppCommands = new CommandList();
        $this->loadCommands();
    }

    /**
     * Callback for the cli-load-grammar action, which adds commands specific to this plugin to the CLI grammar.
     * Example:
     *
     * @return array An array of CLI grammar that will be merged with the rest of the grammar. 
     * @author %AUTHORNAME% <%AUTHOREMAIL%>
     **/

    function load%SYSTEMNAME%() {
        $grammar = [];

        $this->loaded = true;

        /* Example command invoker. Use this for adding CLI commands:

        This code will cause the CLI command `foo bar` to execute the method $this->doSomeWork:

        //Hard way. Use the easy way below. This is here FYI because older apps used this way.
        $callback = 'doSomeWork';
        $criteria = ['is' => ['foo bar' => true]];
        $Command = new CommandInvoker($callback);
        $Command->addCriteria($criteria);
        $this->AppCommands->add($Command);
        
        //Easy way
        $Command = new CommandInvoker('doSomeWork');
        $Command->is('foo bar');
        $this->AppCommands->add($Command);

        //Easy way for "starts with"
        $Command = new CommandInvoker('doSomeWork');
        $Command->startsWith('foo bar');
        $this->AppCommands->add($Command);

        */

        $grammar = array_merge_recursive($grammar, $this->AppCommands->getGrammar());
        
        $results['grammar'] = $grammar;
        $results['success'] = true;
        return $results;
    }

%AUTOLOADER%
    
    /**
     * Callback function that prints to the CLI during cli-init to show this plugin has loaded.
     * Example:
     *
     * @return array An associative array declaring the status / success of the operation.
     * @author %AUTHORNAME% <michael@highpoweredhelp.com>
     **/

    function declareMySelf() {
        if($this->verbosity > 4 && $this->loaded ) print("%FRIENDLYNAME% app loaded.\n");

        return ['success' => true];
    }

    function processCommand($args) {
        $cmd = $args['command'];

        return ['success' => true];
    }


}