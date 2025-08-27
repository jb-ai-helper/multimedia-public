<?php
$seed = getenv('TRANSFERT_SECRET_SEED');

echo $seed
     ? "CONST OK<br>Date key: " . getDailyKey()
     : "CONST ABSENTE";
