<?php
/*
Joe Heffer
jheffer@gmail.com
*/
class htFile
{
  // member variables
  public $scriptName;  
  public $path;
  public $users = array();

  // methods
  // contructor with argument for the path
  function __construct($x){
    $this->path = $x;
    $this->scriptName = $this->getScriptName();
    $this->users = $this->getUsers();
  }
  function path(){
    return $this->path;
  }
  function getScriptName(){      // take the directory name from the path
    $patharr = explode('/', $this->path);  // explode path
    end($patharr); prev($patharr);    // penultimate phrase is the script name
    return current($patharr);    // return script name
  }
  function getUsers(){
    $scriptContents = file_get_contents($this->path);  // get .htaccess contents
    $scriptVec = explode("\n", $scriptContents);    // create array of lines
    foreach($scriptVec as $key => $line){      // look at each line
      $line = trim($line);        // trim whitespace
      if(substr($line, 0, 12) == "require user"){  // if line begins "require user"
        $users = substr($line, 13);    // remove "require user"
        $usersVec = explode(" ", $users);  // extract users as an array
      }
    }
    $usersVec = array_filter($usersVec, 'strlen');    // remove empty entries (i.e. strlen = 0)
    // Multi-line username entry (separated by backslashes)
    if($usersVec[0] == "\\"){        // a slash means separated lines
      foreach($scriptVec as $key => $line){    // grab relevant lines from .htaccess file
        if(trim($line) == "require user \\"){
          $i = $key + 1;    // position of "require user \" plus one line
          break;
        }
      }
      $z = 0; $y = FALSE;        // z = usersVec pos
      for($x = $i; $y == FALSE; $x++){    // x = scriptVec pos
        if(trim($scriptVec[$x]) == ''){    // y = boolean to loop
          $y = TRUE;      // exit loop at a blank line
        }else{
          $usersVec[$z] = trim(rtrim(trim($scriptVec[$x]), '\\'));  // trim newline and \
        }
      $z++;
      }
    }
    return $usersVec;
  }
}

class htList
{
  // member variables
  public $list = array(); // array of htFile objects

  // methods
  function htArray() {
    return $this->list;
  }
  function htAdd($x){
    // create a new htFile object and add to 'list' array
    $this->list[] = new htFile($x);
  }
  function htScan($x) {
    // directory iterator for $cgi_path
    $di = new RecursiveDirectoryIterator($x);
    // find every file
    foreach(new RecursiveIteratorIterator($di) as $path => $file){
      // if the file is named .htaccess
      if($file->getFilename() == ".htaccess"){
        // add .htaccess paths to array
        $this->htAdd($path);
      }
    }
  return $htaccess_arr;
  }
}

class user
{
  public $username;    // username (string)
  public $scriptNames = array();  // array of scripts (strings) a user has access to

  function __construct($x){
    $this->username = $x;        // set username
    //echo "User created: '$x'";
    //$this->scriptNames = $this->getScripts();  // scan for the scripts
  }
  function addScript($scriptName){      // add $scriptName to user $x object's $scriptName array
    if(!in_array($scriptName, $this->scriptNames)){  // if the script isn't already present
      $this->scriptNames[] = $scriptName;    // add the scriptname to the array
    }
  }
}

class userList
{
  // member variables
  public $list = array(); // array of user objects

  // methods
  function userList() {    // return users list (array)
    return $this->list;
  }
  function exists($x){    // check if a username is already in the list
    foreach($this->list as $key => $user){  // for each user object
      if($x == $user->username){  // compare to usernames
        //echo "User $x exists.";
        return TRUE;    // flag up that it already exists
      }
    }
    //echo "User $x doesn't exist.";
    return FALSE;        // if no duplicates found
  }
  function add($x){    // add user (username $x [string]) to list, if not duplicate
    if(!$this->exists($x)){
      $this->list[$x] = new user($x);
    }
    //$this->list[] = new user($x);
  }
  function scanUsers($htArray){      // take usernames from each htFile object in $htList array
    foreach($htArray as $key => $htFile){  // htList array as key => htFile object
      foreach($htFile->users as $key => $username){
        $this->add($username);  // add username to this userList
      }
    }
  }
  function scanScripts($htArray){    // populate scripts for each user
    foreach($htArray as $key => $htFile){      // scan htaccess files
      foreach($htFile->users as $key => $username){  // scan users
        $this->list[$username]->addScript($htFile->scriptName);  // add the script name to each user
      }
    }
  }
  function htmlSummary(){
    foreach($this->list as $key => $user){
      echo "<h3>$key</h3>\r\n";
      echo "<ul>\r\n";
      foreach($user->scriptNames as $key => $script){
        echo "\t<li>$script</li>\r\n";
      }
      echo "</ul>\r\n";
    }
  }
}

?> 
