<?php
/**
 * Copyright 2017 California Institute of Technology
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @file Contains the various functions for interacting with the database. Most of these
 * functions execute a particular query to insert, update, or select data related to the
 * PLAID wizard in the database.
 *
 * Note: This is setup to interact with a MySQL database with three tables: user, label, and link.
 *
 * Creation Date: 8/9/16.
 *
 * @author Trevor Morse
 * @author Michael Kim
 * @author Stirling Algermissen
 */
require_once('../thirdparty/php/PasswordHash.php');
require("configuration.php");
include_once("PlaidSessionHandler.php");
$MAX_FILE_SIZE = 4 * 1024 * 1024;    //  4 MB
$XML_FILE_TYPE = "text/xml";
$XML_FILE_EXTENSION = "xml";
$IMPORT_DIR = "../workspace/";    // Directory that holds the uploaded XML file to be Imported
$HASHER = new PasswordHash(8, false);
try{
    $host = DB_HOST;
    $db   = DB_DATABASE;
    $port = DB_PORT;
    $user = DB_USER;
    $pass = DB_PASSWORD;

    $session_handler = new PlaidSessionHandler(); // MYSQL-backed session handler

    $LINK = new \PDO('mysql:host=' . $host .
                     ';dbname='. $db .
                     ';charset=utf8mb4;port=' . $port,
        $user,
        $pass,
        array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_PERSISTENT => false
        )
    );


    if(isset($_POST['function'])){
        call_user_func($_POST['function'], $_POST);
    }
}
catch(\PDOException $ex){
    print($ex->getMessage());   // TODO: Don't print any error message, for security reasons???
}
/**
 * When a new user creates an account, store the form data in the user table.
 * @param {Object} $args object containing the user's email, password, full name, and organization
 */
function insertUser($args){
    global $LINK;
    global $HASHER;
    //  Enable the use of Session variables
    session_start();
    //  Sanitize the input by replacing special characters with HTML representations
    $email_addr = sanitizeInput($args['email']);

    // Validate that the given e-mail address is in the correct format xxx@xxx.xxx
    $isValidEmailAddrFormat = filter_var($email_addr, FILTER_VALIDATE_EMAIL);
    if (! $isValidEmailAddrFormat) {
        //  Return to the Sign Up page with an error
        $_SESSION['error_code'] = 21;
        header("Location: ../signup.php");
        return;
    }
    // Check that the given e-mail address is not already an existing user
    $duplicateEmailAddr = checkForDuplicateUser($email_addr);
    if($duplicateEmailAddr){
        //  Return to the Login page with an error
        $_SESSION['login'] = true;
        $_SESSION['error_code'] = 3;
        header("Location: ../index.php");
        return;
    }

    $password = sanitizeInput($args['password']);
    $verifyPassword = sanitizeInput($args['verifyPassword']);
    //  Check that the two passwords are the same
    if ($password === $verifyPassword) {

        //  Create a hash value to be associated with this user, for validation
        $hash = md5( rand(0,1000) ); // Generate random 32 character hash and assign it to a local variable.
        // Example output: f4552671f8909587cf485ea990207f3b

        try {
            $handle = $LINK->prepare("INSERT INTO user SET email=?,password=?,full_name=?,organization=?,activation_hash=?, active=0");
            ///$index = 1;
            ///foreach($args as $key=>$value){
            ///    if ($key === "password"){
            ///        $handle->bindValue($index++, $HASHER->HashPassword($value));
            ///    }
            ///    else if ($key !== "function"){
            ///        $handle->bindValue($index++, $value);
            ///    }
            ///}
            $handle->bindValue(1, $email_addr);
            //  Encrypt the password
            $hashedPassword = $HASHER->HashPassword($password);
            $handle->bindValue(2, $hashedPassword);
            $handle->bindValue(3, sanitizeInput($args['full_name']));
            $handle->bindValue(4, sanitizeInput($args['organization']));
            $handle->bindValue(5, $hash);

            $handle->execute();
        } catch(PDOException $ex) {
            //print($ex->getMessage());     // Don't print any error message, for security reasons
        }

        //  Send an e-mail message to the given e-mail address with a link to validate the account
        $to = $email_addr; // Send the email to the given e-mail address
        $subject = "PLAID Signup Verification"; // Give the e-mail message a subject

        //  Build the URL for the Link
        $http = (isset($_SERVER['HTTPS']) ? "https" : "http");
        $host = $_SERVER[HTTP_HOST];
        $uri = $_SERVER['REQUEST_URI'];
        ///echo 'http = '.$http.'<br>';
        ///echo 'host = '.$host.'<br>';
        ///echo 'uri = '.$uri.'<br>';
        //  Remove the filename from the URI
        //  Find the last slash in the URI
        $last_slash_pos = strrpos($uri, '/');
        //  Get everything up to and including the last slash
        $uri_sans_filename = substr($uri, 0, $last_slash_pos+1);
        //  Build the Link to Activate the account
        ///http://localhost/myapp/php/verify_email_address.php?email='.$inactive_email.'&hash='.$hash.'
        $activation_link = $http. '://' .$host . $uri_sans_filename . 'verify_email_address.php?email='.$email_addr.'&hash='.$hash;
        ///echo 'activation_link = '.$activation_link.'<br>';

        ///$short_test_message = 'Message Line 1';
        $message = '
     
    Thanks for signing up!
    Your account has been created.  You can login with the following credentials after you have activated your account by pressing the url below.
     
    ------------------------
    Username: '.$email_addr.'
    Password: the password that you specified when you signed up
    ------------------------
     
    Please click this link to activate your account:  ' . $activation_link . '
     
    '; // Our message above including the link

        ///$headers = 'From:PLAID_Admin@jpl.nasa.gov' . '\r\n'; // Set from headers
        $headers = "From: plaid-support@jpl.nasa.gov"; // Set from headers
        $mail_return_value = mail($to, $subject, $message, $headers); // Send our email
        ///echo "mail return value = " $mail_return_value
        //  IF the mail call had an error
        if ($mail_return_value == FALSE) {
            //  Go to the User Creation Failure page
            header("Location: ../user_creation_failure.html");
        }
        else {
            //  Go to the User Creation Success page
           header("Location: ../user_creation_success.html");
        }
    } else {        //  Else the two passwords are NOT the same
        //  Return to the Sign Up page with an error
        $_SESSION['error_code'] = 20;
        header("Location: ../signup.php");
    }
}
/**
 * Verify that the user exists in the database and entered the correct password.
 * @param {Object} $args object containing the user's email and password
 */
