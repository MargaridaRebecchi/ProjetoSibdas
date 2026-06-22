<?php
session_start();

session_unset();
session_destroy();

header("Location: /SIBDAS_PROJETO_26_MedGest/public/index.php");
exit;