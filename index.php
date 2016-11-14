<?php

// make sure browsers see this page as utf-8 encoded HTML
header('Content-Type: text/html; charset=utf-8');

$limit = 10;
$query = isset($_REQUEST['q']) ? $_REQUEST['q'] : false;
$results = false;

if ($query)
{
  // The Apache Solr Client library should be on the include path
  // which is usually most easily accomplished by placing in the
  // same directory as this script ( . or current directory is a default
  // php include path entry in the php.ini)
  require_once('Apache/Solr/Service.php');

  // create a new solr service instance - host, port, and webapp
  // path (all defaults in this example)
  $solr = new Apache_Solr_Service('localhost', 8983, '/solr/newcore');

  // if magic quotes is enabled then stripslashes will be needed
  if (get_magic_quotes_gpc() == 1)
  {
    $query = stripslashes($query);
  }
	$parameter=[];
if(array_key_exists("pagerank",$_REQUEST)){
$parameter['sort']="pageRankFile desc";
}
  // in production code you'll always want to use a try /catch for any
  // possible exceptions emitted  by searching (i.e. connection
  // problems or a query parsing error)
  try
  {

    $results = $solr->search($query, 0, $limit, $parameter);
  }
  catch (Exception $e)
  {
    // in production you'd probably log or email this error to an admin
    // and then show a special message to the user but for this example
    // we're going to show the full exception
    die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
  }
}

?>
<html>
  <head>
    <title>PHP Solr Client Example</title>
  </head>
  <body>
    <form  accept-charset="utf-8" method="get">
      <label for="q">Search:</label>
      <input id="q" name="q" type="text" value="<?php echo htmlspecialchars($query, ENT_QUOTES, 'utf-8'); ?>"/>
      <input type="submit"/>
<br>
<input type="checkbox" name="pagerank"/ > Using PageRank 
    </form>
<?php
$csv=array();
$file=fopen("mergedMappingFile.csv","r");
while(!feof($file))
{
$line=fgets($file);
$temp=explode(',',$line);
$csv[$temp[0]]=$temp[1];
}
fclose($file);

// display results
if ($results)
{
  $total = (int) $results->response->numFound;
  $start = min(1, $total);
  $end = min($limit, $total);
?>
    <div>Results <?php echo $start; ?> - <?php echo $end;?> of <?php echo $total; ?>:</div>
    <ol>
<?php
  // iterate result documents
  foreach ($results->response->docs as $doc)
  {
?>
  <?php
$id =substr($doc->id, 34);

$title=$doc->title;
if($title==null)
{
$title="NA";
}
$url=$csv[$id];
//$url=$doc->og_url;
$description=$doc->description;
if($description==null)
{
$description="NA";
}
?>
<li> 
<p> <b>Title      :</b><a href="<?php echo $url;?>"target="_blank"><?php echo $title;?></a> </p>
<p> <b>URL        :</b><a href="<?php echo $url;?>"target="_blank"><?php echo $url;?></a> </p>
<p> <b>ID         :</b><?php echo $id;?> </p>
<p> <b>Description:</b><?php echo $description;?> </p>
<hr>

</li>  
<?php
  }
?>
    </ol>
<?php
}
?>
  </body>
</html>
