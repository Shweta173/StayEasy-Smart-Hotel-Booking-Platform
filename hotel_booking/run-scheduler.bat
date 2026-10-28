@echo off
REM Windows Task Scheduler script for Laravel
REM Run this every minute via Windows Task Scheduler

cd /d "%~dp0"
php artisan schedule:run >> storage\logs\scheduler.log 2>&1
