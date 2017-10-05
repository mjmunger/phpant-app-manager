<?php
$buffer = simplexml_load_file('manifest.xml');
var_dump($buffer);
echo $buffer['name'];
foreach($buffer->action as $action) {
	echo $action->hook. PHP_EOL;
}