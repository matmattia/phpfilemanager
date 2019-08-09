<!DOCTYPE html>
<html lang="<?php echo html(__('lang_code'));?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
<title>Filemanager</title>
<link rel="stylesheet" type="text/css" href="css/style.css" />
<link rel="stylesheet" type="text/css" href="https://use.fontawesome.com/releases/v5.10.1/css/all.css" />
<script type="text/javascript" src="js/scripts.js"></script>
<script type="text/javascript">
phpfilebrowser_lang.texts = <?php echo json_encode($lang_values);?>;
</script>
</head>
<body><?php echo $body;?></body>
</html>