function verifyUser($args){
    global $LINK;
    global $HASHER;
    //  Sanitize the input by replacing special characters with HTML representations
    $email_addr = sanitizeInput($args['email']);
    $password_entered = sanitizeInput($args['password']);

    // Validate that the given e-mail address is in the correct format xxx@xxx.xxx
    $isValidEmailAddrFormat = filter_var($email_addr, FILTER_VALIDATE_EMAIL);
    if (! $isValidEmailAddrFormat) {
        //  Invalid username, so return to the Login page w/ an error message
        $_SESSION['login'] = true;
        $_SESSION['error_code'] = 1;
        header("Location: ../index.php");
        return;
    }
    try {
        $handle = $LINK->prepare('select id,password,full_name,organization, active, activation_hash from user where email=?');
        $handle->bindValue(1, $email_addr);

        $handle->execute();
        $result = $handle->fetchAll(\PDO::FETCH_OBJ);

        session_start();
        if (count($result) === 1 &&
            $HASHER->CheckPassword($password_entered, $result[0]->password)){
            //  IF the account is active
            if ($result[0]->active == 1) {
                header("Location: ../dashboard.php");
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $result[0]->id;
                $_SESSION['email'] = $email_addr;
                $_SESSION['full_name'] = $result[0]->full_name;
                $_SESSION['organization'] = $result[0]->organization;
            } else {    //  Else the account is inactive
                $_SESSION['login'] = true;
                $_SESSION['error_code'] = 2;
                $_SESSION['inactive_email'] = $email_addr;
                $_SESSION['hash'] = $result[0]->activation_hash;
                //  Return to the Login page with an error message
                header("Location: ../index.php");

            }
        }
        else{
            //  Invalid username/password combination, so return to the Login page w/ an error message
            header("Location: ../index.php");
            $_SESSION['login'] = true;
            $_SESSION['error_code'] = 1;
        }
    } catch(PDOException $ex) {
        //print($ex->getMessage());     // Don't print any error message, for security reasons
    }

}

/**
 * Fetch the User's full name, given the User Id.
 * @param {Object} $userId the User's Id
 * @return user's full name
 */
function getUserName($userId){
    global $LINK;
    $handle = $LINK->prepare('select full_name from user where id=? and active=1');
    $handle->bindValue(1, $userId);

    $handle->execute();
    $result = $handle->fetch(\PDO::FETCH_OBJ);

    if ($result !== false){
        return($result->full_name);
    } else {
        return null;
    }

}

