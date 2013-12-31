<?php 
require_once('../protected/registerApp.php'); 

if ( mdsRegister::app()->success ) header("Location: ".mdsRegister::app()->libraryUrl);
else $form = mdsRegister::app()->form;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>MDS Register</title>
</head>

<body>

<h1>Register a MDS Library</h1>

<form method="post">

	<div class="errorMessage"><?php echo $form->messages();?></div>

	<table>
    	<tr>
        	<td><label>Alias (single alphanumeric word with no special chars)</label></td>
        	<td><input type="text" name="Register[alias]" size="50" value="<?php echo $form->alias;?>"/></td>
        </tr>
    
		<tr>
        	<td><label>Description</label></td>
        	<td><input type="text" name="Register[title]" size="50" value="<?php echo $form->title;?>" /></td>
    	</tr>
    
		<tr>
        	<td><label>Url</label></td>
        	<td><input type="text" name="Register[url]" size="50" value="<?php echo $form->url;?>"/></td>
        </tr>
    </table>
    
    <br/>
    <input type="submit" value="Submit" />&nbsp;&nbsp;
    <input type="button" onclick="window.location.href='../'" value="Cancel" />
    
</form>
</body>
</html>