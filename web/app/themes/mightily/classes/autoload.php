<?php

/* Load classes only if needed, and without needing require() */
spl_autoload_register(function ($name) {

    // Everything but the last three lines are optimizations to
    // early out if we know this class isn't for us.
    //
    // We want to know which namespaces we are loading classes for.
    // This saves us from searching the filesystem for classes we know
    // we aren't responsible for.

    static $namespaces = [];

    // Get a list of directories, as this corresponds to the namespaces
    // we want to use.
    if (empty($namespaces)) {
        $dir = new DirectoryIterator( dirname(__FILE__ ));
        foreach ( $dir as $fileinfo ) {
            if ( $fileinfo->isDir() && ! $fileinfo->isDot() ) {
                $namespaces[] = $fileinfo->getFilename();
            }
        }
    }

	// Get the first part of the name, which is the root namespace
    // APH/MyNamespace/Class.php -> "APH"
    $parts = explode('\\', $name);
    $root_namespace = array_shift($parts);
    if (! in_array($root_namespace, $namespaces)) return false;

    // We are authoritative for this namespace. Load the file if it exists.
	$name = str_replace("\\", '/', $name);
	$fileForClass = dirname(__FILE__) . "/$name.php";
	if (file_exists($fileForClass)) {
	    include($fileForClass);
	    // die("loading $fileForClass");
	    return true;
    }

	return false;

});