/*
*  Given a query, show all users with a name or email that starts with that query
*  @param {Object} $args object containing the search query
*/
function getUsersListing($args) {
    global $LINK;
    session_start();
    if(isset($_SESSION['user_id'])) {
        $stmt = $LINK->prepare('SELECT id, email, full_name, organization FROM user WHERE (full_name LIKE :query and id!=:user_id) or (email LIKE :query and id!=:user_id) LIMIT 0,10');
        $stmt->execute(array('query' => '%' . $args['q'] . '%',
            'user_id' => $_SESSION['user_id']));
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

/*
 * Share a label with a user
 * @param {Object} $args object containing the label to share and the user to share it with
 */

function shareLabelWithUser($args) {
    global $LINK;
    session_start();
    if(isset($_SESSION['user_id'])) {
        // first verify the label being changed is owned by the user in modifying it's links
        $stmt = $LINK->prepare('SELECT count(*) FROM label WHERE id=? AND owner=?');
        $stmt->bindValue(1, $args["label_id"]);
        $stmt->bindValue(2, $_SESSION["user_id"]);
        $stmt->execute();
        if(count($stmt->fetchAll(\PDO::FETCH_OBJ)) > 0) {
            // insert the association
            $handle = $LINK->prepare('INSERT INTO link SET user_id=?,label_id=?');
            $newLabelId = $LINK->lastInsertId();
            $handle->bindValue(1, $args["user_id"]);
            $handle->bindValue(2, $args["label_id"]);
            $handle->execute();
            getLabelShareSettings(["label_id"=> $args["label_id"]]);
        } else {
            header("HTTP/1.1 401 Unauthorized");
            exit;
        }

    }
}

/*
 * Stop sharing a label with a user
 * @param {Object} $args object containing the label to share and the user to share it with
 */

function stopSharingLabelWithUser($args) {
    global $LINK;
    session_start();
    if(isset($_SESSION['user_id'])) {
        // first verify the label being changed is owned by the user in modifying it's links
        $stmt = $LINK->prepare('SELECT count(*) FROM label WHERE id=? AND owner=?');
        $stmt->bindValue(1, $args["label_id"]);
        $stmt->bindValue(2, $_SESSION["user_id"]);
        $stmt->execute();
        if(count($stmt->fetchAll(\PDO::FETCH_OBJ)) > 0) {
            // insert the association
            $handle = $LINK->prepare('DELETE FROM link WHERE user_id=? AND label_id=?');
            $handle->bindValue(1, $args["user_id"]);
            $handle->bindValue(2, $args["label_id"]);
            $handle->execute();
            getLabelShareSettings(["label_id"=> $args["label_id"]]);
        } else {
            header("HTTP/1.1 401 Unauthorized");
            exit;
        }

    }
}


/**
 * Given a label id, show all users that have access to that label
 * @param {Object} $args object containing label to list who it is shared with
 */

function getLabelShareSettings($args) {
    global $LINK;
    session_start();
    if(isset($_SESSION['user_id'])){
        $handle = $LINK->prepare('SELECT user.full_name, user.email, user.id from user inner JOIN link ON link.user_id=user.id where link.label_id=? and user.id!=?');
        $handle->bindValue(1, $args['label_id']);
        $handle->bindValue(2, $_SESSION['user_id']);

        $handle->execute();
        echo json_encode($handle->fetchAll(PDO::FETCH_ASSOC));

    }
}


/**
 * Send an e-mail to the user with a link to Reset Password.
 * @param {Object} $args object containing the user's email address
 */
function sendLinkToResetPassword($args){
    global $LINK;
    global $HASHER;
    //  Sanitize the input by replacing special characters with HTML representations
    $email_addr = sanitizeInput($args['email']);
    // Validate that the given E-mail address is in the correct format xxx@xxx.xxx
    $isValidEmailAddrFormat = filter_var($email_addr, FILTER_VALIDATE_EMAIL);
    // IF the given E-mail address is NOT in a valid format
    if (! $isValidEmailAddrFormat) {
        // always redirect when using invalid email, per SPL tickets
        header("Location: ../send_link_to_reset_password_failure.html");
        return;
    }

    try {
        $handle = $LINK->prepare('select id from user where email=?');
        $handle->bindValue(1, $email_addr);

        $handle->execute();
        $result = $handle->fetchAll(\PDO::FETCH_OBJ);

        //  Enable the use of Session variables
        session_start();
        // IF found a user record with the given e-mail address
        if (count($result) === 1){
            //  Create a hash value to be associated with this user, for validation
            $hash = md5( rand(0,1000) ); // Generate random 32 character hash and assign it to a local variable.
            // Example output: f4552671f8909587cf485ea990207f3b

            ///$handle = $LINK->prepare("UPDATE user SET activation_hash='".$hash."' where email=?");
            $handle = $LINK->prepare("UPDATE user SET activation_hash=? where email=?");
            $handle->bindValue(1, $hash);
            $handle->bindValue(2, $args['email']);
            $handle->execute();

            //  Send an e-mail message to the given e-mail address with a link to reset the account password
            $email_addr = $args['email'];
            $to = $email_addr; // Send the email to the given e-mail address
            $subject = "PLAID Password Reset"; // Give the e-mail message a subject

            //  Build the URL for the Link
            $http = (isset($_SERVER['HTTPS']) ? "https" : "http");
            $host = $_SERVER[HTTP_HOST];
            $uri = $_SERVER['REQUEST_URI'];
            ///echo 'http = '.$http.'<br>';
            ///echo 'host = '.$host.'<br>';
            ///echo 'uri = '.$uri.'<br>';
            //  Remove the filename from the URI
            //  Find the last slash in the URI
            $last_slash_pos = strrpos($uri, '/');
            //  Get everything up to and including the last slash
            $uri_sans_filename = substr($uri, 0, $last_slash_pos+1);
            //  Build the Link to Activate the account
            ///http://localhost/myapp/php/reset_password.php?email='.$inactive_email.'&hash='.$hash.'
            $activation_link = $http. '://' .$host . $uri_sans_filename . 'reset_password.php?email='.$email_addr.'&hash='.$hash;
            ///echo 'activation_link = '.$activation_link.'<br>';

            ///$short_test_message = 'Message Line 1';
            $message = '
     
You can login with the following credentials after you have reset your PLAID password by pressing the url below.
     
------------------------
Username: '.$email_addr.'
------------------------
     
Please click this link to reset your PLAID password:  ' . $activation_link . '
     
'; // Our message above including the link

            ///$headers = 'From:PLAID_admin@jpl.nasa.gov' . '\r\n'; // Set from headers
            $headers = "From: plaid-support@jpl.nasa.gov"; // Set from headers
            $mail_return_value = mail($to, $subject, $message, $headers); // Send our email
            ///echo "mail return value = " $mail_return_value
            //  IF the mail call had an error
            if ($mail_return_value == FALSE) {
                //  Go to the Send Link to Reset Password Failure page
                header("Location: ../send_link_to_reset_password_failure.html");
            }
            else {
                //  Go to the Send Link to Reset Password Success page
                header("Location: ../send_link_to_reset_password_success.html");
            }

        } else {    // Else NO user found w/ given E-mail address
            // always redirect when using invalid email, per SPL tickets
            header("Location: ../send_link_to_reset_password_success.html");
        }
    } catch(PDOException $ex) {
        //print($ex->getMessage());     // Don't print any error message, for security reasons
    }

}

/**
 * Reset the Password.
 * @param {Object} $args object containing the user's password and verified password
 */
function resetPassword($args){
    global $LINK;
    global $HASHER;
    //  Enable the use of Session variables
    session_start();
    //  Get the email address from the Session variable
    $email = $_SESSION['email'];
    //  Sanitize the input by replacing special characters with HTML representations
    $email = sanitizeInput($email);
    // Validate that the given E-mail address is in the correct format xxx@xxx.xxx
    $isValidEmailAddrFormat = filter_var($email, FILTER_VALIDATE_EMAIL);
    // IF the given E-mail address is NOT in a valid format
    if (! $isValidEmailAddrFormat) {
        // always redirect when using invalid email, per SPL tickets
        //  Return to the Reset Password page with an error; need to re-pass the parameters in the URL
        $_SESSION['error_code'] = 21;
        //  Get the hash from the Session variable
        $hash = $_SESSION['hash'];
        header("Location: reset_password.php?email=".$email."&hash=".$hash);
        return;
    }
    //  Get the passwords from the form arguments
    $password = sanitizeInput($args[password]);
    $verified_password = sanitizeInput($args[verifyPassword]);
    //  Check that the password and verified password are the same
    if ($password === $verified_password) {
        //  Encrypt the password
        $hashed_password = $HASHER->HashPassword($password);
        try {
            //  Store the password into the User table
            $handle = $LINK->prepare("UPDATE user SET password=? where email=?");
            $handle->bindValue(1, $hashed_password);
            $handle->bindValue(2, $email);
            $handle->execute();
        } catch(PDOException $ex) {
            //print($ex->getMessage());     // Don't print any error message, for security reasons
        }

        //  Go to the Reset Password Success page
        header("Location: ../reset_password_success.html");

    } else {
        //  Return to the Reset Password page with an error; need to re-pass the parameters in the URL
        $_SESSION['error_code'] = 20;
        //  Get the hash from the Session variable
        $hash = $_SESSION['hash'];
        header("Location: reset_password.php?email=".$email."&hash=".$hash);

    }

}

/**
 * Use the user_id stored in a session variable to look up the info for all labels
 * associated with that user. Do not return labels whose is_deleted flag is set
 * and order them by last_modified time.
 */
function getLabelInfo(){
    global $LINK;
    session_start();
    if(isset($_SESSION['user_id'])){
        $handle = $LINK->prepare('select link.user_id, label.id, label.creation, label.last_modified, label.name, label.schema_version, label.owner from link inner JOIN label ON link.label_id=label.id where link.user_id=? and label.is_deleted=0 order by label.last_modified desc;');
        $handle->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
        $handle->execute();

        $result = $handle->fetchAll(\PDO::FETCH_OBJ);

        foreach ($result as &$label) {
            if($label->user_id != $label->owner) {
                // if not owner of label, say who is

                $owner_info = $LINK->prepare('select email, full_name from user where id=?');
                $owner_info->bindValue(1, $label->owner);
                $owner_info->execute();

                $owner_obj = $owner_info->fetchAll(\PDO::FETCH_OBJ);

                $label->owner_email = $owner_obj[0]->email;
                $label->owner_name = $owner_obj[0]->full_name;
            }
            $label->in_use = checkIfLabelInUse($label->id);
            if($label->in_use) {
                $label->in_use_by = getUserName($label->in_use); // provide user name when label is in use
            } else {
                $label->in_use_by = false;
            }

        }


        header('Content-type: application/json');
        echo json_encode($result);
    }
}

/**
 * When a user creates a new label, create an entry for it in the label table and link
 * it to the user in the link table.
 *
 * Note: $data will need to be updated in future once multiple product types are supported
 * in PLAID. Currently, observational is the only supported product type.
 *
 * @param {Object} $args object containing the name of the label inputted by the user
 */
function storeNewLabel($args){

    global $LINK;
    global $MAX_FILE_SIZE, $XML_FILE_TYPE, $XML_FILE_EXTENSION, $IMPORT_DIR;

    $filename = "../workspace/observational.xml";
    // $filename = "../workspace/c000m5232t493378259edr_f0000_0134m1.xml";
    $myfile = fopen($filename, "r") or die("Unable to open file!");
    $data = fread($myfile,filesize($filename));
    fclose($myfile);
    var_dump($data);
    /** JPADAMS - load a file here **/

    session_start();

    // Initialize the XML string to Import to be null
    $_SESSION['xmlStringToImport'] = null;

    // IF an XML file was selected to import
    if (isset($_FILES['xmlFileToImport'])) {
        $fileName = $_FILES['xmlFileToImport']['name'];
        $fileSize =$_FILES['xmlFileToImport']['size'];
        $fileTmpPathname =$_FILES['xmlFileToImport']['tmp_name'];
        $fileType=$_FILES['xmlFileToImport']['type'];
        $fileExt=strtolower(end(explode('.',$fileName)));
        //  IF an XML file
        if (($fileExt === $XML_FILE_EXTENSION) && ($fileType === $XML_FILE_TYPE)) {
            // IF file size < MAX_FILE_SIZE
            if ($fileSize <= $MAX_FILE_SIZE) {
                // Read the uploaded XML file's contents into a String
                $xmlFileContents = file_get_contents ($fileTmpPathname);
                // Set the contents of the uploaded XML file into a Session variable
                $_SESSION['xmlStringToImport'] = $xmlFileContents;
            }
        }
    } else {    // Else check to see if a sample label was selected to import
        // Get the sample label that was selected to import
        $selectedSampleLabelId = $args['selectedSampleLabelId'];
        // IF a Sample Label was selected to import
        if ($selectedSampleLabelId > 0) {
            // Get the sample label's XML from the DB
            $sampleLabelXML = getSampleLabelXML($selectedSampleLabelId);
            // Set the XML into a Session variable
            $_SESSION['xmlStringToImport'] = $sampleLabelXML;
        }
    }

    // Default the newLabelId to something, in case the first insert stmt. fails, and is caught
    $newLabelId = null;
    try {
        $handle = $LINK->prepare('INSERT INTO label SET creation=now(),last_modified=now(),name=?,label_xml=?,schema_version=?,owner=?');
        $handle->bindValue(1, $args['labelName']);
        $handle->bindValue(2, $data);
        $handle->bindValue(3, $args['version']);
        $handle->bindValue(4, $_SESSION['user_id']);
        $handle->execute();

        $handle = $LINK->prepare('INSERT INTO link SET user_id=?,label_id=?');
        $newLabelId = $LINK->lastInsertId();
        $handle->bindValue(1, $_SESSION['user_id']);
        $handle->bindValue(2, $newLabelId);
        $handle->execute();
    } catch(PDOException $ex) {
        //print($ex->getMessage());     // Don't print any error message, for security reasons
    }

    $_SESSION['label_id'] = intval($newLabelId);
    return $_SESSION['label_id'];
}


/**
 * When a user creates a new label via table upload, create an entry for it in the label table, link
 * it to the user in the link table, and store the modified label XML as a new label.
 *
 * Note: $data will need to be updated in future once multiple product types are supported
 * in PLAID. Currently, observational is the only supported product type.
 *
 * @param {Object} $args object containing the name of the label inputted by the user
 */
function storeXMLToANewLabel($args){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('INSERT INTO label SET creation=now(),last_modified=now(),name=?,label_xml=?,schema_version=?');
    $handle->bindValue(1, $args['labelName']);
    $handle->bindValue(2, $args['xmlDoc']->saveXML());
    $handle->bindValue(3, $args['version']);
    $handle->execute();

    $handle = $LINK->prepare('INSERT INTO link SET user_id=?,label_id=?');
    $newLabelId = $LINK->lastInsertId();
    $handle->bindValue(1, $_SESSION['user_id']);
    $handle->bindValue(2, $newLabelId);
    $handle->execute();

    $_SESSION['label_id'] = intval($newLabelId);
    return $_SESSION['label_id'];
}

/**
 * When a user chooses to modify the existing label via table upload, store the modified label XML as a new label.
 *
 * Note: $data will need to be updated in future once multiple product types are supported
 * in PLAID. Currently, observational is the only supported product type.
 *
 * @param {Object} $args object containing the name of the label inputted by the user
 */

function storeXML($args){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('update label set label_xml=? where id=?');
    $handle->bindValue(1, $args['xmlDoc']->saveXML());
    $handle->bindValue(2, $_SESSION['label_id']);
    $handle->execute();
    return $_SESSION['label_id'];
}

/**
 * Use the label id stored in the session to determine which label to output
 * the XML from the database.
 * @return {string}
 */
function getLabelXML(){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('select label_xml from label where id=?');
    $handle->bindValue(1, $_SESSION['label_id']);
    $handle->execute();

    $result = $handle->fetch(\PDO::FETCH_OBJ);
    return $result->label_xml;
}

/**
 * Use the label id in the argument to determine which label to output
 * the XML from the database.
 * @return {string}
 */
function getSampleLabelXML($labelId){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('select label_xml from sample_label where id=?');
    $handle->bindValue(1, $labelId);
    $handle->execute();

    $result = $handle->fetch(\PDO::FETCH_OBJ);
    return $result->label_xml;
}

/**
 * Use the label id in the argument to determine which label to output
 * the XML from the database.
 * @return {string}
 */
function getSpecifiedLabelXML($arg){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('select label_xml from label where id=?');
    $handle->bindValue(1, $arg['label_id']);
    $handle->execute();

    $result = $handle->fetch(\PDO::FETCH_OBJ);
    return $result->label_xml;
}

/**
 * Use the label id stored in the session to determine which Ingest LDDTool XML to output
 * from the database.
 * @return {string}
 */
function getIngestLDDToolXML($arg){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('select ingest_ldd_xml from label where id=?');
    if(isset ($arg['target_label_id'])){
        $handle->bindValue(1, $arg['target_label_id']);
    }
    else {
        $handle->bindValue(1, $_SESSION['label_id']);
    }
    $handle->execute();

    $result = $handle->fetch(\PDO::FETCH_OBJ);
    return $result->ingest_ldd_xml;
}

/**
 * Update the Label XML stored in the database with the most recent changes.
 * @param {Object} $args object containing the updated Label XML string
 */
function updateLabelXML($args){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('update label set last_modified=now(),label_xml=? where id=?');
    $handle->bindValue(1, $args['xml']);
    $handle->bindValue(2, $_SESSION['label_id']);
    $handle->execute();
}

function testUpdateLabelXML($args){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('update label set last_modified=now(),label_xml=? where id=?');
    $handle->bindValue(1, $args['xml']);
    $handle->bindValue(2, 313);
    $handle->execute();
}

/**
 * Update the Ingest LDD XML stored in the database with the most recent changes.
 * @param {Object} $ingestLddXML string containing the updated Ingest LDD XML string
 */
function updateIngestLddXML($ingestLddXML){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('update label set last_modified=now(),ingest_ldd_xml=? where id=?');
    $handle->bindValue(1, $ingestLddXML);
    $handle->bindValue(2, $_SESSION['label_id']);
    $handle->execute();
}

/**
 * Set a flag on the label entry to denote that the user has deleted it.
 * Note: this does not actually remove the label from the database so that it can
 * be recovered later if necessary.
 * @param {Object} $args object containing the id of the label to flag
 */
function deleteLabel($args){
    global $LINK;
    session_start();
    $labelInUse = checkIfLabelInUse($args['label_id']);
    if($labelInUse) {
        echo getUserName($labelInUse);
    } else {
        $handle = $LINK->prepare('update label set is_deleted=? where id=?');
        $handle->bindValue(1, 1, PDO::PARAM_INT);
        $handle->bindValue(2, $args['label_id'], PDO::PARAM_INT);
        $handle->execute();
    }
}

/**
 * Store the JSON with the user's progress in the database.
 * @param {Object} $args object containing a string-ified JSON of the user's progress
 */
function storeProgressData($args){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('update label set progress_data=? where id=?');
    $handle->bindValue(1, $args['progressJson']);
    $handle->bindValue(2, $_SESSION['label_id']);
    $handle->execute();
}

/**
 * Store the progressData JSON with the user's modifications to the label in the database associated with the label ID given in the argument.
 * @param {Object} $args object containing a string-ified JSON of the user's progress
 */
function storeProgressDataLocal($args){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('update label set progress_data=? where id=?');
    $handle->bindValue(1, $args['progressJson']);
    $handle->bindValue(2, $args['label_id']);
    $handle->execute();
}

/**
 * Get the progress data for the active label and send it to the front-end
 * as a JSON.
 */
function getProgressData(){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('select progress_data from label where id=?');
    $handle->bindValue(1, $_SESSION['label_id']);
    $handle->execute();

    $result = $handle->fetch(\PDO::FETCH_OBJ);
    header('Content-type: application/json');
    echo json_encode($result->progress_data);
    return json_encode($result->progress_data);
}

/**
 * Get the progress data for the active label and send it to the front-end
 * as a JSON.
 */
function getRawProgressData($arg){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('select progress_data from label where id=?');
    $handle->bindValue(1, $arg['label_id']);
    $handle->execute();

    $result = $handle->fetch(\PDO::FETCH_OBJ);
//    header('Content-type: application/json');
//    echo json_encode($result->progress_data);
//    return json_encode($result->progress_data);
    return utf8_encode($result->progress_data);
}

/**
 * Store the JSON with the user's progress in the database.
 * @param {Object} $args object containing a string-ified JSON of the user's mission specifics content
 */
function storeMissionSpecificsData($args){
    global $LINK;
    $missionSpecificsHeader = $args['missionSpecificsHeader'];
    $missionName = $missionSpecificsHeader['missionName'];
    $stewardId = $missionSpecificsHeader['stewardId'];
    $namespaceId = $missionSpecificsHeader['namespaceId'];
    $comment = $missionSpecificsHeader['comment'];
    session_start();
    $handle = $LINK->prepare('update label set mission_specifics=?, ms_mission_name=?, ms_steward_id=?, ms_namespace_id=?, ms_comment=?  where id=?');
    $handle->bindValue(1, $args['missionSpecificsJson']);
    $handle->bindValue(2, $missionName);
    $handle->bindValue(3, $stewardId);
    $handle->bindValue(4, $namespaceId);
    $handle->bindValue(5, $comment);
    if (isset ($args['store_location'])) {
        $handle->bindValue(6, $args['store_location']);
    } else {
        $handle->bindValue(6, $_SESSION['label_id']);
    }
    $handle->execute();
}

/**
 * Get the mission-specific data for the active label and send it to the front-end
 * as a JSON.
 */
function getMissionSpecificsData($arg){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('select mission_specifics from label where id=?');
    if(isset ($arg['target_label_id'])){
        $handle->bindValue(1, $arg['target_label_id']);
    }
    else {
        $handle->bindValue(1, $_SESSION['label_id']);
    }
    $handle->execute();

    $result = $handle->fetch(\PDO::FETCH_OBJ);
    header('Content-type: application/json');
    if(isset ($arg['isReturn'])){
        return utf8_encode($result->mission_specifics);
    }
    else {
        echo json_encode($result->mission_specifics);
    }
}

/**
 * Get the mission-specific header data for the active label and send it to the front-end
 * as an object.
 */
function getMissionSpecificsHeaderData($arg){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('select ms_mission_name, ms_steward_id, ms_namespace_id, ms_comment from label where id=?');
    if(isset ($arg['target_label_id'])){
        $handle->bindValue(1, $arg['target_label_id']);
    }else{
        $handle->bindValue(1, $_SESSION['label_id']);
    }
    $handle->execute();

    $result = $handle->fetch(\PDO::FETCH_OBJ);
    $missionSpecificsHeader['missionName'] = $result->ms_mission_name;
    $missionSpecificsHeader['stewardId'] = $result->ms_steward_id;
    $missionSpecificsHeader['namespaceId'] = $result->ms_namespace_id;
    $missionSpecificsHeader['comment'] = $result->ms_comment;

    header('Content-type: application/json');
    $missionSpecificsHeaderJson = json_encode($missionSpecificsHeader);
    if(isset ($arg['isReturn'])){
        return $missionSpecificsHeader;
    }
    else {
        echo $missionSpecificsHeaderJson;
    }
}

/**
 * Get the name of the label corresponding to the id stored in the session.
 */
function getLabelName(){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('select name from label where id=?');
    $handle->bindValue(1, $_SESSION['label_id']);
    $handle->execute();

    $result = $handle->fetch(\PDO::FETCH_OBJ);
    echo $result->name;
    return $result->name;
}

/**
 * Get the XML String to Import.
 * called by main.js in wizard.php
 */
function getXMLStringToImport() {
    session_start();
    // Get the XML String to Import from the server
    // This value was stored into a session variable by the storeNewLabel()
    // call by the Dashboard's 'Create New' popup
    $xmlStringToImport = $_SESSION['xmlStringToImport'];

    echo $xmlStringToImport;
    return $xmlStringToImport;
}

/**
 * Check all active sessions besides the current user and see if they are currently accessing the provided label id.
 * @param {Object} $label_id id of label to determine if in use
 */
function checkIfLabelInUse($label_id) {
    global $LINK;
    session_start();
    $result = $LINK->query('SELECT data FROM sessions');
    $current_user = $_SESSION['user_id'];
    $current_session = session_encode();

    foreach ($result as $row)
    {
        session_decode($row['data']);
        if($_SESSION['user_id'] != $current_user) {
            if($_SESSION['label_id'] == intval($label_id)) {
                $user_in_use = $_SESSION['user_id'];
                session_decode($current_session);
                return $user_in_use;
            }
        }
    }
    session_decode($current_session);
    return false;

}


/**
 * Check for the duplicate email address already exists in the db.
 * @param {Object} $args object containing the user's email and password
 */
function checkForDuplicateUser($args){
    global $LINK;
    global $HASHER;
    try {
        $handle = $LINK->prepare('select id,password,full_name,organization, active, activation_hash from user where email=?');
        $handle->bindValue(1, $args);

        $handle->execute();
        $result = $handle->fetchAll(\PDO::FETCH_OBJ);

        session_start();
        $count = count($result);
        if (count($result) >= 1) {
                    //  Duplicate email address exists in the db. Return to the Login page w/ an error message
                    //$_SESSION['login'] = true;
                    //$_SESSION['error_code'] = 3;
                    //  Return to the Login page with an error message
                    //header("Location: ../index.php");
                    return true;
        }
    } catch(PDOException $ex) {
        //print($ex->getMessage());     // Don't print any error message, for security reasons
        //  IF query error, return true so it won't continue to create a possible duplicate user
        return true;
    }
    return false;
}

/**
 * Use the label id stored in the session to pass label XML string to the front-end.
 */
function fetchLabelXML(){
    global $LINK;
    session_start();
    $handle = $LINK->prepare('select label_xml from label where id=?');
    $handle->bindValue(1, $_SESSION['label_id']);
    $handle->execute();

    $result = $handle->fetch(\PDO::FETCH_OBJ);
    header('Content-type: application/xml');

    echo $result->label_xml;
    return $result->label_xml;
}

/**
 * Pass current label ID to the front-end.
 */
function getSessionLabelID(){
    session_start();
    $labelID = $_SESSION['label_id'];
    echo $labelID;
}

/**
 * Replaces special chars. in input data w/ their HTML representations
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
