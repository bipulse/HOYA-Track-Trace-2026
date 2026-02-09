<?php
  require_once "../../config.php";
  // Create an image, 1x1 pixel in size
  $im=imagecreate(1,1);

  // Set the background colour
  $white=imagecolorallocate($im,255,255,255);

  // Allocate the background colour
  imagesetpixel($im,1,1,$white);

  // Set the image type
  header("content-type:image/jpg");

  header('Pragma: public');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private', false);

header('Content-Transfer-Encoding: binary');

  // Create a JPEG file from the image
  imagejpeg($im);

  // Free memory associated with the image
  imagedestroy($im);


  $ip = $_SERVER['REMOTE_ADDR'];
  $referer = $_SERVER['HTTP_REFERER'];
  $useragent = $_SERVER['HTTP_USER_AGENT'];
  $browser = get_browser(null, true);
  $time = $_GET['t'];
  $customer = $_GET['cid'];
  $message = "Email opened at $time by $customer";
  #write_mysql_log($message, '4', $conn);

  // Construct query
  $sql = "INSERT INTO mail_statistics (customer , mail_date,ip,referrer,useragent,browser) VALUES('$customer','".date("Y-m-d",$time)."','$ip','$referer','$useragent','$browser')";
 
  // Execute query and save data
  $result = $conn->query($sql);
 
  if($result) {
    return array('status' => true);  
  }
  else {
    return array('status' => false, message => 'Unable to write to the database');
  }

?>