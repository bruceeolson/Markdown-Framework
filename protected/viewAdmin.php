<?php 
require_once('adminApp.php'); 

if ( mdsAdmin::app()->action === FALSE ) header("Location: ".mdsAdmin::app()->mdsBaseUrl);
elseif ( mdsAdmin::app()->success ) header("Location: ".mdsAdmin::app()->libraryUrl);
else $form = mdsAdmin::app()->form;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?php echo $form->formTitle;?></title>
</head>

<body>

<h1><?php echo $form->formTitle;?></h1>

<pre>
<?php 
//print_r($_SERVER); 
//print_r(mdsAdmin::app());
?>
</pre>

<form method="post">

	<div class="errorMessage"><?php echo $form->messages();?></div>
    
    <input type="hidden" name="Register[id]" value="<?php echo $form->id;?>" />
    
	<table>
    	<tr>
        	<td style="text-align:right;"><label style="text-align:right;">Alias <br/>(single alphanumeric word)</label></td>
        	<td><input type="text" name="Register[alias]" size="50" value="<?php echo $form->alias;?>"/></td>
        </tr>
    
		<tr>
        	<td style="text-align:right;"><label>Description</label></td>
        	<td><input type="text" name="Register[title]" size="50" value="<?php echo $form->title;?>" /></td>
    	</tr>
    
		<tr>
        	<td style="text-align:right;"><label>Owner</label></td>
        	<td><input type="text" name="Register[owner]" size="50" value="<?php echo $form->owner;?>" /></td>
    	</tr>
    
		<tr>
        	<td style="text-align:right;"><label>Url to library folder</label></td>
        	<td><input type="text" name="Register[url]" size="50" value="<?php echo $form->url;?>"/></td>
        </tr>
    </table>
    
    <br/>
    <input type="submit" value="Submit" />&nbsp;&nbsp;
    <input type="button" onclick="window.location.href='../'" value="Cancel" />
    
</form>
</body>
</html>