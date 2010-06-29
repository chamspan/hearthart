<?

    include($_SERVER['DOCUMENT_ROOT']."/wp-load.php");

 	//mysql_connect(DB_HOST , DB_USER , DB_PASSWORD);
 	//mysql_select_db(DB_NAME);

	$res = 'Null';
    if($_POST['text'])
    {
	    	if(mysql_query("INSERT INTO `496248_heart`.`wp_dsfaq_quest` (`id` ,`id_book` ,`date` ,`quest`)VALUES (NULL , '2', NOW(), '".$_POST['text']."')"))
	    	{
	    		$res = "Your question has been successfully sent!";
	    	}
	    	else
	    	{
	            $res = "Error while question sending. Please try again later.";

	    	}
    }
    else
    {        if($_POST['text'] == '')
        {
        	$res = "Please fill your question first!";
        }

    }
    echo $res;


?>