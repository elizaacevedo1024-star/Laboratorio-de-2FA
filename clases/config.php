<?php
// Configuración central de la BD. NUNCA usar root: usuarios con privilegios mínimos (ver sql/database.sql)
return [
    'host' => 'localhost',
    'name' => 'company_info',
    // Usuario que usa la aplicación (SELECT, INSERT, UPDATE)
    'user' => 'app_login',
    'pass' => 'Cambiar_Esta_Clave_1!',
    // Usuario de solo lectura (SELECT)
    'ro_user' => 'app_lectura',
    'ro_pass' => 'Cambiar_Esta_Clave_2!',
];
