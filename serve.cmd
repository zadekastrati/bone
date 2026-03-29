@echo off
cd /d "%~dp0"
set "PHP83=C:\laragon\bin\php\php-8.3.9-Win32-vs16-x64\php.exe"
if not exist "%PHP83%" (
  echo Edit serve.cmd: PHP 8.2+ not found at:
  echo %PHP83%
  exit /b 1
)
echo Starting Laravel at http://127.0.0.1:8000
echo Use this URL — not Laragon Apache — until Apache uses PHP 8.2+.
"%PHP83%" artisan serve --host=127.0.0.1 --port=8000
