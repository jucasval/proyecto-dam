# Asignación de Horas por Departamentos
## Proyecto Fin de Ciclo — DAM

---

## Descripción
Aplicación web CRUD para gestionar la asignación de horas del Departamento de Informática.
Arquitectura API REST (PHP + MySQL) + Frontend (HTML/CSS/JS).

---

## Estructura del proyecto

```
proyecto/
├── api/
│   ├── config/
│   │   └── database.php          ← Configuración de conexión MySQL
│   ├── controllers/
│   │   ├── ProfesorController.php
│   │   ├── GrupoController.php
│   │   ├── ModuloController.php
│   │   └── AsignacionController.php
│   └── index.php                 ← Router principal de la API
├── frontend/
│   ├── index.html
│   ├── css/
│   ├── js/
│   └── pages/
│       ├── profesores.html
│       ├── grupos.html
│       ├── modulos.html
│       └── asignaciones.html
├── database/
│   └── schema.sql                ← Script SQL completo con datos reales
└── README.md
```

---

## Instalación

### Requisitos
- PHP 8.1+
- MySQL 8.0+
- Apache / Nginx (o `php -S localhost:8000` para desarrollo)

### Pasos

1. **Importar la base de datos**
   ```bash
   mysql -u root -p < database/schema.sql
   ```

2. **Configurar la conexión** en `api/config/database.php`
   ```php
   define('DB_USER', 'tu_usuario');
   define('DB_PASS', 'tu_contraseña');
   ```

3. **Lanzar el servidor** (desarrollo)
   ```bash
   php -S localhost:8000 -t api/
   ```

---

## Endpoints de la API

| Método | URL                       | Acción                        |
|--------|---------------------------|-------------------------------|
| GET    | /api/profesores           | Listar todos los profesores   |
| GET    | /api/profesores/{id}      | Ver un profesor               |
| POST   | /api/profesores           | Crear profesor                |
| PUT    | /api/profesores/{id}      | Editar profesor               |
| DELETE | /api/profesores/{id}      | Eliminar profesor             |
| GET    | /api/grupos               | Listar grupos                 |
| GET    | /api/grupos/{id}          | Ver grupo                     |
| POST   | /api/grupos               | Crear grupo                   |
| PUT    | /api/grupos/{id}          | Editar grupo                  |
| DELETE | /api/grupos/{id}          | Eliminar grupo                |
| GET    | /api/modulos              | Listar módulos                |
| GET    | /api/modulos/{id}         | Ver módulo                    |
| POST   | /api/modulos              | Crear módulo                  |
| PUT    | /api/modulos/{id}         | Editar módulo                 |
| DELETE | /api/modulos/{id}         | Eliminar módulo               |
| GET    | /api/asignaciones         | Listar asignaciones completas |
| GET    | /api/asignaciones/{id}    | Ver asignación                |
| POST   | /api/asignaciones         | Crear asignación              |
| PUT    | /api/asignaciones/{id}    | Editar asignación             |
| DELETE | /api/asignaciones/{id}    | Eliminar asignación           |

---

## Vistas SQL útiles

- `v_asignaciones_completas` — todas las asignaciones con nombres completos
- `v_horas_por_profesor` — resumen de horas asignadas vs contrato por profesor

---

## Tecnologías
- **Backend**: PHP 8.1, PDO
- **Base de datos**: MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Arquitectura**: API REST — preparada para conectar una app Android/iOS futura
