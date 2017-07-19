<?php

namespace %PROJECT%\%SUBSPACE%;

use PHPAnt\Core\CommandInvoker;
use PHPAnt\Core\CommandList;

/**
 * App Name: %FRIENDLYNAME%
 * App Description: %APPDESCRIPTION%
 * App Version: 1.0
 * App Action: cli-load-grammar  -> load%SYSTEMNAME% @ 90
 * App Action: cli-init          -> declareMySelf  @ 50
 * App Action: cli-command       -> processCommand @ 50
 * App Action: load_loaders      -> load%SYSTEMNAME%Classes @ 50
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


class %SYSTEMNAME% extends \PHPAnt\Core\AntApp implements \PHPAnt\Core\AppInterface  {

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

        $this->AppCommands = new CommandList();
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