<?php
$db = new PDO('mysql:host=localhost;dbname=hvt_petshop', 'root', ''); 
$db->exec("ALTER TABLE tutores ADD COLUMN criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER observacoes;");
echo "Column added";
