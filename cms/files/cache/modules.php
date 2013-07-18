<?
$updated=1270022935;
include_once(_MODULES_ABS_."/content/index.php");
$this->modules["content"] = new content;
include_once(_MODULES_ABS_."/subscribe/index.php");
$this->modules["subscribe"] = new subscribe;
include_once(_MODULES_ABS_."/excel_parser/index.php");
$this->modules["excel_parser"] = new excel_parser;
?>