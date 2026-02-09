<?php
header('Access-Control-Allow-Origin: *'); 

require "../config.php";

if($email = $_POST['EmailAddress']){
$sql = "insert INTO unsubscribe (id,customeremail,time) values (NULL,'".$email."','".date("Y-m-d H:i",time())."'); ";
$result = mysqli_query($conn, $sql);

$message = $email." unsubscribed from the Track & Trace service.";
write_mysql_log($message, '1', $conn);

echo "<p class='block-text' style='color:green; font-weight: bold'>Succès. Nous avons exclu votre adresse e-mail (".$email.") de notre liste.</p>

<p>
Gardons le contact.<br /><br />
 
Pour toute information, consultez notre service client.<br />
Tel : 0811 904 692<br />
E-mail :  <a href='mailto:serviceclient@hoya.fr'>serviceclient@hoya.fr</a></p>";  
}
else{
    echo "<p class='block-text' style='color:red; font-weight: bold'>Pas de succès. Nous n'avons pas trouvé votre adresse e-mail dans notre système!</p>

    <p>
        
    Pour toute information, consultez notre service client.<br />
    Tel : 0811 904 692<br />
    E-mail :  <a href='mailto:serviceclient@hoya.fr'>serviceclient@hoya.fr</a></p>";  
    

}
?> 