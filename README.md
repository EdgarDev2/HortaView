<p align="center">
    <a target="_blank">
        <img src="https://cdn.pixabay.com/photo/2017/01/16/23/31/icon-1985550_640.png" height="210px" width="210">
    </a>
    <h1 align="center">Monitoreo Web en Tiempo Real de Hortalizas</h1>
    <br>
</p>

## Descripción del Proyecto

El proyecto "Monitoreo Web en Tiempo Real de Hortalizas" tiene como objetivo monitorear las variables ambientales cruciales para el cultivo de hortalizas para proporcionar al cliente datos en tiempo real, gráficos predictivos, gráficos de comparación y más.
Este sistema permitirá optimizar la producción agrícola y controlar la distribución del agua en función a la humedad del suelo.

## Institución

**Escuela**: Instituto Tecnológico Superior de Valladolid  
**Ciudad**: Valladolid, Yucatán.

## Asesor del Proyecto

**Asesor interno**: Dr. Jesús Antonio Santos Tejero.  
**Asesor Externo**: Dr. Rusell Renan Iuit
Manzanero.

## Estudiantes Participantes

- Ing. Edgar Poot Ku.

## Tecnologías Utilizadas

- **Lenguajes de programación**: PHP, JS
- **Frameworks**: YII2
- **Base de datos**: MySQL

## Requisitos

- Tener Instalado [WampServer](https://wampserver.aviatechno.net/).
- Tener instalado [Composer](https://getcomposer.org/download/).
- Tener Instalado [Git](https://git-scm.com/downloads/win).

## Instrucciones de instalación

1. Iniciar el entorno de desarrollo web WampServer desde la cmd
   ```bash
    start C:\wamp64\wampmanager.exe
   ```
2. Clonar el repositorio en "C:\wamp64\www":
   ```bash
   git clone https://github.com/EdgarDev2/HortaView.git
   ```
3. Cambiar de directorio al proyecto e inicializarlo seleccionando 0
   ```bash
   cd HortaView && php init
   ```
4. Instalar las dependencias
   ```sql
   composer install
   ```
5. Configurar el nombre de la BD a "sistemariego" en common/main-local.php en la linea:
   ```bash
   'dsn' => 'mysql:host=localhost;dbname=sistemariego',
   ```
6. Configurar el charset agregando utf8 en la linea:
   ```sql
   'charset' => 'utf8',
   ```
7. Crear la base de datos con MySQL console de WampServer
   ```sql
   CREATE DATABASE sistemariego CHARACTER SET utf8mb4 COLLATE utf8_unicode_ci;
   ```
8. Selecciona la base de datos
   ```sql
   USE sistemariego;
   ```
9. Importar el script SQL a la base de datos
   ```sql
   SOURCE C:/wamp64/www/HortaView/databaseScript.sql;
   ```
10. salir de la BD y listo
    ```sql
    exit
    ```

## uso

1. Acceder a la aplicación en el navegador
   ```
   http://localhost/HortaView/frontend/web/
   ```
