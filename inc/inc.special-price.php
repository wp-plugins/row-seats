

<style type="text/css">
input[type=submit] {
    border-radius: 5px;
    border: 0;
    width: 80px;
    height:25px;
    font-family: Tahoma;
    background: #f4f4f4;
    /* Old browsers */
    background: -moz-linear-gradient(top, #f4f4f4 1%, #ededed 100%);
    /* FF3.6+ */
    background: -webkit-gradient(linear, left top, left bottom, color-stop(1%, #f4f4f4), color-stop(100%, #ededed));
    /* Chrome,Safari4+ */
    background: -webkit-linear-gradient(top, #f4f4f4 1%, #ededed 100%);
    /* Chrome10+,Safari5.1+ */
    background: -o-linear-gradient(top, #f4f4f4 1%, #ededed 100%);
    /* Opera 11.10+ */
    background: -ms-linear-gradient(top, #f4f4f4 1%, #ededed 100%);
    /* IE10+ */
    background: linear-gradient(to bottom, #f4f4f4 1%, #ededed 100%);
    /* W3C */
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#f4f4f4', endColorstr='#ededed', GradientType=0);
    /* IE6-9 */
}

input[type=button] {
    border-radius: 5px;
    border: 0;
    width: 80px;
    height:25px;
    font-family: Tahoma;
    background: #f4f4f4;
    /* Old browsers */
    background: -moz-linear-gradient(top, #f4f4f4 1%, #ededed 100%);
    /* FF3.6+ */
    background: -webkit-gradient(linear, left top, left bottom, color-stop(1%, #f4f4f4), color-stop(100%, #ededed));
    /* Chrome,Safari4+ */
    background: -webkit-linear-gradient(top, #f4f4f4 1%, #ededed 100%);
    /* Chrome10+,Safari5.1+ */
    background: -o-linear-gradient(top, #f4f4f4 1%, #ededed 100%);
    /* Opera 11.10+ */
    background: -ms-linear-gradient(top, #f4f4f4 1%, #ededed 100%);
    /* IE10+ */
    background: linear-gradient(to bottom, #f4f4f4 1%, #ededed 100%);
    /* W3C */
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#f4f4f4', endColorstr='#ededed', GradientType=0);
    /* IE6-9 */
}


</style>
<?php 
error_reporting(0);
if(isset($_POST['add']))
{
	for($m=1;$m<=$_POST['hidnum'];$m++)
{
	if($_POST['category'.$m]!="" && $_POST['price'.$m]!="")
{
	$sql=mysql_query("insert into rst_specialprice(name,price,show_id)values('".$_POST['category'.$m]."','".$_POST['price'.$m]."','".$_POST['showname']."')");
	
	
	}

	}


	header('location:admin.php?page=rst-special-price');
}



?>





<div style="width:950px">

<form method="post" action="<?php echo($_SERVER['PHP_SELF'].'?page=rst-special-price'); ?>">
<table cellspacing="0" class="widefat fixed">

<?php
if(isset($_REQUEST['delete']))
{
	//echo $_REQUEST['delete'];
$sqdl=mysql_query("delete from rst_specialprice where s_id='".($_REQUEST['delete'])."'" );
	
}
	
	?>



<?php
if(isset($_REQUEST['upd']))
{
	
	
	$sql=mysql_query("update  rst_specialprice  set   name='".$_POST['nam']."' ,price='".$_POST['price']."' where s_id='".$_POST['hi']."' " );
	
}?>



<?php
if(isset($_REQUEST['edit']))
{?>





<tr><td>Category</td><td>Price</td></tr>

<?php

$sql=mysql_query('select * from rst_specialprice where s_id='.$_POST['edit'].'');
$ads=mysql_fetch_array($sql);


?>
<tr><td><input type="text" name="nam" value="<?php echo $ads['name'];  ?>" /></td><td><input type="text" name="price" value="<?php echo $ads['price'];  ?>" /></td>
<?php  ?>
<tr><td colspan="2"></td><td><input type="hidden" name="hi" value="<?php echo $ads['s_id'];  ?>" /></td><td><input type="submit" name="upd" value="Update" ></td>
<td><a href="<?php echo($_SERVER['PHP_SELF'].'?page=rst-special-price'); ?>"><input type="button" value="Cancel" /></a></td></tr>


<?php } ?>


<?php if($_REQUEST['num']=='0' && !isset($_POST['edit'])) 
{?>
<tr><td>Number of Types you need to add</td><td><input type="text" name="num"></td><td>Maximum 6 at a time</td></tr>
<tr><td><input type="submit" name="nosub" value="Enter">

</td></tr>
<?php } ?>




<?php if($_REQUEST['num']=='' && !isset($_POST['edit'])) 
{?>
<tr><td>Number of Types you need to add</td><td><input type="text" name="num"></td><td>Maximum 6 at a time</td></tr>
<tr><td><input type="submit" name="nosub" value="Enter">

</td></tr>
<?php } ?>

<?php if($_REQUEST['num']!='0' && $_REQUEST['num']<7 &&isset($_REQUEST['nosub']) ) 
{?><tr><td>You are going to add <?php echo $_REQUEST['num']; ?> Fields .</td></tr>



<tr><td>Select Show</td><td><select name="showname" ><option value="0">==========</option>
<?php
$rty=mysql_query('select * from rst_shows');
while($rt=mysql_fetch_array($rty))
{
?>


<option value="<?php echo $rt['id']; ?>">
<?php echo $rt['show_name']; ?> 

</option>

<?php } ?>
</select></td></tr>
<?php 

for($i=1;$i<=$_REQUEST['num'];$i++)
{ ?>
<tr><td><input type="hidden" name="hidnum" value="<?php echo $_REQUEST['num']; ?>"
<tr><td>Category <?php echo $i; ?> </td><td><input type="text" name="category<?php echo $i; ?>" ></td><td>Price <?php echo $i; ?> </td><td><input type="text" name="price<?php echo $i; ?>" ></td></tr> <?php echo '<br>' ;?>





<?php
}

?>
<tr><td colspan="3"></td><td><input type="submit" name="add" value="Add" ></td>
<td><a href="<?php echo($_SERVER['PHP_SELF'].'?page=rst-special-price'); ?>"><input type="button" value="Cancel" /></a></td></tr>







<?php } ?>

</table>






</div>
<div style="height:10px"></div>

<div style="clear: both;;"></div>

<?php echo "  <h3>" . __('List of Special Price:', 'rst') . "</h3>"; ?>
<table cellspacing="0" class="widefat fixed">
<thead>

<tr class="thead">
 <th class="column-username" id="username" scope="col"
                style="width: 150px;"><?php echo 'Show Name'; ?></th>
                <th class="column-name" id="name"
                scope="col"><?php echo 'Cateogory'; ?></th>
          
          <th class="column-name" id="name"
                scope="col"><?php echo 'Price'; ?></th>
                
          <th class="column-name" scope="col"><?php echo 'Edit'; ?></th>      
                 <th class="column-name" scope="col"><?php echo 'Delete'; ?></th> 
 </tr>
        </thead>
<?php

$ssql=mysql_query('select *from rst_specialprice');
while($adf=mysql_fetch_array($ssql))
{
	$shown=mysql_query("select * from rst_shows  where id='".$adf['show_id']."'");
$snm=mysql_fetch_array($shown);
 
?>
 <tbody class="list:user user-list" id="users">
<tr class="alternate" id="user-1">
                <td class="username column-username" style="width: 150px;">

<?php echo($snm['show_name']);?></td><td class="username column-username" style="width: 100px;"><?php echo($adf['name']);?></td><td class="username column-username" style="width: 100px;"><?php echo($adf['price']);?></td><td><button name="edit" value="<?php echo($adf['s_id']);?>"><img src="<?php echo esc_url( admin_url( 'images/generic.png' ) ); ?>" /></button></td><td><button  name="delete" value="<?php echo($adf['s_id']);?>"><img src="<?php echo esc_url( admin_url( 'images/no.png' ) ); ?>" /></button></td>
 </tr></tbody>
<?php }
?>
<thead>

<tr class="thead">
 <th class="column-username" id="username" scope="col"
                style="width: 150px;"><?php echo 'Show Name'; ?></th>
                <th class="column-name" id="name"
                scope="col"><?php echo 'Cateogory'; ?></th>
          
          <th class="column-name" id="name"
                scope="col"><?php echo 'Price'; ?></th>
                
          <th class="column-name" scope="col"><?php echo 'Edit'; ?></th>      
                 <th class="column-name" scope="col"><?php echo 'Delete'; ?></th> 
 </tr>
        </thead>

</table>

</form>