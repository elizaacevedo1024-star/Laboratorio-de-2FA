# Parcial Práctico 2FA – Desarrollo Web

**Universidad Tecnológica de Panamá – Facultad de Ingeniería en Sistemas**
Período: II Semestre 2026 · Asignatura: Desarrollo Web

**Estudiantes:** Elizabeth Acevedo y Ambar Greco  
**Grupo:** 1S3122  
**Fecha:** 24 de noviembre de 2026  
**Contacto:** elizabeth.acevedo@utp.ac.pa

## Descripción
Sistema de **registro e inicio de sesión con autenticación de dos factores (2FA)** hecho en PHP + MySQL.
Al registrarse, el usuario recibe un **código QR** que escanea con Google Authenticator; al iniciar sesión, después de la contraseña, se le solicita el código de 6 dígitos antes de entrar al sistema.

## Lenguajes y herramientas utilizadas

**Lenguajes**
- PHP 8 (programación orientada a objetos, PDO)
- JavaScript (validaciones en el frontend y consultas AJAX con `fetch`)
- SQL (MySQL)
- HTML5 y CSS3

**Herramientas y librerías**
| Herramienta | Uso |
|---|---|
| WampServer | Servidor local (Apache, PHP y MySQL) |
| phpMyAdmin | Administración de la base de datos y creación de usuarios con privilegios mínimos |
| Visual Studio Code | Editor de código |
| Composer | Gestor de dependencias de PHP |
| `sonata-project/google-authenticator` | Generación y verificación de códigos TOTP (2FA) |
| TCPDF | Generación local del código QR |
| Google Authenticator | Aplicación móvil para escanear el QR y generar los códigos |
| Git y GitHub | Control de versiones y repositorio |

## Instalación
1. Copiar la carpeta del proyecto a `C:\wamp64\www\`.
2. Importar `sql/database.sql` en phpMyAdmin (crea la base `company_info`, las tablas `usuarios` e `intentos_login` y los usuarios de BD con privilegios mínimos).
3. Verificar que las contraseñas de `clases/config.php` coincidan con las del SQL.
4. Abrir `http://localhost/<carpeta>/registro.php`.

## Estructura principal
| Archivo | Función |
|---|---|
| `registro.php` | Formulario de registro con validaciones |
| `verificar_usuario.php` | Validación AJAX de usuario/correo duplicado |
| `clases/RegistroUsuario.php` | Clase del formulario de registro |
| `clases/SanitizarEntrada.php` | Métodos estáticos de sanitización |
| `clases/IHash.php`, `clases/HashBcrypt.php` | Interfaz y clase para generar/validar hash |
| `clases/Csrf.php` | Protección Anti-CSRF |
| `activar_2fa.php` | Muestra el QR y confirma el código |
| `verificar_2fa.php` | Solicita el código 2FA tras el login |
| `privilegios.php` | Muestra los privilegios de los usuarios de BD |
| `sql/database.sql` | Estructura de BD y usuarios con privilegios mínimos |
| `css/auth.css` | Estilos de login, registro y 2FA |

---

# Evidencias según la rúbrica

## 1. Usuarios con privilegios mínimos (15 pts)
No se utiliza el súper usuario `root`. Se crearon `app_login` (SELECT, INSERT, UPDATE) y `app_lectura` (solo SELECT). Comando para ver los privilegios: `SHOW GRANTS FOR 'app_login'@'localhost';`

Código de la pantalla que ejecuta `SHOW GRANTS`:
![Código de privilegios.php](img/01_privilegios_codigo.png)

Pantalla con los privilegios concedidos:
![Privilegios en pantalla](img/16_privilegios_pantalla.png)

## 2. Formulario de registro con validaciones básicas (5 pts)
Campos requeridos, usuario mínimo de 4 caracteres, correo válido, contraseña mínima de 8 caracteres y confirmación de contraseña.
![Validaciones del registro](img/02_registro_validaciones.png)

## 3. Validación de correo y usuario duplicados (5 pts)
Se valida en el frontend, en tiempo real (AJAX): en la captura anterior el correo ya existente aparece como *«Ya está registrado, elige otro»*; con datos nuevos aparece *«Disponible»*.
![Usuario y correo disponibles](img/05_registro_disponible.png)

## 4. Clase para el formulario de registro (10 pts)
`RegistroUsuario` con métodos de una sola responsabilidad: `sanitizar()`, `validarCampos()`, `validarDuplicados()`, `guardar()` y `registrar()`.
![Clase RegistroUsuario](img/03_clase_registro.png)

## 5. Sanitización de datos (10 pts)
Clase `SanitizarEntrada` con 7 métodos estáticos: `limpiarCadena`, `limpiarNombre`, `limpiarEmail`, `limpiarUsuario`, `limpiarEntero`, `escaparHtml` y `esEmailValido`.
![Clase SanitizarEntrada](img/04_sanitizar_entrada.png)

## 6. Generación del código QR (5 pts)
El QR se genera de forma local (TCPDF) justo después de crear el registro; el secreto no sale del servidor.
![Código QR](img/06_codigo_qr.png)

## 7. Inicio de sesión y solicitud del código antes de entrar (5 pts)
Tras activar el 2FA el usuario inicia sesión con su contraseña…
![Login](img/07_login_2fa_activado.png)

…y antes de entrar se le solicita el código de Google Authenticator:
![Verificación en dos pasos](img/08_verificacion_2fa.png)

## 8. Otra sesión que confirma que la fase del código fue exitosa (5 pts)
Con el código correcto se regenera el ID de sesión (`session_regenerate_id`) y se crea `$_SESSION['2fa_verificado']`; el panel exige esa variable para permitir el acceso.
![Panel de control](img/09_panel_control.png)
![Cookie de sesión PHPSESSID](img/10_cookie_sesion.png)

## 9. Interfaz para generar y validar el hash – POO (5 pts)
Interfaz `IHash` implementada por la clase `HashBcrypt`.
![Interfaz IHash](img/11_interfaz_ihash.png)
![Clase HashBcrypt](img/12_hash_bcrypt.png)

## 10. Protección Anti-CSRF (10 pts)
Clase `Csrf`: genera un token aleatorio (`random_bytes`) guardado en sesión, lo coloca en un campo oculto de cada formulario y lo compara con `hash_equals()` al recibir el POST. Se aplica en login, registro, activación y verificación 2FA.
![Clase Csrf](img/14_clase_csrf.png)

## 11. Tablas que registran datos (10 pts – 5 por tabla)
Tabla `usuarios` (con hash bcrypt y secreto 2FA):
![Tabla usuarios](img/13_tabla_usuarios.png)

Tabla `intentos_login`:
![Tabla intentos_login](img/15_tabla_intentos_login.png)

## 12. Calidad del CSS y valor agregado (10 pts)
Diseño unificado en login, registro, QR y verificación (`css/auth.css`), con diseño adaptable a móvil, medidor de fuerza de contraseña, mensajes de validación en tiempo real y retroalimentación de errores (ver capturas 02, 05, 06, 07 y 08).
