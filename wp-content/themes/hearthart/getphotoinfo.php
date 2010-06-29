<?

    include($_SERVER['DOCUMENT_ROOT']."/wp-load.php");

 	//mysql_connect(DB_HOST , DB_USER , DB_PASSWORD);
 	//mysql_select_db(DB_NAME);


    if($_POST['id'])
    {
    	$res = 'Null';
    	$query = mysql_query("select * from wp_ngg_pictures where galleryid = 1 and exclude = 0 and pid = ".$_POST['id']);
    	if(mysql_num_rows($query) == 0)
    	{
    		$res = 'Error: wrong pic id';
    	}
    	else
    	{

    		$row = mysql_fetch_object($query);

    		$res = $row->description;

    	}

   		echo $res;
    }


    if($_POST['g'])
    {

       $query = mysql_query("select count(pid) as count from wp_ngg_pictures where galleryid = 1 and exclude = 0");
       $p = mysql_fetch_object($query);
       echo $p->count;

    }

?>