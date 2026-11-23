# DocuFlow AI — Plataforma Web de Gestión Documental Inteligente



**Curso:** Aplicaciones Distribuidas I — Escuela Profesional de Ingeniería de Sistemas e Informática (EPISI)  

**Institución:** Universidad Nacional del Santa (UNS) — Nuevo Chimbote, Perú  

**Proyecto:** Grupo 01 — DocuFlow AI  



---



## 1. Descripción General



**DocuFlow AI** es una aplicación web distribuida desarrollada bajo arquitectura cliente-servidor y patrón **Modelo-Vista-Controlador (MVC)** con **Laravel 11** y **MySQL**. Centraliza el registro, clasificación, almacenamiento digital, búsqueda semántica y consulta conversacional de documentos administrativos.



Integra **Inteligencia Artificial real mediante Google Gemini API** (gemini-3.5-flash-lite) para realizar reconocimiento óptico y comprensión multimodal sobre archivos PDF e imágenes (JPG/PNG), proponiendo metadatos estructurados que el operador puede revisar y corregir antes de su confirmación definitiva. Adicionalmente, incorpora un **Asistente RAG (Retrieval-Augmented Generation)** que responde consultas en lenguaje natural fundamentadas en el repositorio institucional con citas verificables.



---



## 2. Arquitectura Tecnológica



* **Frontend:** HTML5, CSS3, Bootstrap 5.3, FontAwesome 6, JavaScript (Fetch API) y plantillas Blade.

* **Backend:** PHP 8.2+ y Framework Laravel 11 bajo patrón MVC.

* **Base de Datos:** MySQL 8.0+ gestionado mediante Eloquent ORM y migraciones versionadas.

* **Procesamiento Documental e IA:** smalot/pdfparser para extracción local de texto + Google Gemini API (generateContent) para extracción estructurada JSON y respuestas RAG.

* **API REST:** Endpoint JSON en /api/v1/documents para interoperabilidad externa.

* **Seguridad:** Autenticación de sesiones, cifrado de contraseñas con Bcrypt, control de acceso basado en roles (Administrador, Operador), validación en servidor, protección CSRF y gestión de secretos mediante .env.



---



## 3. Requisitos del Sistema



* **Entorno Local:** Laragon 6.0+ (Apache/Nginx, PHP >= 8.2, MySQL >= 8.0) o servidor web equivalente.

* **Gestor de Dependencias:** Composer 2.x.

* **Extensiones PHP habilitadas:** curl, fileinfo, mbstring, openssl, pdo_mysql, tokenizer, xml.

* **Credencial externa:** Clave API gratuita de Google AI Studio (tu_clave_api_de_google_ai_studio).



---



## 4. Guía de Instalación y Despliegue (Laragon)



### Paso 1: Clonar el repositorio

Ubícate en la carpeta raíz web de Laragon (C:\laragon\www\) y clona el proyecto:


git clone https://github.com/ZaidL19/DocuFlow.git

cd DocuFlow




### Paso 2: Instalar dependencias de PHP


composer install




### Paso 3: Configurar variables de entorno

Copia la plantilla .env.example hacia .env y genera la clave de cifrado de Laravel:

copy .env.example .env

php artisan key:generate





Abre el archivo .env y configura tu conexión MySQL y tu clave personal de Google Gemini API:


APP_NAME="DocuFlow AI"

APP_URL=http://docuflow.test



DB_CONNECTION=mysql

DB_HOST=127.0.0.1

DB_PORT=3306

DB_DATABASE=bd_docuflow_ai

DB_USERNAME=root

DB_PASSWORD=1234



SESSION_DRIVER=file

FILESYSTEM_DISK=public



GEMINI_API_KEY= tu_clave_api_de_google_ai_studio

GEMINI_MODEL=gemini-3.5-flash-lite




### Paso 4: Crear la base de datos y ejecutar migraciones

1. Crea una base de datos vacía llamada bd_docuflow_ai en MySQL (mediante HeidiSQL o phpMyAdmin) con cotejamiento utf8mb4_unicode_ci.

