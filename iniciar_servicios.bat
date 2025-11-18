@echo off
echo ========================================
echo   Iniciando Servicios - Autolote
echo ========================================
echo.

REM Verificar si XAMPP está instalado
if exist "C:\xampp\xampp-control.exe" (
    echo [1/3] Iniciando servicios de XAMPP...
    start "" "C:\xampp\xampp-control.exe"
    echo     Esperando 5 segundos para que los servicios inicien...
    timeout /t 5 /nobreak >nul
) else if exist "C:\wamp64\wampmanager.exe" (
    echo [1/3] Iniciando servicios de WAMP...
    start "" "C:\wamp64\wampmanager.exe"
    echo     Esperando 5 segundos para que los servicios inicien...
    timeout /t 5 /nobreak >nul
) else (
    echo [ADVERTENCIA] No se encontró XAMPP ni WAMP instalado.
    echo     Por favor, inicia Apache y MySQL manualmente.
    pause
    exit /b 1
)

echo.
echo [2/3] Verificando servicios...
timeout /t 3 /nobreak >nul

echo.
echo [3/3] Abriendo el proyecto en el navegador...
timeout /t 2 /nobreak >nul
start http://localhost/Autolote/start.php

echo.
echo ========================================
echo   Servicios iniciados correctamente
echo ========================================
echo.
echo El proyecto debería estar disponible en:
echo   http://localhost/Autolote
echo.
echo Presiona cualquier tecla para cerrar...
pause >nul

