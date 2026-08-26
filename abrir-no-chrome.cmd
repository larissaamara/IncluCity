@echo off
setlocal
cd /d "%~dp0"

set "PHP_EXECUTAVEL=php"
if exist "C:\xampp\php\php.exe" set "PHP_EXECUTAVEL=C:\xampp\php\php.exe"

start "Servidor IncluCity" /min "%PHP_EXECUTAVEL%" -S localhost:8000 -t "%~dp0"
timeout /t 2 /nobreak >nul

if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" (
  start "" "%ProgramFiles%\Google\Chrome\Application\chrome.exe" "http://localhost:8000"
) else if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" (
  start "" "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" "http://localhost:8000"
) else if exist "%LocalAppData%\Google\Chrome\Application\chrome.exe" (
  start "" "%LocalAppData%\Google\Chrome\Application\chrome.exe" "http://localhost:8000"
) else (
  echo Google Chrome nao foi encontrado.
  echo Abra manualmente: http://localhost:8000
  pause
)

endlocal
