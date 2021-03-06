<?php
/*
    Below are the length definitions for the columns in the
    flat file. You may use it, move it, delete it. Whatever
    works for you. Please put all your code in this file and return
    it for review.
 */
$definitions = [
   ['name' => 'Serial Number', 'length' => 16, 'type' => 'int'],
    ['name' => 'Language', 'length' => 3, 'type' => 'string'],
    ['name' => 'Business Name', 'length' => 32, 'type' => 'string'],
    ['name' => 'Business Code', 'length' => 8, 'type' => 'string'],
    ['name' => 'Authorization Code', 'length' => 8, 'type' => 'string'],
    ['name' => 'Timestamp', 'length' => 20, 'type' => 'timestamp'],
];

//validate input argument
if ($argc!=2){
    exit("invalid argument!");
}

//open txt file for input based on input
$filename = $argv[1];
$fhandle = fopen($filename,'r') or exit("unable to open file ($filename)");

//initialize all variables
$linecount = 1;
$rowSize = 0;
$businessList = array();
$serNum=""; $lang=""; $name=""; $busCode=""; $authCode=""; $time="";

//calculate total rowSize for each based on provided definitions
for ($row = 0; $row < sizeof($definitions); $row++){
    $rowSize += $definitions[$row]['length'];
}

//read file line by line and create object for each business
 while (($line = fgets($fhandle)) !== false) {
    //strip newline char
    $line = preg_replace("/[\n|\r]/",'',$line);

    //validate the line size
    if (strlen($line)>($rowSize)){
        print ("Row is too long at line $linecount\n\n");
        $linecount++;
        continue;
    }

    //strip each part of infomation
    if (preg_match('/^\d*/',$line,$match)!==-1 && strlen($serNum=$match[0])!==$definitions[0]['length']) {continue;}
    if (preg_match('/^\d*([A-Z]*)/',$line,$match)!==-1 && strlen($lang=$match[1])!==$definitions[1]['length']) {continue;}
    if (preg_match('/^\S+\s*(\D*)/',$line,$match)!==-1 && strlen($name=$match[1])>=$definitions[2]['length']) {continue;}
    if (preg_match('/^\S+\s*\D+(.{8})/',$line,$match)!==-1) {$busCode=$match[1];}
    else {continue;}
    if (preg_match('/(.{8})\s*\S*\s*\S*$/',$line,$match)!==-1) {$authCode=$match[1];}
    else {continue;}
    if (preg_match('/(\S*\s*\S*)$/',$line,$match)!==-1 && strlen($time=$match[1])==$definitions[5]['length']){continue;}

    //create new business based on infomation striped
    $newBusiness = new business($serNum, $lang, $name, $busCode, $authCode, $time);
    array_push($businessList, $newBusiness);

    //output information of each line on console
    print("Line Number: $linecount\n");
    print($newBusiness->get());
    $linecount++;
}

//define sorting function for all business, by name
function sort_business($a, $b){
    return strcmp($a->name, $b->name);
}

//sort business list based on defined function(in this case by name)
usort($businessList, 'sort_business');

//write the sorted result to file (optional function)
$outputFile = fopen("sorted_Business.txt", "w") or die("Unable to open file!");
for ($i=0; $i<sizeof($businessList); $i++){
  fwrite($outputFile, $businessList[$i]->get());
}

//close both files
fclose($outputFile);
fclose($fhandle);

//define business object
class business{
    public $serNum;
    public $lang ;
    public $name;
    public $busCode;
    public $authCode;
    public $time;

    //constructor method for the object
    public function __construct($serNum, $lang, $name, $busCode, $authCode, $time){
        $this->serNum = $serNum;
        $this->lang = $lang ;
        $this->name = $name;
        $this->busCode = $busCode;
        $this->authCode = $authCode;
        $this->time = $time;
    }

    //information dump method
    public function get(){
      return("Serial Number: $this->serNum\nLanguage: $this->lang\nBusiness Name: $this->name\nBusiness Code: $this->busCode\nAuthorization Code: $this->authCode\nTimestamp: $this->time\n\n");
    }
}
