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
 * @file Contains the backend code to download the XML file using the filename
 * specified by the user on the frontend.
 *
 * Creation Date: 8/1/16
 *
 * @author Trevor Morse
 * @author Michael Kim
 * @author Stirling Algermissen
 */
require "../thirdparty/php/PHPMailerAutoload.php";
require_once("interact_db.php");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $filename = "Sample_Label.xml";

    $tmp_file = tempnam(sys_get_temp_dir(), 'Sample_Label');
    $handle = fopen($tmp_file, "w");
    fwrite($handle, getLabelXML());
    fclose($handle);


    $mail = new PHPMailer;
    $mail->isSendmail();
    $mail->setFrom($_SESSION['email'], $_SESSION['full_name']);
    $mail->Subject = "PLAID generated PDS4 label from " . $_SESSION['full_name'];
    $mail->addAddress($_POST["pds_node_email"]);
    $mail->addCC($_SESSION['email']);
    $mail->Body ="
    Hi " . $_POST['pds_node_rep_name'] . ",\n
    PLAID user " . $_SESSION['full_name']  . " has generated a PDS4 label for your review. Please see attached. User message was:
    
    '" . $_POST['comments'] . "'
    
    Thanks,
    PLAID
    ";

    $mail->addAttachment($tmp_file, "label_template.xml");

    if (!$mail->send()) {
        echo 1;
    } else {
        echo 0;
    }
}
/**
 * Helper function to remove bad input from the data.
 * @param {string} $data
 * @return {string}
 */
function handleData($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}