# Importar usando Railway CLI

## Paso 1: Instalar Railway CLI

En PowerShell:
```powershell
iwr https://railway.app/install.ps1 | iex
```

O descarga desde: https://railway.app/cli

## Paso 2: Iniciar sesión
```bash
railway login
```

## Paso 3: Conectar a tu proyecto
```bash
railway link
# Selecciona tu proyecto "melodious-smile"
```

## Paso 4: Importar el SQL
```bash
railway connect MySQL < database_railway.sql
```

Esto importará todas las tablas automáticamente.

