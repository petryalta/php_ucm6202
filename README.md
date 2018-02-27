# php_ucm6202
PHP class for CDR api Grandstream UCM6202

Example use:

<?php
 $PBX = new Ucm6202('https://192.168.1.89', 'cdrapi', 'cdrapi123', '8443');
 $params = [
  'startTime' => '2018-03-01T09:00:00',
  'endTime' => '2018-03-02T23:59:59',
  'numRecords' => 100
 ];
 $data = $PBX->find($params);
 var_dump($data);
?>
