<?php

// Check if the script is being run via the CLI
if (php_sapi_name() !== 'cli') {
    die("This script can only be executed via the command line (CLI).\n");
}

// Include cml-load.php file
error_reporting(E_ALL & ~E_NOTICE);
include_once 'app/admin/cml-load.php';

// Process CLI arguments
$scriptName = array_shift($argv);
$command = array_shift($argv);

// Process optional parameters
$options = array_flip($argv);


// Execute the corresponding command
switch ($command) {
    case 'create:controller':
        $useDatabase = isset($options['--db']) || isset($options['--database']);
        $controllerName = array_shift($argv);
        createController($controllerName, $useDatabase);
        break;

    case 'cml:version':
        version();
        break;

    case 'cml:update':
        if ($checkUpdate = isset($options['--check'])) {
            checkUpdate($checkUpdate);
        } else {
            updateCML();
            echo "\nUpdate complete!\n\n";
            echo "Your now on Version: v".useTrait('getFrameworkVersion');
        }
        break;

    default:
        echo "Unknown command\n";
        break;
}

// CLI command: php cli.php create:controller TestController --db
function createController($controllerName, $useDatabase) {
    $controllerFilePath = __DIR__ . "/controllers/{$controllerName}.php";

    // Check if the file already exists
    if (file_exists($controllerFilePath)) {
        echo "The controller {$controllerName} already exists. Choose a different name.\n";
        return;
    }

    // Create the controller code based on the presence of the -db or --database parameter
    if ($useDatabase) {
        // Create the controller with database code
        $controllerCode = "<?php

namespace CML\Controllers;

use CML\Classes\DB;

class {$controllerName} extends DB {
    public function getTest(\$params) {
        
        // \$arrID = ['id' => \$params['id']];
        // \$news = DB::sql2array(\"SELECT * FROM news\");
        // return \$news;
        
        // Write your logic here
    }
}";
    } else {
        // Create the controller without database code
        $controllerCode = "<?php

namespace CML\Controllers;

class {$controllerName} {
    public function myFirstController(\$params) {
        // Write your logic here
    }
}";
    }

    // Write the controller code to the file
    if (file_put_contents($controllerFilePath, $controllerCode) !== false) {
        echo "Controller {$controllerName} created: {$controllerFilePath}\n";
    } else {
        echo "Error creating the controller\n";
    }
}

function version(){
    $version = "v".useTrait('getFrameworkVersion');
    echo "CML Framework Version: {$version}\n";
}

function updateCML($url = "https://api.github.com/repos/CallMeLeon167/CML-Framework/contents/app", $path = __DIR__.'/app') {
    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0'); // Necessary because the GitHub API requires a user-agent

    // Execute the cURL session
    $response = curl_exec($ch);
    curl_close($ch);

    // Decode the JSON response
    $files = json_decode($response);

    // Array to store directories
    $directories = [];

    // Loop through each file in the response
    foreach ($files as $file) {
        // If it's a file, download it
        if ($file->type == 'file') {
            file_put_contents($path . '/' . $file->name, file_get_contents($file->download_url));
        } 
        // If it's a directory, create it if it doesn't exist
        elseif ($file->type == 'dir') {
            $directories[] = $file;
            if (!file_exists($path . '/' . $file->name)) {
                mkdir($path . '/' . $file->name, 0777, true);
            }
        }
    }

    // Recursively call the function for each subdirectory
    foreach ($directories as $dir) {
        updateCML($dir->url, $path . '/' . $dir->name);
        echo "Update ".$dir->name." complete!\n"; 
    }
}

function checkUpdate($checkUpdate){
    if($checkUpdate){
        $localVersion = "v".useTrait('getFrameworkVersion');
        $response = @json_decode(file_get_contents(
            "https://api.github.com/repos/CallMeLeon167/CML-Framework",
            false,
            stream_context_create([
                "http" => [
                    "header" => [
                        "User-Agent: Mozilla/5.0",
                    ]
                ]
            ])
        ));
        $remoteVerions = $response ? ($response->default_branch ?? "Error: Unable to retrieve default branch information.") : "Error: Unable to retrieve data from GitHub API.";
        if(strpos($remoteVerions, 'Error') !== false){
            echo "Unable to check for updates. Please try again later.\n";
            return;
        }

        // Compare version parts sequentially
        if($remoteVerions == $localVersion){
            echo "Your CML Framework is up to date. Version: {$localVersion}\n";
        } else {
            echo "CML Framework {$remoteVerions} is available. Your Version: {$localVersion}\n";
        }
    }
}