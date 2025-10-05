<?php
$baseUrl='/' . explode('/',trim($_SERVER['SCRIPT_NAME'],'/'))[0];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width,initial-scale=1"/>
        <title><?php echo isset($page_title) ? $page_title . '-Quiz Platform' : 'Quiz Platform';?></title>

    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/custom.css" />
    </head>
</html>