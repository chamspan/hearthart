<?
    include($_SERVER['DOCUMENT_ROOT']."/wp-load.php");

 	//mysql_connect(DB_HOST , DB_USER , DB_PASSWORD);
 	//mysql_select_db(DB_NAME);

	$res = 'Null';
    if($_POST['id'])
    {    	$query = mysql_query("select post_content from wp_posts where ID = ".$_POST['id']);
    	if(mysql_num_rows($query) == 0)
    	{    		$res = 'error';
    	}
    	else
    	{
    		$row = mysql_fetch_object($query);

    		$res = $row->post_content;

    	}

    }

    echo $res;


?>