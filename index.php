<?php

require 'vendor/autoload.php';

use chillerlan\QRCode\{QRCode, QROptions};



class User{
    public function __construct($link){
        $content = file_get_contents($link);
        $data = json_decode($content);
        foreach ($data AS $key => $value) $this->{$key} = $value;
    }

    public function getUserJSON(){
        return json_encode($this);
    }

    public function getDomain(){
        $email = $this->email;
        $domain_name = substr(strrchr($email, "@"), 1);
        echo "Domain name: ". $domain_name .'<br>';
        return $domain_name;
    }
}
function SelectDomainQuery($domain_name, $table_domain_name):string{
    $query = "SELECT `id` FROM `".$table_domain_name."` WHERE `domain` = '".$domain_name."' LIMIT 1" ;
    return  $query;
}

function InsertEmailQuery($email, $domain_id, $table_email_name):string{
    $query = "INSERT INTO `".$table_email_name."` (`email`, `domain_id`) VALUES ('".$email."', ".$domain_id.");";
    return $query;
}
function InsertDomainQuery($domain, $table_domain_name):string{
    $query = "INSERT INTO `".$table_domain_name."` (`domain`, `total`) VALUES ('".$domain."', 1)";
    return $query;
}
function UpdateDomainCountQuery($domain_id, $table_domain_name):string{
    $query = "UPDATE `".$table_domain_name."` SET total = total + 1 WHERE `id` = ".$domain_id;
    return $query;
}

//zadanie 1
echo 'Zadanie 1'.'<br>';
$link1 = "https://jsonplaceholder.typicode.com/users/1";
$user1 = new User($link1);
print_r($user);

//zadanie 2
echo '<br><br>'.'Zadanie 2'.'<br>';
echo 'Email: '. $user->email.'<br>';
$user->getDomain();

//zadanie 3
echo '<br>'.'Zadanie 3'.'<br>';
$userJSON = $user->getUserJSON();
echo $userJSON; 

echo '<img src="'.(new QRCode())->render($userJSON).'" alt="QR Code" />';


//zadanie 4
$link2 = "https://jsonplaceholder.typicode.com/users/2";
$user2 = new User($link2);
echo '<br>'.'Zadanie 4'.'<br>';

$servername = "localhost";
$username = "root";
$password = "";
$db_name = "test_user";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully"."<br>";


$query = "CREATE DATABASE ".$db_name;

if ($conn->query($query) === TRUE) {
  echo "Database created successfully"."<br>";
} else {
  echo "Error creating database: " . $conn->error. "<br>";
}
$conn->close();

$conn = new mysqli($servername, $username, $password, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  echo "Connected to database ".$db_name." successfully"."<br>";
$table_email = "UserEmail";
$create_table_1 = "CREATE TABLE UserEmail (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(50) NOT NULL,
    domain_id int(6) UNSIGNED NOT NULL)";

if ($conn->query($create_table_1) === TRUE) {
    echo "Table ". $table_email ." created successfully"."<br>";
} else {
    echo "Error creating table: " . $conn->error."<br>";
}

$table_domain = "DomainCount";
$create_table_2 = "CREATE TABLE DomainCount (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    domain VARCHAR(50) NOT NULL,
    total int(6) UNSIGNED NOT NULL)";

if ($conn->query($create_table_2) === TRUE) {
    echo "Table ". $table_domain ." created successfully"."<br>";
} else {
    echo "Error creating table: " . $conn->error."<br>";
}

$email = $user->email;
$domain = $user->getDomain();
$query = SelectDomainQuery($domain, $table_domain);
echo $query."<br>";

///check damain name in table
$result = $conn->query($query);

//if exist damain name in table
if ($result->num_rows>0) { 
  $row = $result->fetch_assoc();
  $domain_id = $row["id"];
  $result_insert_email = $conn->query(InsertEmailQuery($email, $domain_id, $table_email)); 
  if ($result_insert_email === TRUE){ 
        // insert email
        echo "Record E-mail Inserted Successfully<br>";
        $result_update_domain = $conn->query(UpdateDomainCountQuery($domain_id, $table_domain));
        echo UpdateDomainCountQuery($domain_id, $table_domain)."<br>";
        if ($result_update_domain === TRUE){
            echo "Record Domain Updated Successfully<br>";
        }else{
            echo "UpdateDomainCountQuery error<br>" . $conn->error;
        }

  }else {
        echo "InsertEmailQuery error<br>". $conn->error;
  }

} else {
    $queryInsertDomain = InsertDomainQuery($domain, $table_domain);
    echo $queryInsertDomain."<br>";
    $result_insert_domain = $conn->query(InsertDomainQuery($domain, $table_domain));

    if ($result_insert_domain === TRUE){
        echo "Record Domain Inserted Successfully<br>";
        $domain_last_id = $conn->insert_id;
        $result_insert_email = $conn->query(InsertEmailQuery($email, $domain_last_id, $table_email));
        if ($result_insert_email === TRUE){
            echo "Record E-mail Inserted Successfully<br>";
        }else {
            echo "InsertEmailQuery error<br>". $conn->error;
        }
        
    }
    else {
        echo "InsertDomainQuery error<br>". $conn->error;
    }
}



$conn->close();
?>