2. Ejecuta las migraciones y los datos iniciales (*seeders*):


php artisan migrate:fresh --seed




### Paso 5: Habilitar almacenamiento público de archivos

Crea el enlace simbólico para que los PDFs e imágenes cargados sean accesibles desde el visor web:


php artisan storage:link




### Paso 6: Iniciar el servidor

* **Con Virtual Host de Laragon:** Inicia **Start All** en Laragon y accede desde el navegador a https://zaidl19.github.io/DocuFlow/.

* **Con servidor integrado de Artisan:** Ejecuta php artisan serve y accede a http://127.0.0.1:8000.



---



## 5. Credenciales de Demostración



El seeder (database/seeders/DatabaseSeeder.php) registra automáticamente los siguientes usuarios de prueba con contraseñas cifradas mediante Bcrypt:



| Rol | Correo Institucional | Contraseña | Permisos Principales |

| :--- | :--- | :--- | :--- |

| **Administrador** | admin@docuflow.com | Admin123* | Acceso total, carga con IA, carga manual, revisión, RAG, auditoría y eliminación de documentos. |

| **Operador** | operador@docuflow.com | Operador123* | Carga con IA, carga manual, validación de metadatos, consultas al asistente RAG y auditoría. |



---



## 6. Módulos Principales y Endpoints



### Rutas Web (routes/web.php)

* GET /login — Autenticación de usuarios.

* GET / — Dashboard ejecutivo con métricas operativas y rendimiento de IA (latencia en ms y precisión *Exact Match*).

* `GET /documents/create` — Registro dual de documentos: **Carga con IA (OCR + LLM)** y **Carga Manual** (línea base).

* `GET /documents/{id}/review` — Pantalla dividida de validación humana (visor del documento original + metadatos extraídos editables).

* `GET /documents` — Repositorio central con búsqueda por código, asunto, remitente, tipo, estado y contenido OCR.

* `GET /rag-assistant` — Asistente conversacional RAG fundamentado exclusivamente en documentos validados con citas de fuente [CÓDIGO].

* `GET /audit` — Trazas experimentales de extracción IA (ai_extraction_logs) e historial de auditoría del sistema (audit_logs).



### API REST (routes/api.php)

* GET /api/v1/documents — Devuelve en formato JSON el listado estructurado de documentos validados y sus categorías.



---



## 7. Pruebas Automatizadas y Carga Experimental



* **Ejecutar pruebas funcionales PHPUnit:**


  php artisan test


* **Procesamiento experimental por lote (Batch IA):**

  Coloca documentos PDF/JPG/PNG en storage/app/lote_50/ y ejecuta:


  php artisan docs:import-batch


  Este comando procesa cada documento contra la API real de Gemini y registra la respuesta cruda, los metadatos validados y el tiempo de ejecución en milisegundos en la tabla ai_extraction_logs.



---



## 8. Estructura del Proyecto




DocuFlow/

├── app/

│   ├── Console/Commands/    # Comando Artisan para procesamiento experimental por lote

│   ├── Http/

│   │   ├── Controllers/     # Controladores MVC (Auth, Dashboard, Document, RagSearch, Audit)

│   │   └── Middleware/      # Control de acceso por roles (CheckRole)

│   ├── Models/              # Modelos Eloquent (User, Document, DocumentCategory, AiExtractionLog, AuditLog)

│   └── Services/            # AiDocumentService (integración real con Google Gemini API y PDFParser)

├── config/                  # Configuración de base de datos, almacenamiento y servicios externos

├── database/

│   ├── migrations/          # Esquema relacional MySQL ejecutado por Artisan

│   └── seeders/             # Usuarios demo y categorías documentales iniciales

├── resources/views/         # Vistas Blade (layouts, auth, dashboard, documents, rag, audit)

├── routes/                  # Definición de rutas web.php y api.php

├── tests/Feature/           # Pruebas automatizadas de flujos y seguridad

├── .env.example             # Plantilla de variables de entorno (sin secretos)

└── README.md                # Documentación técnica de instalación y uso